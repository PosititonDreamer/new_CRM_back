<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['title', 'id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$title = $_POST["title"];
$id = $_POST["id"];

$check = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `title` = '$title'");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `measure_units` SET `title`='$title' WHERE `id` = '$id'");
        $req = [
            'messages' => ["Единица измерения успешно изменена"],
            'measure_unit' => [
                'id' => $id,
                'title' => $title,
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            'messages' => ["Такое название уже есть"]
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `measure_units` SET `title`='$title' WHERE `id` = '$id'");
    $req = [
        'messages' => ["Единица измерения успешно изменена"],
        'measure_unit' => [
            'id' => $id,
            'title' => $title,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}