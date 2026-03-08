<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `hidden` = 0");

$list_types = mysqli_query($connect, "SELECT * FROM `warehouses_type`");

$new_list = [];
$new_list_types = [];

while ($item = mysqli_fetch_assoc($list)) {
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

while ($item = mysqli_fetch_assoc($list_types)) {
    $new_list_types[] = [
        "id" => $item["id"],
        "title" => $item["title"],
    ];
}

$req = [
    "messages" => ["Получен список складов"],
    "warehouses" => $new_list,
    "warehouses_type" => $new_list_types
];
http_response_code(200);
echo json_encode($req);