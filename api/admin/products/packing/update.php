<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['id', 'packing', 'product'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST["id"];
$packing = $_POST["packing"];
$product = $_POST["product"];

$check = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id_product` = '$product' AND `packing` = $packing");

if (mysqli_num_rows($check) > 0) {
    $item = mysqli_fetch_assoc($check);
    if($item['id'] == $id) {
        mysqli_query($connect, "UPDATE `products_packing` SET `id_product`=$product,`packing`=$packing WHERE `id` = '$id'");
        $req = [
            'messages' => ["Фасовка успешно изменена"],
            "product_packing" => [
                "id" => $id,
                "packing" => $packing,
                "product" => $product
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ["Такая фасовка уже есть"]
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `products_packing` SET `id_product`=$product,`packing`=$packing WHERE `id` = '$id'");
    $req = [
        'messages' => ["Фасовка успешно изменена"],
        "product_packing" => [
            "id" => $id,
            "packing" => $packing,
            "product" => $product
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}