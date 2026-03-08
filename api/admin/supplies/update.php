<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id', 'list'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$list = json_decode($_POST['list'], true);

$list_supplies = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id_supply_warehouse` = $id AND `hidden` = 0");
while ($item = mysqli_fetch_assoc($list_supplies)) {
    $item_id = $item['id'];
    $check = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply_warehouse_connection` = $item_id");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($connect, "UPDATE `supplies_warehouse_connection` SET `hidden` = 1 WHERE `id` = $item_id");
    } else {
        mysqli_query($connect, "DELETE FROM `supplies_warehouse_connection` WHERE `id` = '$item_id'");
    }
}

$new_list = [];
foreach ($list as $item) {
    $good_receive = $item['good_receive'];
    $good_give = $item['good_give'];
    $good_type = $item['good_type'];
    mysqli_query($connect, "INSERT INTO `supplies_warehouse_connection`(`id_supply_warehouse`, `id_good_receive`, `id_good_give`, `good_type`, `hidden`) VALUES ($id,$good_receive,$good_give,'$good_type', 0)");
    $last_id_item = mysqli_insert_id($connect);
    $new_list[] = [
        "id" => $last_id_item,
        "supply_warehouse" => $id,
        "good_receive" => $good_receive,
        "good_give" => $good_give,
        "good_type" => $good_type,
    ];
}
$req = [
    "messages" => ["Связь складов успешно изменена"],
    "supply" => [
        "id" => $id,
        "list" => $new_list,
    ]
];
http_response_code(200);
echo json_encode($req);