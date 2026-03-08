<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_GET['id'];

$supply = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id` = $id");
$supply = mysqli_fetch_assoc($supply);

$list = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $id");
$statuses_list = mysqli_query($connect, "SELECT * FROM `supplies_process` WHERE `id_supply` = $id");
$new_list = [];
$new_statuses_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item['id'],
        "supply" => $item['id_supply'],
        "supply_warehouse_connection" => $item['id_supply_warehouse_connection'],
        "quantity" => $item['quantity'],
        "ready" => $item['ready'],
    ];
}

while ($status = mysqli_fetch_assoc($statuses_list)) {
    $new_statuses_list[] = [
        "id" => $status['id'],
        "supply" => $status['id_supply'],
        "supply_process_status" => $status['id_supply_process_status'],
        "date" => $status['date'],
    ];
}

$req = [
    "messages" => ["Успешно получена детальная информация о поставке"],
    "supply" => [
        "id" => $supply['id'],
        "supply_warehouse" => $supply['id_supply_warehouse'],
        "supply_status" => $supply['id_supply_status'],
        "date" => $supply['date'],
    ],
    "list" => $new_list,
    "status_list" => $new_statuses_list,
];
http_response_code(200);
echo json_encode($req);
