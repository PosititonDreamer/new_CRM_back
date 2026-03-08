<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $id");
$check_2 = mysqli_query($connect, "SELECT * FROM `goods_kit_list` WHERE `id_good` = $id");
$check_3 = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_good` = $id");
$check_4 = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_good` = $id");
$check_5 = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE (`id_good_receive` = $id OR `id_good_give` = $id) AND `good_type` = 'good'");
$check_6 = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_good` = $id AND `type` = 'good'");

if(
    mysqli_num_rows($check) > 0 ||
    mysqli_num_rows($check_2) > 0 ||
    mysqli_num_rows($check_3) > 0 ||
    mysqli_num_rows($check_4) > 0 ||
    mysqli_num_rows($check_5) > 0 ||
    mysqli_num_rows($check_6) > 0
)  {
    mysqli_query($connect, "UPDATE `goods` SET `hidden`= 1 WHERE `id` = '$id'");
    mysqli_query($connect, "UPDATE `supplies_warehouse_connection` SET `hidden`= 1 WHERE (`id_good_receive` = $id OR `id_good_give` = $id) AND `good_type` = 'good'");
    $req = [
        'messages' => ['Фасованный товар скрыт, так как он привязан к заказам']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `goods` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Фасованный товар успешно удален']
    ];
    http_response_code(200);
    echo json_encode($req);
}
