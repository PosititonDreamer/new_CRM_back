<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];

mysqli_query($connect, "DELETE FROM `orders` WHERE `id`=$id");
mysqli_query($connect, "DELETE FROM `orders_good` WHERE `id_order`=$id");
mysqli_query($connect, "DELETE FROM `orders_process` WHERE `id_order`=$id");

$req = [
    "messages" => ['Заказ успешно удален']
];
http_response_code(200);
echo json_encode($req);
