<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];

move_uploaded_file($_FILES["blank"]["tmp_name"], __DIR__ . "/../../files/$id.pdf");

$req = [
    "messages" => ['Бланк успешно добавлен'],
];
http_response_code(200);
echo json_encode($req);