<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST["id"];

$check = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product` = '$id'");
$check_2 = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = '$id'");


if(mysqli_num_rows($check) > 0 || mysqli_num_rows($check_2) > 0) {
    mysqli_query($connect, "UPDATE `products` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Продукт скрыт, так как продукт уже используется в учете складов']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `products` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Продукт успешно удален']
    ];
    http_response_code(200);
    echo json_encode($req);
}