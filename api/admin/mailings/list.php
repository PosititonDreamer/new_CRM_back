<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `mailings`");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_list[] = [
        "id" => $item["id"],
        "title" => $item["title"],
        "text" => nl2br($item["text"]),
        "start_text" => $item["text"],
    ];
}
$req = [
  'messages' => ['Успешно получен список рассылок'],
  'mailings' => $new_list
];
http_response_code(200);
echo json_encode($req);