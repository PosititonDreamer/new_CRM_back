<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";
$messages = check_data(['token'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";
require_once __DIR__ . "/functions.php";

$worker = find_user($connect, $_POST["token"]);

if(is_null($worker)) {
    $req = [
        "messages" => ['Пользователя с таким токеном не существует']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    $req = [
        "messages" => ['Авторизация прошла успешно'],
        "worker" => $worker
    ];
    http_response_code(200);
    echo json_encode($req);
}