<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../../helpers/check_messages.php";

$warehouse = $_GET['warehouse'];

$list = mysqli_query($connect, "SELECT * FROM `goods` WHERE `hidden` = 0 AND `id_warehouse` = $warehouse");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item['id'],
        "product" => $item['id_product'],
        "warehouse" => $item['id_warehouse'],
        "quantity" => $item['quantity'],
        "balance" => $item['balance'],
        "article" => $item['article'],
        "few" => $item['few'],
        "few_very" => $item['few_very'],
        "price" => $item['price'],
        "weight" => intval($item['weight']),
    ];
}

$products = mysqli_query($connect, "SELECT * FROM `products` WHERE `hidden` = 0 ORDER BY `products`.`sort` ASC");

$new_products = [];

while ($item = mysqli_fetch_assoc($products)) {
    $new_products[] = [
        "id" => $item["id"],
        "measure_unit" => $item["id_measure_unit"],
        "title" => $item["title"],
        "show_title" => $item["show_title"],
        "sort" => $item["sort"]
    ];
}

$measures = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `hidden` = 0");

$new_measures = [];

while ($item = mysqli_fetch_assoc($measures)) {
    $new_measures[] = [
        "id" => $item["id"],
        "title" => $item["title"],
    ];
}

$req = [
    "messages" => ["Получен список фасованных товаров"],
    "goods" => $new_list,
    "products" => $new_products,
    "measure_units" => $new_measures,

];
http_response_code(200);
echo json_encode($req);