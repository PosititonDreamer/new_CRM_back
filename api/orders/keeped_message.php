<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";
require_once __DIR__ . "/functions.php";

$messages = check_data(['id'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$order_id = $_GET['id'];

send_keeped_mail($connect, $order_id);

$req = [
    'messages' => ['Письмо успешно отправлено'],
];
http_response_code(200);
echo json_encode($req);
