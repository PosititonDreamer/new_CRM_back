<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `products` WHERE `hidden` = 0 ORDER BY `products`.`sort` ASC");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "measure_unit" => $item["id_measure_unit"],
        "title" => $item["title"],
        "show_title" => $item["show_title"],
        "client_title" => $item["client_title"],
        "sort" => $item["sort"],
        "weight" => $item["weight"],
    ];
}

$req = [
    'messages' =>[ "Получен список продуктов"],
    'products' => $new_list
];
http_response_code(200);
echo json_encode($req);