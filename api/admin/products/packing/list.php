<?php
require_once __DIR__ . "/../../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `products_packing` ORDER BY `products_packing`.`packing` ASC");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "product" => $item["id_product"],
        "packing" => $item["packing"],
    ];
}

$req = [
    'messages' => ['Получен список фасовок'],
    "packing" => $new_list,
];
http_response_code(200);
echo json_encode($req);
