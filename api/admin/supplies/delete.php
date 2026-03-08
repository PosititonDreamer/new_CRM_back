<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id_supply_warehouse` = $id");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($connect, "UPDATE `supplies_warehouse` SET `hidden` = 1 WHERE `id` = $id");
    $req = [
        "messages" => ['Связь складов успешно скрыта, так как были поставки']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `supplies_warehouse` WHERE `id` = $id");
    mysqli_query($connect, "DELETE FROM `supplies_warehouse_connection` WHERE `id_supply_warehouse` = $id");
    $req = [
        "messages" => ['Связь складов успешно удалена']
    ];
    http_response_code(200);
    echo json_encode($req);
}