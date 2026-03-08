<?php
require_once __DIR__ . "/../../../connect.php";

$warehouse = $_GET['warehouse'];

$list = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = $warehouse AND `hidden` = 0 ORDER BY `goods_other`.`sort` ASC");
$list_type = mysqli_query($connect, "SELECT * FROM `goods_other_type`");

$new_list = [];
$new_list_type = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item['id'],
        "warehouse" => $item['id_warehouse'],
        "type" => $item['id_good_other_type'],
        "sort" => $item['sort'],
        "title" => $item['title'],
        "balance" => $item['balance'],
        "few" => $item['few'],
        "few_very" => $item['few_very'],
    ];
}

while ($item = mysqli_fetch_assoc($list_type)) {
    $new_list_type[] = [
        "id" => $item['id'],
        "title" => $item['title'],
    ];
}

$req = [
    'messages' =>[ "Получен список коробок и магнитов"],
    'goods_other' => $new_list,
    "goods_other_type" => $new_list_type
];
http_response_code(200);
echo json_encode($req);