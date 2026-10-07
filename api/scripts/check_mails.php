<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../orders/functions_mail.php";
$date = date("Y-m-d");
$mails = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `date` = '$date'");

if (mysqli_num_rows($mails) > 0) {
    while ($mail = mysqli_fetch_assoc($mails)) {
        $order_id = $mail['id_order'];
        $type = $mail['type'];
        send_mail($connect, $order_id, $type);
    }
}
