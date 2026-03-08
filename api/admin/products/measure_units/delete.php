<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST["id"];

$check = mysqli_query($connect, "SELECT * FROM `products` WHERE `id_measure_unit` = '$id'");

if(mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ["Нельзя удалить, единица измерения уже используется в продуктах"]
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `measure_units` WHERE `id` = '$id'");
    $req = [
        'messages' => ['Единица измерения успешно удалена']
    ];
    http_response_code(200);
    echo json_encode($req);
}