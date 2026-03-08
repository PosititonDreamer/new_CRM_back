<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `promos` ORDER BY `promos`.`date_start` ASC");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "title" => $item["title"],
        "date_start" => $item["date_start"],
        "date_end" => $item["date_end"],
    ];
}

$req = [
    "messages" => ['Успешно получен список промокодов'],
    "promos" => $new_list
];
http_response_code(200);
echo json_encode($req);