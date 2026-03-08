<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];

mysqli_query($connect, "DELETE FROM `promos` WHERE `id` = $id");

$req = [
    "messages" => ['Промокод успешно удален']
];
http_response_code(200);
echo json_encode($req);