<?php
require __DIR__ . "/../connect.php";
require __DIR__ . "/../clients/functions.php";

$orders = mysqli_query($connect, "SELECT `orders`.`id`, `clients`.`full_name` FROM orders JOIN `clients` ON `orders`.`id_client` = `clients`.`id`");
while ($order = mysqli_fetch_assoc($orders)) {
    $id = $order['id'];
    $full_name = $order['full_name'];
    mysqli_query($connect, "UPDATE `orders` SET `full_name`='$full_name' WHERE `id` = $id");
}

$clients = mysqli_query($connect, "SELECT * FROM `clients` WHERE `phone` IS NOT NULL AND `email` != ''");
while ($client = mysqli_fetch_assoc($clients)) {
    $id = $client['id'];
    $check_client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $id");
    if(mysqli_num_rows($check_client) > 0) {
        $full_name = $client['full_name'];
        $phone = $client['phone'];
        $email = $client['email'];

        $find_clients = mysqli_query($connect, generate_request($full_name, $phone, $email) . " AND `id` != $id");
        if(mysqli_num_rows($find_clients) > 0){
            while ($find_client = mysqli_fetch_assoc($find_clients)) {
                $find_client_id = $find_client['id'];
                mysqli_query($connect, "UPDATE `clients_address` SET `id_client`=$id WHERE `id_client`=$find_client_id");
                mysqli_query($connect, "UPDATE `orders` SET `id_client`=$id WHERE `id_client`=$find_client_id");
                mysqli_query($connect, "DELETE FROM `clients` WHERE `id` = $find_client_id");
            }
        }
    }
}

