<?php
require_once __DIR__ . "/../connect.php";

$orders_created = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 1");
$orders_assembled = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 2");
$orders_processed = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 3");
$orders_send = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 4");
$orders_returned = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 5");
$orders_assembled_not_track = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 6");
$orders_assembled_add_track = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 7");
$orders_delivered = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_order_status` = 4 AND (`delivered` = 0 OR `keeped` = 0)");
$orders_unprocessed = mysqli_query($connect, "SELECT * FROM `orders_unprocessed`");

$req = [
    "messages" => ['Получен список заказов'],
    "orders_info" => [
        "created" => mysqli_num_rows($orders_created),
        "assembled" => mysqli_num_rows($orders_assembled),
        "processed" => mysqli_num_rows($orders_processed),
        "returned" => mysqli_num_rows($orders_returned),
        "send" => mysqli_num_rows($orders_send),
        "assembled_not_track" => mysqli_num_rows($orders_assembled_not_track),
        "assembled_add_track" => mysqli_num_rows($orders_assembled_add_track),
        "unprocessed" => mysqli_num_rows($orders_unprocessed),
        "delivered" => mysqli_num_rows($orders_delivered)
    ]
];
http_response_code(200);
echo json_encode($req);
