<?php
require_once __DIR__ . "/../../connect.php";

$list_goods = mysqli_query($connect, "SELECT * FROM `goods` WHERE `hidden` = 0");
$list_goods_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `composite` = 0");
$list_goods_consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `hidden` = 0 ORDER BY `goods_consumable`.`sort` ASC");
$list_goods_other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `hidden` = 0 ORDER BY `goods_other`.`sort` ASC");

$new_list_goods = [];

while ($item = mysqli_fetch_assoc($list_goods)) {
    $new_list_goods[] = [
        "id" => $item["id"],
        "product" => $item["id_product"],
        "warehouse" => $item["id_warehouse"],
        "quantity" => $item["quantity"],
        "weight" => $item["weight"] == 1,
        "type" => "good",
    ];
}

while ($item = mysqli_fetch_assoc($list_goods_weight)) {
    $new_list_goods[] = [
        "id" => $item["id"],
        "product" => $item["id_product"],
        "warehouse" => $item["id_warehouse"],
        "type" => "weight",
    ];
}

while ($item = mysqli_fetch_assoc($list_goods_consumable)) {
    $new_list_goods[] = [
        "id" => $item["id"],
        "title" => $item["title"],
        "warehouse" => $item["id_warehouse"],
        "type" => "consumable",
    ];
}

while ($item = mysqli_fetch_assoc($list_goods_other)) {
    $new_list_goods[] = [
        "id" => $item["id"],
        "title" => $item["title"],
        "warehouse" => $item["id_warehouse"],
        "type" => "other",
    ];
}

$req = [
    "messages" => ['Получен список всех товаров на складах'],
    "goods" => $new_list_goods
];
http_response_code(200);
echo json_encode($req);