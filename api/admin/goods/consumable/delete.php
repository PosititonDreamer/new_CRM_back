<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good_consumable` = $id");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($connect, "UPDATE `goods_consumable` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Раходник скрыт, так как расходник уже используется в учете складов']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `goods_consumable` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Расходник успешно удален']
    ];
    http_response_code(200);
    echo json_encode($req);
}