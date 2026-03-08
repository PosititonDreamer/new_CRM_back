<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['text'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$text = $_POST['text'];

$list = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name` LIKE '%$text%'");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_item = [
        "id" => $item["id"],
        "full_name" => $item["full_name"],
        "phone" => $item["phone"],
        "email" => !empty($item["email"]) ? $item["email"] : "",
        "address" => [],
    ];

    $address_list = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id_client` = " . $item["id"]);
    while ($address_item = mysqli_fetch_assoc($address_list)) {
        $new_item["address"][] = [
            "id" => $address_item["id"],
            "address" => $address_item["address"],
            "delivery" => $address_item["delivery"],
        ];
    }

    $new_list[] = $new_item;
}
$req = [
    "messages" => ["Получен список имен"],
    "clients" => $new_list
];

http_response_code(200);
echo json_encode($req);