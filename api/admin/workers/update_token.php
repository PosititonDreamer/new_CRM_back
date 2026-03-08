<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
require_once __DIR__ . "/functions.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$token = generate_token();

mysqli_query($connect, "UPDATE `workers` SET `token`='$token' WHERE `id`= $id");

$req = [
  "messages" => ['Токен успешно обновлен'],
  "token" => $token
];
http_response_code(200);
echo json_encode($req);