<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['supply', 'list'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$supply = $_POST['supply'];
$list = json_decode($_POST['list'], true);

$date = date("Y-m-d");

mysqli_query($connect, "INSERT INTO `supplies`(`id_supply_warehouse`, `id_supply_status`, `date`) VALUES ($supply,1,'$date')");
$last_id = mysqli_insert_id($connect);
mysqli_query($connect, "INSERT INTO `supplies_process`(`id_supply`, `id_supply_process_status`, `date`) VALUES ($last_id,1,'$date')");

foreach ($list as $item) {
    $warehouse_connection = $item['warehouse_connection'];
    $quantity = $item['quantity'];
    mysqli_query($connect, "INSERT INTO `supplies_list`( `id_supply`, `id_supply_warehouse_connection`, `quantity`, `ready`) VALUES ($last_id,$warehouse_connection,$quantity,0)");
    $item_id = mysqli_insert_id($connect);
}

$length = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $last_id");
$length = mysqli_num_rows($length);

$req = [
    "messages" => ["Поставка успешно создана"],
    "supply" => [
        "id" => $last_id,
        "supply_warehouse" => $supply,
        "supply_status" => 1,
        "date" => $date,
        "length" => $length,
    ]
];
http_response_code(200);
echo json_encode($req);