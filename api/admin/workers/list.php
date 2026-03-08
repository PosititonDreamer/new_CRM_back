<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `workers` WHERE `hidden` = 0 AND `id` != 1");
$list_rules = mysqli_query($connect, "SELECT * FROM `workers_rule` WHERE `title` != 'Админ'");
$list_warehouses = mysqli_query($connect, "SELECT * FROM `workers_warehouse`");

$new_list = [];
$new_list_rules = [];
$new_list_warehouses = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "rule" => $item["id_worker_rule"],
        "name" => $item["name"],
        "description" => $item["description"],
        "salary" => $item["salary"],
        "token" => $item["token"],
    ];
}

while ($item = mysqli_fetch_assoc($list_rules)) {
    $new_list_rules[] = [
        "id" => $item["id"],
        "title" => $item["title"],
    ];
}

while ($item = mysqli_fetch_assoc($list_warehouses)) {
    $new_list_warehouses[] = [
        "id" => $item["id"],
        "worker" => $item["id_worker"],
        "warehouse" => $item["id_warehouse"],
    ];
}

$req = [
    "messages" => ['Получен список работников'],
    "workers" => $new_list,
    "rules" => $new_list_rules,
    "warehouses" => $new_list_warehouses,
];
http_response_code(200);
echo json_encode($req);