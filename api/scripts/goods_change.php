<?php
require_once __DIR__ . "/../connect.php";

$months = 3;

$date = new DateTime();
$interval = new DateInterval("P" . $months . "M");
date_sub($date, $interval);
$old_date = $date->format('Y-m-d');
$date = date('Y-m-d');

$list_goods = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `date` >= '$old_date' AND `date` <= '$date' AND `id_expense_type` = 1 AND id_expense_good_type = 1");
$list_consumable = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `date` >= '$old_date' AND `date` <= '$date' AND id_expense_good_type = 2");
$list_other = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `date` >= '$old_date' AND `date` <= '$date' AND `id_expense_type` = 1 AND id_expense_good_type = 3");

$supplies = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `date` >= '$old_date' AND `date` <= '$date'");

$list = [
    "products" => [],
    "consumables" => [],
    "others" => [],
];


while ($good = mysqli_fetch_assoc($list_goods)) {
    $good_id = $good['id_good'];
    $good_quantity = $good['quantity'];

    $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
    $good_item = mysqli_fetch_assoc($good_item);
    $product_id = $good_item['id_product'];
    $warehouse_id = $good_item['id_warehouse'];
    $good_item_quantity = $good_item['quantity'];

    if (isset($list['products'][$product_id])) {
        $list['products'][$product_id]['expense'] += $good_item_quantity * $good_quantity;
        if(isset($list['products'][$product_id]["$good_item_quantity"])) {
            $list['products'][$product_id]["$good_item_quantity"]["expense"] += $good_quantity;
        } else {
            $list['products'][$product_id]["$good_item_quantity"] = [
                "quantity" => $good_item_quantity,
                "expense" => $good_quantity,
            ];
        }
    } else {
        $list['products'][$product_id] = [
            "expense" => $good_item_quantity * $good_quantity,
            "$good_item_quantity" => [
                "quantity" => $good_item_quantity,
                "expense" => $good_quantity,
            ]
        ];
    }

    if($good_item['weight'] == 1) {
        $weight_item = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product_id AND `composite` = 1 AND `id_warehouse` = $warehouse_id");
        if(mysqli_num_rows($weight_item) > 0) {
            $weight_item = mysqli_fetch_assoc($weight_item);
            $weight_id = $weight_item['id'];
            $composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE  `id_good_weight` = $weight_id");
            $composite = mysqli_fetch_assoc($composite);
            $composite_id = $composite['id'];
            $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE  `id_good_weight_composite` = $composite_id");
            while ($composite = mysqli_fetch_assoc($composite_list)) {
                $weight_composite_id = $composite['id_good_weight'];
                $weight_composite_item = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id` = $weight_composite_id");
                $weight_composite_item = mysqli_fetch_assoc($weight_composite_item);
                $weight_product_id = $weight_composite_item['id_product'];

                if (isset($list['products'][$weight_product_id])) {
                    $list['products'][$weight_product_id]['expense'] += ($good_item_quantity * $good_quantity) / 100 * $composite['proportion'];
                } else {
                    $list['products'][$weight_product_id] = [
                        "expense" => ($good_item_quantity * $good_quantity) / 100 * $composite['proportion'],
                    ];
                }
            }
        }
    }
}

while($consumable = mysqli_fetch_assoc($list_consumable)) {
    $consumable_id = $consumable['id_good'];
    $consumable_item = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id` = $consumable_id");
    $consumable_item = mysqli_fetch_assoc($consumable_item);
    $title = $consumable_item['title'];
    $quantity = $consumable['quantity'];

    if (isset($list['consumables'][$title])) {
        $list['consumables'][$title]['expense'] += $quantity;
    } else {
        $list['consumables'][$title] = [
            "title" => $title,
            "expense" => $quantity,
        ];
    }
}

while($other = mysqli_fetch_assoc($list_other)) {
    $other_id = $other['id_good'];
    $other_item = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $other_id");
    $other_item = mysqli_fetch_assoc($other_item);
    $title = $other_item['title'];
    $quantity = $other['quantity'];

    if (isset($list['others'][$title])) {
        $list['others'][$title]['expense'] += $quantity;
    } else {
        $list['others'][$title] = [
            "title" => $title,
            "expense" => $quantity,
        ];
    }
}

while ($supply = mysqli_fetch_assoc($supplies)) {
    $supply_id = $supply['id'];

    $supplies_list = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $supply_id");
    while ($supply_item = mysqli_fetch_assoc($supplies_list)) {
        $connection_id = $supply_item['id_supply_warehouse_connection'];
        $supply_connection = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id` = $connection_id AND `good_type` = 'consumable'");
        if (mysqli_num_rows($supply_connection) > 0) {
            $supply_connection = mysqli_fetch_assoc($supply_connection);
            $id_consumable = $supply_connection['id_good_give'];
            $consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id` = $id_consumable");
            $consumable = mysqli_fetch_assoc($consumable);
            $quantity_supply = $supply_item['quantity'];
            $title = $consumable['title'];
            if (isset($list['consumables'][$title])) {
                $list['consumables'][$title]['expense'] -= $quantity_supply;
            } else {
                $list['consumables'][$title] = [
                    "title" => $title,
                    "expense" => $quantity_supply * -1,
                ];
            }
        }
    }
}

$warehouses = mysqli_query($connect, "SELECT * FROM `warehouses`");

while($warehouse = mysqli_fetch_assoc($warehouses)) {
    $warehouse_id = $warehouse['id'];
    $few = $warehouse['few'];
    $few_very = $warehouse['few_very'];
    $few_other = $warehouse['few_other'];
    $few_very_other = $warehouse['few_very_other'];
    $goods_warehouse = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_warehouse` = $warehouse_id AND `weight` = 0 AND `hidden` = 0");
    while($good = mysqli_fetch_assoc($goods_warehouse)) {
        $good_id = $good['id'];
        $product_id = $good['id_product'];
        $quantity = $good['quantity'];
        if(isset($list['products'][$product_id])) {
            $item = $list['products'][$product_id];
            if(isset($item["$quantity"])) {
                $day_expense = $item["$quantity"]["expense"] / 90;
                $item_few = round($day_expense * $few);
                $item_few_very = round($day_expense * $few_very);
                mysqli_query($connect, "UPDATE `goods` SET `few`=$item_few,`few_very`=$item_few_very WHERE `id` = $good_id");
            } else {
                mysqli_query($connect, "UPDATE `goods` SET `few`=0,`few_very`=0 WHERE `id` = $good_id");
            }
        } else {
            mysqli_query($connect, "UPDATE `goods` SET `few`=0,`few_very`=0 WHERE `id` = $good_id");
        }
    }

    $weight_warehouse = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_warehouse` = $warehouse_id AND `composite` = 0");
    while($weight = mysqli_fetch_assoc($weight_warehouse)) {
        $weight_id = $weight['id'];
        $product_id = $weight['id_product'];
        if(isset($list['products'][$product_id])) {
            $item = $list['products'][$product_id];
            if(isset($item["expense"])) {
                $day_expense = $item["expense"] / 90;
                $item_few = round($day_expense * $few);
                $item_few_very = round($day_expense * $few_very);
                mysqli_query($connect, "UPDATE `goods_weight` SET `few`=$item_few,`few_very`=$item_few_very WHERE `id` = $weight_id");
            } else {
                mysqli_query($connect, "UPDATE `goods_weight` SET `few`=0,`few_very`=0 WHERE `id` = $weight_id");
            }
        } else {
            mysqli_query($connect, "UPDATE `goods_weight` SET `few`=0,`few_very`=0 WHERE `id` = $weight_id");
        }
    }

    $consumable_warehouse = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id_warehouse` = $warehouse_id AND `hidden` = 0");
    while($consumable = mysqli_fetch_assoc($consumable_warehouse)) {
        $consumable_id = $consumable['id'];
        $title = $consumable['title'];
        if(isset($list['consumables'][$title])) {
            $item = $list['consumables'][$title];
            $day_expense = $item["expense"] / 90;
            $item_few = round($day_expense * $few_other);
            $item_few_very = round($day_expense * $few_very_other);
            mysqli_query($connect, "UPDATE `goods_consumable` SET `few`=$item_few,`few_very`=$item_few_very WHERE `id` = $consumable_id");
        } else {
            mysqli_query($connect, "UPDATE `goods_consumable` SET `few`=0,`few_very`=0 WHERE `id` = $consumable_id");
        }
    }

    $other_warehouse = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = $warehouse_id AND `hidden` = 0");
    while($other = mysqli_fetch_assoc($other_warehouse)) {
        $other_id = $other['id'];
        $title = $other['title'];
        if(isset($list['others'][$title])) {
            $item = $list['others'][$title];
            $day_expense = $item["expense"] / 90;
            $item_few = round($day_expense * $few_other);
            $item_few_very = round($day_expense * $few_very_other);
            mysqli_query($connect, "UPDATE `goods_other` SET `few`=$item_few,`few_very`=$item_few_very WHERE `id` = $other_id");
        } else {
            mysqli_query($connect, "UPDATE `goods_other` SET `few`=0,`few_very`=0 WHERE `id` = $other_id");
        }
    }
}