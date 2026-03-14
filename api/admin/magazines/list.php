<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['warehouse', 'date_start', 'date_end'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$warehouse = $_POST["warehouse"];
$date_start = $_POST["date_start"];
$date_end = $_POST["date_end"];

$list = mysqli_query($connect, "SELECT * FROM `magazines` WHERE `id_warehouse` = $warehouse AND `date` <= '$date_end' AND `date` >= '$date_start' ORDER BY `magazines`.`id` ASC");

$new_goods = [];
$new_weight = [];
$new_consumable = [];
$new_other = [];
$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $item_id = $item["id"];

    $new_item = [
        "id" => $item_id,
        "date" => $item["date"],
        "type" => $item["type"],
        "supply_type" => $item['type'] === 'supply' ? $item["supply_type"] : null,
        "list" => [
            'good' => [],
            "weight" => [],
            "consumable" => [],
            "other" => [],

        ]
    ];

    $magazines_list = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $item_id");

    while ($magazine_item = mysqli_fetch_assoc($magazines_list)) {
        $magazine_type = $magazine_item["type"];
        $new_item['list'][$magazine_type][] = [
            "good" => $magazine_item["id_good"],
            "balance" => $magazine_item["balance"],
            "type_view" => $magazine_item["type_view"],
        ];
    }

    $new_list[] = $new_item;
}

$goods = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_warehouse` = $warehouse AND `weight` = 0 AND `hidden` = 0");
$weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_warehouse` = $warehouse AND `composite` = 0");
$other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = $warehouse AND `hidden` = 0 ORDER BY `goods_other`.`sort` ASC");
$consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id_warehouse` = $warehouse AND `hidden` = 0 ORDER BY `goods_consumable`.`sort` ASC");

while ($good = mysqli_fetch_assoc($goods)) {
    $new_goods[] = [
        "id" => $good["id"],
        "product" => $good["id_product"],
        "quantity" => $good["quantity"],
    ];
}

while ($good = mysqli_fetch_assoc($weight)) {
    $new_weight[] = [
        "id" => $good["id"],
        "product" => $good["id_product"],
    ];
}

while ($good = mysqli_fetch_assoc($consumable)) {
    $new_consumable[] = [
        "id" => $good["id"],
        "title" => $good["title"],
    ];
}


while ($good = mysqli_fetch_assoc($other)) {
    $new_other[] = [
        "id" => $good["id"],
        "title" => $good["title"],
    ];
}

$req = [
    "messages" => ['Список журнала успешно получен'],
    "list" => $new_list,
    "goods" => $new_goods,
    "weight" => $new_weight,
    "consumable" => $new_consumable,
    "other" => $new_other,
];
http_response_code(200);
echo json_encode($req);