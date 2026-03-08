<?php
require_once __DIR__ . "/../../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `hidden` = 0");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "title" => $item["title"],
    ];
}

$req = [
    'messages' => ["Получен список единиц измерения"],
    'measure_units' => $new_list
];
http_response_code(200);
echo json_encode($req);