<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id', 'track'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$track = str_replace(" ", "", $_POST['track']);
$date = date("Y-m-d");
$time = date("H:i:s");
$order = mysqli_query($connect, "SELECT * FROM orders WHERE id = '$id'");
$order = mysqli_fetch_assoc($order);
$address_id = $order['id_client_address'];
$delivery = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
$delivery = mysqli_fetch_assoc($delivery)['delivery'];

if($order['id_order_status'] == 6) {
    mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=7, `track`='$track' WHERE `id` = $id");
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,7,'$date', '$time')");
} else {
    mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=1, `track`='$track' WHERE `id` = $id");
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,1,'$date', '$time')");
}

$req = [
    "messages" => ['Трек-номер успешно добавлены'],
    "track" => $track,
];
http_response_code(200);
echo json_encode($req);

