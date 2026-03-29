<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";
require_once __DIR__ . "/functions.php";

$messages = check_data(['orders'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$orders = json_decode($_POST['orders'], true);
$date = date("Y-m-d");
$time = date("H:i:s");

foreach ($orders as $order) {
    mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=4 WHERE `id` = $order");
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order,4,'$date', '$time')");
    mysqli_query($connect, "UPDATE `salaries_assembler` SET `send`= 1, `date`= '$date' WHERE `id_order` = $order");
    send_track_mail($connect, $order);

    if(file_exists(__DIR__ . "/../../files/$order.pdf")) {
        unlink(__DIR__ . "/../../files/$order.pdf");
    }
}

$req = [
    "messages" => ['Заказы успешно отправлены']
];
http_response_code(200);
echo json_encode($req);