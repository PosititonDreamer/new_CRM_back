<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$order_id = $_POST['id'];

mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id ");

$req = [
    'messages' => ['Данные заказа успешно изменены'],
];
http_response_code(200);
echo json_encode($req);
