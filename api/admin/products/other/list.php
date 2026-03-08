<?php
require_once __DIR__ . "/../../../connect.php";
$list = mysqli_query($connect, "SELECT * FROM `products_other`");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "packing" => $item["id_packing"],
        "title" => $item["title"]
    ];
}

$req = [
    "messages" => ["Получен список кривых продуктов"],
    "products_other" => $new_list
];
http_response_code(200);
echo json_encode($req);