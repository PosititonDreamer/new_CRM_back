<?php
require_once __DIR__ . "/../../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `products_connection`");

$new_list = [];

while($item = mysqli_fetch_assoc($list)){
    $item_id = $item["id"];
    $new_item = [
        "id" => $item_id,
        "title" => $item["title"],
        "list" => []
    ];

    $connections = mysqli_query($connect, "SELECT * FROM `products_connection_list` WHERE `id_product_connection` = $item_id");

    while($connection = mysqli_fetch_assoc($connections)){
        $new_item["list"][] = [
            "id" => $connection["id"],
            "product" => $connection["id_product"],
        ];
    }

    $new_list[] = $new_item;
}

$req = [
    "messages" => ['Список связи продуктов успешно получен'],
    "products_connections" => $new_list
];
http_response_code(200);
echo json_encode($req);