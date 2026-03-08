<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id', 'list'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$list = json_decode($_POST['list'], true);

mysqli_query($connect, "DELETE FROM `supplies_list` WHERE `id_supply` = $id");


foreach ($list as $item) {
    $warehouse_connection = $item['warehouse_connection'];
    $quantity = $item['quantity'];
    mysqli_query($connect, "INSERT INTO `supplies_list`( `id_supply`, `id_supply_warehouse_connection`, `quantity`, `ready`) VALUES ($id,$warehouse_connection,$quantity,0)");
    $item_id = mysqli_insert_id($connect);
}

$length = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $id");
$length = mysqli_num_rows($length);

$req = [
    "messages" => ["Поставка успешно изменена"],
    "length" => $length,
];
http_response_code(200);
echo json_encode($req);