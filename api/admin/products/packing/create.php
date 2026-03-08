<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['product', 'packing'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$product = $_POST["product"];
$packing = $_POST["packing"];

$check = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id_product` = '$product' AND `packing` = $packing");
if (mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ["Такая фасовка уже есть"]
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `products_packing`(`id_product`, `packing`) VALUES ($product,$packing)");
    $last_id = mysqli_insert_id($connect);
    $req = [
      "messages" => ["Фасовка создаана"],
      "product_packing" => [
          "id" => $last_id,
          "packing" => $packing,
          "product" => $product
      ]
    ];
    http_response_code(200);
    echo json_encode($req);
}