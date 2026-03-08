<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";
require_once __DIR__ . "/functions.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];

send_info_telegram($connect, $id);

http_response_code(200);
$req = [
    "messages" => ['Сообщение успешно отправлено']
];
echo json_encode($req);