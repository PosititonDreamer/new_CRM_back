<?php
require_once __DIR__ . '/../../connect.php';

$list = mysqli_query($connect, "SELECT * FROM `mails`");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        'id' => $item['id'],
        'text' => $item['text'],
        'order_type' => $item['order_type'],
        'mail_type' => $item['mail_type'],
        'min_orders' => $item['min_orders'],
        'max_orders' => $item['max_orders'],
    ];
}

$req = [
    'messages' => ['Список писем успешко получен'],
    'mails' => $new_list,
];

http_response_code(200);
echo json_encode($req);