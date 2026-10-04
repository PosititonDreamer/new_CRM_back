<?php
require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id','order_type', 'mail_type', 'min_orders', 'max_orders', 'text'], $_POST);

$id = $_POST['id'];
$order_type = $_POST['order_type'];
$mail_type = $_POST['mail_type'];
$min_orders = $_POST['min_orders'];
$max_orders = $_POST['max_orders'];
$text = trim($_POST['text']);

require_once __DIR__ . "/../../helpers/check_messages.php";

mysqli_query($connect, "UPDATE `mails` SET `order_type`='$order_type',`mail_type`='$mail_type',`min_orders`=$min_orders,`max_orders`=$max_orders,`text`='$text' WHERE `id` = $id");

$req = [
    'messages' => ['Письмо успешно изменено'],
    'mail' => [
        'id' => $id,
        'text' => $text,
        'order_type' => $order_type,
        'mail_type' => $mail_type,
        'min_orders' => $min_orders,
        'max_orders' => $max_orders,
    ]
];
http_response_code(200);
echo json_encode($req);