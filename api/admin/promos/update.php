<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id', 'title', 'date_start', 'date_end'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$title = $_POST['title'];
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];

mysqli_query($connect, "UPDATE `promos` SET `title`='$title',`date_start`='$date_start',`date_end`='$date_end' WHERE `id` = $id");

$req = [
    "messages" => ['Промокод успешно изменен'],
    "promo" => [
        "id" => $id,
        "title" => $title,
        "date_start" => $date_start,
        "date_end" => $date_end,
    ]
];
http_response_code(200);
echo json_encode($req);