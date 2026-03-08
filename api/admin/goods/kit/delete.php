<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_good` = '$id' AND `id_order_composition_type` = 2");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($connect, "UPDATE `goods_kit` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Набор скрыт, так как уже используется в заказах']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `goods_kit` WHERE `id` = '$id'");
    mysqli_query($connect, "DELETE FROM `goods_kit_list` WHERE `id_good_kit` = $id");
    $req = [
        'messages' => ["Набор успешно удален"]
    ];
    http_response_code(200);
    echo json_encode($req);
}