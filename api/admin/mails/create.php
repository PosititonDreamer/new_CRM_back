<?php
require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['order_type', 'mail_type', 'min_orders', 'max_orders', 'text'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$order_type = $_POST['order_type'];
$mail_type = $_POST['mail_type'];
$min_orders = $_POST['min_orders'];
$max_orders = $_POST['max_orders'];
$text = trim($_POST['text']);

mysqli_query($connect, "INSERT INTO `mails`(`order_type`, `mail_type`, `min_orders`, `max_orders`, `text`) VALUES ('$order_type','$mail_type',$min_orders,$max_orders,'$text')");
$last_id = mysqli_insert_id($connect);
$req = [
  'messages' => ['Письмо успешно создано'],
  'mail' => [
      'id' => $last_id,
      'text' => $text,
      'order_type' => $order_type,
      'mail_type' => $mail_type,
      'min_orders' => $min_orders,
      'max_orders' => $max_orders,
  ]
];
http_response_code(200);
echo json_encode($req);