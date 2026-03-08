<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['title'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$title = $_POST["title"];
$check = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `title` = '$title'");

if (mysqli_num_rows($check) > 0) {
    $req = [
      "messages" => ["Такое название уже есть"]
    ];
    http_response_code(404);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `measure_units`(`title`, `hidden`) VALUES ('$title',0)");
    $id = mysqli_insert_id($connect);
    $req = [
        'messages' => ["Новая единица измерения добавлена"],
        'measure_unit' => [
            'id' => $id,
            'title' => $title,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}