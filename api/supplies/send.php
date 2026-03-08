<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$date = date("Y-m-d");

$supply = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id` = $id");
$supply = mysqli_fetch_assoc($supply);

$supply_warehouse_id = $supply['id_supply_warehouse'];
$warehouse_give = mysqli_query($connect, "SELECT `id_warehouse_give` FROM `supplies_warehouse` WHERE `id` = $supply_warehouse_id");
$warehouse_give = mysqli_fetch_assoc($warehouse_give)["id_warehouse_give"];

mysqli_query($connect, "UPDATE `supplies` SET `id_supply_status` = 2 WHERE `id` = $id");
mysqli_query($connect, "INSERT INTO `supplies_process`(`id_supply`, `id_supply_process_status`, `date`) VALUES ($id,2,'$date')");

$list = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $id");

$update = [
    'goods' => [],
    'weight' => [],
    'consumable' => [],
    'other' => [],
];

while ($item = mysqli_fetch_assoc($list)) {
    $item_id = $item['id'];
    $supply_warehouse_connection = $item['id_supply_warehouse_connection'];
    $quantity = $item['quantity'];

    mysqli_query($connect, "UPDATE `supplies_list` SET `ready`= 1 WHERE `id`=$item_id");

    $good = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id` = $supply_warehouse_connection");
    $good = mysqli_fetch_assoc($good);

    $good_id = $good['id_good_give'];
    $good_type = $good['good_type'];

    if ($good_type == "good") {
        $check_good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
        $check_good = mysqli_fetch_assoc($check_good);
        if ($check_good['weight'] == 0) {
            if(isset($update['goods'][$good_id])) {
                $update['goods'][$good_id]['balance'] += $quantity;
            } else {
                $update['goods'][$good_id] = [
                    'good_id' => $good_id,
                    'balance' => $quantity,
                ];
            }
            mysqli_query($connect, "UPDATE `goods` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
            $consumable_list = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $good_id");
            while ($consumable = mysqli_fetch_assoc($consumable_list)) {
                $consumable_id = $consumable['id_good_consumable'];
                if(isset($update['consumable'][$consumable_id])) {
                    $update['consumable'][$consumable_id]['balance'] += $quantity;
                } else {
                    $update['consumable'][$consumable_id] = [
                        'good_id' => $consumable_id,
                        'balance' => $quantity,
                    ];
                }
                mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`-$quantity WHERE `id` = $consumable_id");
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$consumable_id,2,2,$quantity,'$date')");
            }
        } else {
            $consumable_list = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $good_id");
            while ($consumable = mysqli_fetch_assoc($consumable_list)) {
                $consumable_id = $consumable['id_good_consumable'];
                if(isset($update['consumable'][$consumable_id])) {
                    $update['consumable'][$consumable_id]['balance'] += $quantity;
                } else {
                    $update['consumable'][$consumable_id] = [
                        'good_id' => $consumable_id,
                        'balance' => $quantity,
                    ];
                }
                mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`-$quantity WHERE `id` = $consumable_id");
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$consumable_id,2,2,$quantity,'$date')");
            }

            $warehouse = $check_good['id_warehouse'];
            $product = $check_good['id_product'];
            $weight_quantity = intval($check_good['quantity']) * $quantity;

            $check_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
            $check_weight = mysqli_fetch_assoc($check_weight);
            $weight_id = $check_weight['id'];

            if ($check_weight['composite'] == 0) {
                if(isset($update['weight'][$weight_id])) {
                    $update['weight'][$weight_id]['balance'] += $weight_quantity;
                } else {
                    $update['weight'][$weight_id] = [
                        'good_id' => $weight_id,
                        'balance' => $weight_quantity,
                    ];
                }
                mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`-$weight_quantity WHERE `id` = $weight_id");
            } else {
                $composite_id = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id");
                $composite_id = mysqli_fetch_assoc($composite_id)['id'];

                $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $weight_id");

                while ($composite = mysqli_fetch_assoc($composite_list)) {
                    $composite_weight_id = $composite['id_good_weight'];
                    $composite_quantity = $weight_quantity / 100 * intval($composite['proportion']);
                    if(isset($update['weight'][$composite_weight_id])) {
                        $update['weight'][$composite_weight_id]['balance'] += $composite_quantity;
                    } else {
                        $update['weight'][$composite_weight_id] = [
                            'good_id' => $composite_weight_id,
                            'balance' => $composite_quantity,
                        ];
                    }
                    mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`-$composite_quantity WHERE `id` = $composite_weight_id");
                }
            }
        }
    }

    if ($good_type == 'consumable') {
        if(isset($update['consumable'][$good_id])) {
            $update['consumable'][$good_id]['balance'] += $quantity;
        } else {
            $update['consumable'][$good_id] = [
                'good_id' => $good_id,
                'balance' => $quantity,
            ];
        }
        mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
    }

    if ($good_type == 'other') {
        if(isset($update['other'][$good_id])) {
            $update['other'][$good_id]['balance'] += $quantity;
        } else {
            $update['other'][$good_id] = [
                'good_id' => $good_id,
                'balance' => $quantity,
            ];
        }
        mysqli_query($connect, "UPDATE `goods_other` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
    }

    if ($good_type == 'weight') {
        if(isset($update['weight'][$good_id])) {
            $update['weight'][$good_id]['balance'] += $quantity;
        } else {
            $update['weight'][$good_id] = [
                'good_id' => $good_id,
                'balance' => $quantity,
            ];
        }
        mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
    }
}

$check = mysqli_query($connect, "SELECT * FROM `magazines` WHERE `date` = '$date' AND `type` = 'supply' AND `supply_type` = 'outcome' AND `id_warehouse` = $warehouse_give LIMIT 1");
if(mysqli_num_rows($check) > 0){
    $check = mysqli_fetch_assoc($check);
    $last_id = $check['id'];
} else {
    mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`, `supply_type`) VALUES ('$date','supply',$warehouse_give, 'outcome')");
    $last_id = mysqli_insert_id($connect);
}

foreach ($update['goods'] as $good) {
    $good_id = $good['good_id'];
    $balance = $good['balance'];
    $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'good'");
    if(mysqli_num_rows($check_good) > 0){
        $check_good = mysqli_fetch_assoc($check_good);
        $item_id = $check_good['id'];
        mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $balance WHERE `id` = $item_id");
    } else {
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'good',$balance)");
    }
}

foreach ($update['weight'] as $good) {
    $good_id = $good['good_id'];
    $balance = $good['balance'];
    $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'weight'");
    if(mysqli_num_rows($check_good) > 0){
        $check_good = mysqli_fetch_assoc($check_good);
        $item_id = $check_good['id'];
        mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $balance WHERE `id` = $item_id");
    } else {
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'weight',$balance)");
    }
}

foreach ($update['consumable'] as $good) {
    $good_id = $good['good_id'];
    $balance = $good['balance'];
    $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'consumable'");
    if(mysqli_num_rows($check_good) > 0){
        $check_good = mysqli_fetch_assoc($check_good);
        $item_id = $check_good['id'];
        mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` +  $balance WHERE `id` = $item_id");
    } else {
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'consumable',$balance)");
    }
}

foreach ($update['other'] as $good) {
    $good_id = $good['good_id'];
    $balance = $good['balance'];
    $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'other'");
    if(mysqli_num_rows($check_good) > 0){
        $check_good = mysqli_fetch_assoc($check_good);
        $item_id = $check_good['id'];
        mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $balance WHERE `id` = $item_id");
    } else {
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'other',$balance)");
    }
}

$req = [
    "messages" => ['Поставка успешно отправлена'],
    "supply_status" => 2
];
http_response_code(200);
echo json_encode($req);