<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['warehouse_receive', 'warehouse_give', 'list'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$warehouse_receive = $_POST['warehouse_receive'];
$warehouse_give = $_POST['warehouse_give'];
$list = json_decode($_POST['list'], true);

$check = mysqli_query($connect, "SELECT * FROM `supplies_warehouse` WHERE `id_warehouse_receive`=$warehouse_receive AND `id_warehouse_give`=$warehouse_give AND `hidden` = 0");
if (mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ['Такая связь складов уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `supplies_warehouse`(`id_warehouse_receive`, `id_warehouse_give`, `hidden`) VALUES ($warehouse_receive,$warehouse_give,0)");
    $last_id = mysqli_insert_id($connect);
    $new_list = [];
    foreach ($list as $item) {
        $good_receive = $item['good_receive'];
        $good_give = $item['good_give'];
        $good_type = $item['good_type'];
        mysqli_query($connect, "INSERT INTO `supplies_warehouse_connection`(`id_supply_warehouse`, `id_good_receive`, `id_good_give`, `good_type`, `hidden`) VALUES ($last_id,$good_receive,$good_give,'$good_type',0)");
        $last_id_item = mysqli_insert_id($connect);
        $new_list[] = [
            "id" => $last_id_item,
            "supply_warehouse" => $last_id,
            "good_receive" => $good_receive,
            "good_give" => $good_give,
            "good_type" => $good_type,
        ];
    }
    $req = [
        "messages" => ["Связь складов успешно добавлена"],
        "supply" => [
            "id" => $last_id,
            "warehouse_receive" => $warehouse_receive,
            "warehouse_give" => $warehouse_give,
            "list" => $new_list,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}