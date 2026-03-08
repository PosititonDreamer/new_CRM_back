<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['address', 'address_join'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$address = $_POST['address'];
$address_join = json_decode($_POST['address_join'], true);

foreach ($address_join as $address_item) {
    mysqli_query($connect, "UPDATE `orders` SET `id_client_address`=$address WHERE `id_client_address`=$address_item");
    mysqli_query($connect, "DELETE FROM `clients_address` WHERE `id` = $address_item");
}

$req = [
    "messages" => ['Адреса клиента успешно объединены'],
];
http_response_code(200);
echo json_encode($req);