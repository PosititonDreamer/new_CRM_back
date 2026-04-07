<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$order_id = $_GET['id'];

mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");

$req = [
    'messages' => ['Данные заказа успешно изменены'],
];
http_response_code(200);
echo json_encode($req);
