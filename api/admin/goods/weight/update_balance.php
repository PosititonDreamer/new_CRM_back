<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id', 'balance'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$balance = $_POST['balance'];

mysqli_query($connect, "UPDATE `goods_weight` SET `balance` = $balance WHERE `id` = $id");
$req = [
    "messages" => ["Остаток весового товара успешно изменен"],
    "balance" => $balance
];
http_response_code(200);
echo json_encode($req);