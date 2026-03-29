<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['date_start', 'date_end', 'period'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$date_start = $_POST["date_start"];
$date_end = $_POST["date_end"];
$period = $_POST["period"];

$start = new DateTime("$date_start");
$end = new DateTime("$date_end");
$interval = new DateInterval('P1D');
$days = 0;
for($i = $start; $i <= $end; $i->add($interval)){
    $days++;
}

$expenses_good = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_expense_type` = 1 AND `id_expense_good_type` = 1 AND `date` >= '$date_start' && `date` <= '$date_end'");
$expenses_consumable = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_expense_good_type` = 2 AND `date` >= '$date_start' && `date` <= '$date_end'");
$expenses_other = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_expense_type` = 1 AND `id_expense_good_type` = 3 AND `date` >= '$date_start' && `date` <= '$date_end'");

$goods = [];
$consumable = [];
$other = [];

while($good = mysqli_fetch_assoc($expenses_good)){
    $quantity = $good['quantity'];
    $good_id = $good['id_good'];

    if(isset($goods[$good_id])){
        $goods[$good_id]['quantity'] += $quantity;
    } else {
        $goods[$good_id] = [
            'quantity' => $quantity,
            'good' => $good_id
        ];
    }
}

$products = [];

foreach($goods as $good){
    $good_id = $good['good'];
    $quantity = $good['quantity'];

    $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
    $good_item = mysqli_fetch_assoc($good_item);
    $product_id = $good_item['id_product'];
    $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
    $product = mysqli_fetch_assoc($product);


    if($good_item['weight'] == 1) {
        $warehouse = $good_item['id_warehouse'];
        $weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product_id AND `id_warehouse` = $warehouse");
        $weight = mysqli_fetch_assoc($weight);
        if($weight['composite'] == 1) {
            $weight_id = $weight['id'];
            $composite_item = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id");
            $composite_item = mysqli_fetch_assoc($composite_item);
            $composite_id = $composite_item['id'];
            $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $composite_id");
            while($composite = mysqli_fetch_assoc($composite_list)){
                $weight_id = $composite['id_good_weight'];
                $proportion = $composite['proportion'];
                $weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id` = $weight_id");
                $weight = mysqli_fetch_assoc($weight);
                $product_id = $weight['id_product'];
                $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
                $product = mysqli_fetch_assoc($product);

                if(isset($products[$product_id])){
                    if(isset($products[$product_id]["expense_composite"])) {
                        $products[$product_id]["expense_composite"] += $product['weight'] * $good_item['quantity'] * $quantity / 100 * $proportion;
                    } else {
                        $products[$product_id]["expense_composite"] = $product['weight'] * $good_item['quantity'] * $quantity / 100 * $proportion;
                    }
                } else {
                    $products[$product_id] = [
                        'id' => $product_id,
                        "title" => $product['show_title'] ? $product['show_title'] : $product['title'],
                        "expense" => 0,
                        "expense_composite" => $product['weight'] * $good_item['quantity'] * $quantity / 100 * $proportion
                    ];
                }
            }
            continue;
        }
    }

    if(isset($products[$product_id])){
        $products[$product_id]["expense"] += $product['weight'] * $good_item['quantity'] * $quantity;
    } else {
        $products[$product_id] = [
            'id' => $product_id,
            "title" => $product['show_title'] ? $product['show_title'] : $product['title'],
            "expense" => $product['weight'] * $good_item['quantity'] * $quantity,
        ];
    }
}

foreach ($products as $product) {
    $product_id = $product['id'];
    $product_item = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
    $product_item = mysqli_fetch_assoc($product_item);
    $weight = $product_item['weight'];
    $actual = 0;
    $goods_list = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product` = $product_id AND `weight` = 0");
    while($good_item = mysqli_fetch_assoc($goods_list)){
        $actual += $good_item['quantity'] * $good_item['balance'] * $weight;
    }

    $weight_list = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product_id AND `composite` = 0");
    while($good_item = mysqli_fetch_assoc($weight_list)){
        $actual += $good_item['balance'] * $weight;
    }
    $products[$product_id]["actual"] = round($actual);
    $products[$product_id]["expense"] = round($products[$product_id]["expense"]);
    if(isset($products[$product_id]["expense_composite"])){
        $products[$product_id]["expense_composite"] = round($products[$product_id]["expense_composite"]);
    }

}

while($good = mysqli_fetch_assoc($expenses_consumable)){
    $quantity = $good['quantity'];
    $good_id = $good['id_good'];

    if(isset($consumable[$good_id])){
        $consumable[$good_id]['quantity'] += $quantity;
    } else {
        $consumable[$good_id] = [
            'quantity' => $quantity,
            'good' => $good_id
        ];
    }
}

$consumable_list = [];

foreach($consumable as $good){
    $good_id = $good['good'];
    $quantity = $good['quantity'];
    $good_item = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id` = $good_id");
    $good_item = mysqli_fetch_assoc($good_item);
    if(isset($consumable_list[$good_item['title']])) {
        $consumable_list[$good_item['title']]['expense'] += $quantity;
        $consumable_list[$good_item['title']]['actual'] += $good_item['balance'];

    } else {
        $consumable_list[$good_item['title']] = [
            'title' => $good_item['title'],
            'expense' => $quantity,
            'actual' => $good_item['balance'],
        ];
    }
}

while($good = mysqli_fetch_assoc($expenses_other)){
    $quantity = $good['quantity'];
    $good_id = $good['id_good'];

    if(isset($other[$good_id])){
        $other[$good_id]['quantity'] += $quantity;
    } else {
        $other[$good_id] = [
            'quantity' => $quantity,
            'good' => $good_id
        ];
    }
}

$other_list = [];
foreach($other as $good){
    $good_id = $good['good'];
    $quantity = $good['quantity'];
    $good_item = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
    $good_item = mysqli_fetch_assoc($good_item);
    if(isset($other_list[$good_item['title']])) {
        $other_list[$good_item['title']]['expense'] += $quantity;
        $other_list[$good_item['title']]['actual'] += $good_item['balance'];

    } else {
        $other_list[$good_item['title']] = [
            'title' => $good_item['title'],
            'expense' => $quantity,
            'actual' => $good_item['balance'],
        ];
    }
}

$req = [
    "purchasing" =>[
        "date_start" => $date_start,
        "date_end" => $date_end,
        "days" => $days,
        "period" => intval($period),
        "products" => $products,
        "consumable" => $consumable_list,
        "other" => $other_list,
    ]
];

http_response_code(200);
echo json_encode($req);
