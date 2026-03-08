<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['worker'], $_GET);

require_once __DIR__ . "/../../helpers/check_messages.php";

$worker = $_GET['worker'];

$list = mysqli_query($connect, "SELECT * FROM `workers_warehouse` WHERE `id_worker` = $worker");

$new_list = [];

while ($warehouse_item = mysqli_fetch_assoc($list)) {
    $warehouse_id = $warehouse_item['id_warehouse'];

    $item = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `id` = $warehouse_id ANd `hidden`=0");
    $item = mysqli_fetch_assoc($item);

    $item_id = $item["id"];
    $check = mysqli_query($connect, "SELECT * FROM `supplies_warehouse` WHERE (`id_warehouse_receive` = $item_id OR `id_warehouse_give` = $item_id) AND `hidden` = 0 ");

    if(mysqli_num_rows($check) > 0) {
        $count = 0;

        while($supply = mysqli_fetch_assoc($check)) {
            $supply_id = $supply["id"];
            $count_supplies = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id_supply_warehouse` = $supply_id AND `id_supply_status` < 3");
            $count += mysqli_num_rows($count_supplies);
        }

        $new_list[] = [
            "id" => $item["id"],
            "title" => $item["title"],
            "description" => $item["description"],
            "type" => $item["id_type"],
            "supply" => true,
            "count" => $count,
        ];
    } else {
        $new_list[] = [
            "id" => $item["id"],
            "title" => $item["title"],
            "description" => $item["description"],
            "type" => $item["id_type"],
            "supply" => false,
            "count" => 0
        ];
    }
}
$req = [
    "messages" => ["Получен список складов"],
    "warehouses" => $new_list,
];
http_response_code(200);
echo json_encode($req);