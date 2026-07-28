<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['packing'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$packing = $_POST["packing"];

$check =  mysqli_query($connect, "SELECT `id` FROM `presents` WHERE `id_product_packing` = $packing");

if(mysqli_num_rows($check) > 0){
    $req = [
        'messages' => ['Такой подарок уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `presents`(`id_product_packing`) VALUES ($packing)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        'messages' => ["Подарок успешно добавлен"],
        'present' => [
            'id' => $last_id,
            'packing' => $packing
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}