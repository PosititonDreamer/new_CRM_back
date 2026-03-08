<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];

if($id == 1) {
    $req = [
        'messages'=> ['Нельзя удалить склад, используется на всем сайте']
    ];
    http_response_code(400);
    echo json_encode($req);
    die();
}

$check = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_warehouse` = $id LIMIT 1");
$check_2 = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id_warehouse` = $id LIMIT 1");
$check_3 = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = $id LIMIT 1");
$check_4 = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_warehouse` = $id LIMIT 1");
$check_5 = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_warehouse` = $id LIMIT 1");
$check_6 = mysqli_query($connect, "SELECT * FROM `workers_warehouse` WHERE `id_warehouse` = $id LIMIT 1");
$check_7 = mysqli_query($connect, "SELECT * FROM `supplies_warehouse` WHERE `id_warehouse_receive` = $id OR `id_warehouse_give` = $id LIMIT 1");

if (
    mysqli_num_rows($check) > 0 ||
    mysqli_num_rows($check_2) > 0 ||
    mysqli_num_rows($check_3) > 0 ||
    mysqli_num_rows($check_4) > 0 ||
    mysqli_num_rows($check_5) > 0 ||
    mysqli_num_rows($check_6) > 0 ||
    mysqli_num_rows($check_7) > 0
) {
    mysqli_query($connect, "UPDATE `warehouses` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Склад скрыт, так как он уже используется в учете данных']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `warehouses` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Склад успешно удален']
    ];
    http_response_code(200);
    echo json_encode($req);
}