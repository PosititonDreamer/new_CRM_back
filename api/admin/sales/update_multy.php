<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id', 'title', 'days', 'goods'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$title = $_POST['title'];
$sum = $_POST['sum'];
$days = $_POST['days'];
$goods = json_decode($_POST['goods'], true);

mysqli_query($connect, "UPDATE `sales_multy` SET `sum`='$sum',`days`='$days',`title`='$title' WHERE `id` = $id");
mysqli_query($connect, "DELETE FROM `sales_multy_goods` WHERE `id_sale_multy` = $id");
$new_goods = [];
foreach ($goods as $item) {
    $good = $item['good'];
    $quantity = $item['quantity'];
    mysqli_query($connect, "INSERT INTO `sales_multy_goods`(`id_sale_multy`, `id_good`, `quantity`) VALUES ($id,$good,$quantity)");
    $item_id = mysqli_insert_id($connect);
    $new_goods[] = [
        "id" => $item_id,
        "good" => $good,
        "quantity" => $quantity,
    ];
}
$date = date("Y-m-d");

$new_list = [];

$list = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` >= '$date' AND `id_sale_multy` = $id");
$list_closed = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` < '$date' AND `id_sale_multy` = $id");

$new_multy_list = [];

while ($multy_item = mysqli_fetch_assoc($list)) {
    $new_multy_list[] = [
        "id" => $multy_item['id'],
        "sale_multy" => $multy_item['id_sale_multy'],
        "good" => $multy_item['id_good'],
        "code" => $multy_item['code'],
        "used" => $multy_item['used'] == 1,
        "denied" => false,
        "date_start" => $multy_item['date_start'],
        "date_end" => $multy_item['date_end'],
    ];
}

while ($multy_item = mysqli_fetch_assoc($list_closed)) {
    $new_multy_list[] = [
        "id" => $multy_item['id'],
        "sale_multy" => $multy_item['id_sale_multy'],
        "good" => $multy_item['id_good'],
        "code" => $multy_item['code'],
        "used" => $multy_item['used'] == 1,
        "denied" => true,
        "date_start" => $multy_item['date_start'],
        "date_end" => $multy_item['date_end'],
    ];
}

$req = [
    "messages" => ['Акция успешно изменена'],
    "sale" => [
        "id" => $id,
        "title" => $title,
        "sum" => $sum,
        "days" => $days,
        "active" => true,
        "goods" => $new_goods,
        "list" => $new_list,
    ],
];
http_response_code(200);
echo json_encode($req);