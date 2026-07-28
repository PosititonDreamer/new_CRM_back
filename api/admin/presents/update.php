<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id','packing'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$packing = $_POST["packing"];

$check =  mysqli_query($connect, "SELECT `id` FROM `presents` WHERE `id_product_packing` = $packing AND `id` != $id");

if(mysqli_num_rows($check) > 0){
    $req = [
        'messages' => ['Такой подарок уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "UPDATE `presents` SET `id_product_packing`='$packing' WHERE `id` = '$id'");
    $req = [
        'messages' => ["Подарок успешно изменен"],
        'present' => [
            'id' => $id,
            'packing' => $packing
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}