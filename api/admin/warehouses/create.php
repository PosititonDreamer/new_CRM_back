<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['type', 'title', 'description', 'few', 'few_very', 'few_other', 'few_very_other'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$type = $_POST['type'];
$title = $_POST['title'];
$description = $_POST['description'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$few_other = $_POST['few_other'];
$few_very_other = $_POST['few_very_other'];

$check = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `title` = '$title'");
if (mysqli_num_rows($check) > 0) {
    $req = [
        'messages' => ['Склад с таким названием уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `warehouses` (`id_type`, `title`, `description`, `few`, `few_very`, `few_other`, `few_very_other`, `hidden`) VALUES ($type, '$title', '$description', $few, $few_very, $few_other, $few_very_other, 0)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        'messages' => ['Склад успешно добавлен'],
        'warehouse' => [
            'id' => $last_id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'few' => $few,
            'few_very' => $few_very,
            'few_other' => $few_other,
            'few_very_other' => $few_very_other,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}


