<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `supplies_warehouse` WHERE `hidden` = 0");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $id = $item["id"];
    $warehouse_receive = $item['id_warehouse_receive'];
    $warehouse_give = $item['id_warehouse_give'];
    $supply_list = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id_supply_warehouse` = $id AND `hidden` = 0");
    $new_item = [
        "id" => $id,
        "warehouse_receive" => $warehouse_receive,
        "warehouse_give" => $warehouse_give,
        "list" => [],
    ];
    while ($supply = mysqli_fetch_assoc($supply_list)) {
        $supply_id = $supply["id"];
        $good_receive = $supply["id_good_receive"];
        $good_give = $supply["id_good_give"];
        $good_type = $supply["good_type"];
        $new_item["list"][] = [
            "id" => $supply_id,
            "supply_warehouse" => $id,
            "good_receive" => $good_receive,
            "good_give" => $good_give,
            "good_type" => $good_type,
        ];
    }
    $new_list[] = $new_item;
}
$req = [
    "messages" => ["Список связанных складов успешно получен"],
    "supplies" => $new_list,
];
http_response_code(200);
echo json_encode($req);