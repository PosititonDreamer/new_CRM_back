<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id', 'type', 'title', 'description', 'few', 'few_very', 'few_other', 'few_very_other'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$type = $_POST['type'];
$title = $_POST['title'];
$description = $_POST['description'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$few_other = $_POST['few_other'];
$few_very_other = $_POST['few_very_other'];

$check = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `title`='$title'");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `warehouses` SET `id_type`=$type,`title`='$title',`description`='$description', `few` = $few, `few_very` = $few_very, `few_other`=$few_other, `few_very_other`=$few_very_other WHERE `id`=$id");
        $req = [
          "messages" => ["Склад успешно изменен"],
          "warehouse" => [
              "id" => $id,
              "type" => $type,
              "title" => $title,
              "description" => $description,
              'few' => $few,
              'few_very' => $few_very,
              'few_other' => $few_other,
              'few_very_other' => $few_very_other,
          ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Склад с таким названием уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `warehouses` SET `id_type`=$type,`title`='$title',`description`='$description', `few` = $few, `few_very` = $few_very, `few_other`=$few_other, `few_very_other`=$few_very_other WHERE `id`=$id");
    $req = [
        "messages" => ["Склад успешно изменен"],
        "warehouse" => [
            "id" => $id,
            "type" => $type,
            "title" => $title,
            "description" => $description,
            'few' => $few,
            'few_very' => $few_very,
            'few_other' => $few_other,
            'few_very_other' => $few_very_other,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}