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
        "price" => intval($item['price']),
        "weight" => intval($item['weight']),
    ];
}

$req = [
  "messages" => ["Получен список фасованных товаров"],
  "goods" => $new_list,
];
http_response_code(200);
echo json_encode($req);