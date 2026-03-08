<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order_good_type` = 2 AND `id_good` = $id");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($connect, "UPDATE `goods_other` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Коробка или магнит скрыты, так как он привязан к заказам']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `goods_other` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Коробка или магнит успешно удалены']
    ];
    http_response_code(200);
    echo json_encode($req);
}