<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_GET['warehouse'];

$list = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id_warehouse` = $warehouse AND `hidden` = 0 ORDER BY `goods_consumable`.`sort` ASC");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_item = [
        "id" => $item['id'],
        "title" => $item['title'],
        "balance" => $item['balance'],
        "few" => $item['few'],
        "few_very" => $item['few_very'],
        "sort" => $item['sort'],
        "binding" => []
    ];
    $id = $item['id'];
    $binding = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good_consumable` = $id");
    while ($binding_item = mysqli_fetch_assoc($binding)) {
        $new_item["binding"][] = [
            "id" => $binding_item['id'],
            "consumable" => $binding_item['id_good_consumable'],
            "good" => $binding_item['id_good']

        ];
    }
    $new_list[] = $new_item;
}
$req = [
    "messages" => ["Получен список расходников"],
    "goods_consumable" => $new_list
];
http_response_code(200);
echo json_encode($req);
