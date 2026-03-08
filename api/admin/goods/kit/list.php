<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_GET['warehouse'];

$list = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `id_warehouse` = '$warehouse' AND `hidden` = 0");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $id = $item['id'];
    $new_item = [
        "id" => $id,
        "warehouse" => $item['id_warehouse'],
        "number" => $item['number'],
        "title" => $item['title'],
        "comment" => $item['comment'],
        "view_comment" => $item['view_comment'] == 1,
        "list" => []
    ];

    $kit_list = mysqli_query($connect, "SELECT * FROM `goods_kit_list` WHERE `id_good_kit` = '$id'");
    while ($kit_item = mysqli_fetch_assoc($kit_list)) {
        $new_item["list"][] = [
            "id" => $kit_item['id'],
            "good" => $kit_item['id_good'],
            "quantity" => $kit_item['quantity'],
        ];
    }
    $new_list[] = $new_item;
}

$req = [
    "messages" => ['Получен список наборов'],
    "goods_kits" => $new_list
];
http_response_code(200);
echo json_encode($req);
