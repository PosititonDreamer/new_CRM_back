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

$expense = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_expense_type` = 1 AND `id_expense_good_type` = 1 AND `date` >= '$date_start' && `date` <= '$date_end'");

$goods = [];

while($good = mysqli_fetch_assoc($expense)){
    $quantity = $good['quantity'];
    $good_id = $good['id_good'];

    if(isset($goods[$good_id])){
        $goods[$good_id]['expenses'] += $quantity;
    } else {
        $goods[$good_id] = [
            'expenses' => $quantity,
            'good' => $good_id
        ];
    }
}

$products = [];

foreach ($goods as $good) {
    $good_id = $good['good'];
    $expense = $good['expenses'];

    $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
    $good_item = mysqli_fetch_assoc($good_item);

    $product_id = $good_item['id_product'];
    $quantity = $good_item['quantity'];

    $current_quantity = mysqli_query($connect, "SELECT SUM(balance) as balance FROM `goods` WHERE `id_product` = $product_id AND `weight` = 0 AND `quantity` = $quantity");
    $current_quantity = mysqli_fetch_assoc($current_quantity);

    $product = mysqli_query($connect, "SELECT `products`.`sort`, `products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure`  FROM `products` JOIN `measure_units` ON `products`.`id_measure_unit` = `measure_units`.`id` WHERE `products`.`id` = $product_id");
    $product = mysqli_fetch_assoc($product);

    if($good_item['weight'] == 0) {
        if(!isset($products[$product_id])) {
            $products[$product_id] = [
                'id' => $product_id,
                "sort" => $product['sort'],
                "measure" => $product['measure'],
                "title" => $product['show_title'] ? $product['show_title'] : $product['title'],
                "list" => [],
            ];
        }

        $products[$product_id]['list'][$quantity] = [
            "quantity" => $quantity,
            "expense" => $expense,
            "current_balance" => $current_quantity['balance'] ?? 0,
        ];
    }
}

$req = [
    "messages" => ["Список данных успешно получен"],
    "packing" =>[
        "date_start" => $date_start,
        "date_end" => $date_end,
        "days" => $days,
        "period" => intval($period),
        "products" => $products,
    ]
];

http_response_code(200);
echo json_encode($req);

