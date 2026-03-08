<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['client', 'clients_join'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$client = $_POST['client'];
$clients_join = json_decode($_POST['clients_join'], true);

foreach ($clients_join as $client_item) {
    mysqli_query($connect, "UPDATE `clients_address` SET `id_client`=$client WHERE `id_client`=$client_item");
    mysqli_query($connect, "UPDATE `orders` SET `id_client`=$client WHERE `id_client`=$client_item");
    mysqli_query($connect, "DELETE FROM `clients` WHERE `id` = $client_item");
}

$req = [
    "messages" => ['Клиенты успешно объединены'],
];
http_response_code(200);
echo json_encode($req);