<?php
$connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_crm_system');
$old_connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_default');

$orders = mysqli_query($old_connect, "SELECT * FROM orders");

while ($order = mysqli_fetch_assoc($orders)) {
    $name = $order['name'];
    $name = strtolower(trim($name));
    $name = mb_convert_case($name, MB_CASE_TITLE, "UTF-8");
    $email = $order['email'];
    $address = trim($order['address']);
    $delivery = $order['delivery'];

    $check_client = mysqli_query($connect, "SELECT * FROM clients WHERE full_name = '$name'");
    if (mysqli_num_rows($check_client) > 0) {
        $check_client = mysqli_fetch_assoc($check_client);
        $client_id = $check_client['id'];
        $check_address = mysqli_query($connect, "SELECT * FROM clients_address WHERE `id_client` = $client_id AND `address` = '$address'");

        if(!empty($phone) && empty($client_item['phone'])) {
            mysqli_query($connect, "UPDATE `clients` SET `phone`='$phone' WHERE `id` = $client_id");
        }

        if(!empty($email) && empty($client_item['email'])) {
            mysqli_query($connect, "UPDATE `clients` SET `email`='$email' WHERE `id` = $client_id");
        }

        if (mysqli_num_rows($check_address) > 0) {
            continue;
        } else {
            mysqli_query($connect, "INSERT INTO `clients_address`(`id_client`, `address`, `delivery`) VALUES ($client_id,'$address', '$delivery')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `clients`(`full_name`, `phone`, `email`, `messenger`) VALUES ('$name',null,'$email', '')");
        $client_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `clients_address`(`id_client`, `address`, `delivery`) VALUES ($client_id,'$address', '$delivery')");
    }
}