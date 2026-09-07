<?php
require __DIR__ . '/../connect.php';

$files = glob(__DIR__ . '/../../files/*.pdf');

foreach ($files as $file) {
    $order_id = explode('.', basename($file))[0];
    $status = mysqli_query($connect, "SELECT `id_order_status` FROM `orders` WHERE `id`=$order_id");
    if(mysqli_num_rows($status) > 0) {
        $status = mysqli_fetch_assoc($status)['id_order_status'];
        if($status == 4 || $status == 5) {
            unlink(__DIR__ . "/../../files/$order_id.pdf");
        }
    } else{
        unlink(__DIR__ . "/../../files/$order_id.pdf");
    }
}