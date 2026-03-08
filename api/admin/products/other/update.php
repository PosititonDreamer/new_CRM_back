<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['id', 'packing'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST["id"];
$packing = $_POST["packing"];

$item = mysqli_query($connect, "SELECT * FROM `products_other` WHERE id = '$id'");

if(mysqli_num_rows($item) > 0) {
    $item = mysqli_fetch_assoc($item);
    mysqli_query($connect, "UPDATE `products_other` SET `id_packing` = $packing WHERE `id` = '$id'");
    $req = [
        "messages" => ["Кривой продукт успешно изменен"],
        "product_other" => [
            "id" => $id,
            "packing" => $packing,
            "title" => $item["title"]
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    $req = [
        'messages' => ["Кривой продукт не найден"]
    ];
    http_response_code(400);
    echo json_encode($req);
}

