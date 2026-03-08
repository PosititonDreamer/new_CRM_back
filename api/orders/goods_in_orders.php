<?php
require_once __DIR__ . "/../connect.php";

$orders = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 1 OR `id_order_status` = 3");

$new_orders = [];

while ($order = mysqli_fetch_assoc($orders)) {
    $order_id = $order['id'];
    $goods = mysqli_query($connect, "SELECT `orders_good`.`quantity`, `goods`.`balance`, `goods`.`quantity` AS good_quantity, products.id AS id_product, products.title AS product_title, products.show_title AS product_show_title, measure_units.title AS measure, products.sort FROM `orders_good` JOIN goods ON goods.id = `orders_good`.`id_good` JOIN `products` ON goods.id_product = products.id JOIN measure_units ON measure_units.id = products.id_measure_unit WHERE `orders_good`.`id_order` = $order_id ORDER BY products.sort;");
    while ($good = mysqli_fetch_assoc($goods)) {
        $quantity = $good['quantity'];
        $good_quantity = $good['good_quantity'];
        $product_title = $good['product_title'];

        if(isset($new_orders["$product_title-$good_quantity"])) {
            $new_orders["$product_title-$good_quantity"]['quantity'] += $quantity;
        } else {
            $balance = $good['balance'];
            $product_show_title = $good['product_show_title'];
            $measure = $good['measure'];
            $sort = $good['sort'];

            $new_orders["$product_title-$good_quantity"] = [
                "quantity" => $quantity,
                "balance" => $balance,
                "good_quantity" => $good_quantity,
                "title" => $product_title,
                "show_title" => $product_show_title,
                "measure" => $measure,
                "sort" => $sort
            ];
        }
    }
}

$req = [
    "messages" => ['Список товаров в заказе успешно получен'],
    "goods" => []
];

foreach ($new_orders as $new_order) {
    $req['goods'][] = $new_order;
}

http_response_code(200);
echo json_encode($req);