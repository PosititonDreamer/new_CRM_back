<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id', 'product', 'warehouse', 'quantity', 'balance'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$product = $_POST['product'];
$warehouse = $_POST['warehouse'];
$quantity = $_POST['quantity'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$article = $_POST['article'] ?? "";
$price = $_POST['price'] ?? 0;

$check = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product` = $product AND `id_warehouse` = $warehouse AND `quantity` = $quantity");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `goods` SET `id_product`=$product, `quantity`=$quantity,`balance`=$balance,`article`='$article',`few`=$few,`few_very`=$few_very, `price`=$price WHERE `id` = $id");
        $req = [
            "messages" => ['Фасованный товар успешно изменен'],
            "good" => [
                "id" => $id,
                "product" => $product,
                "warehouse" => $warehouse,
                "quantity" => $quantity,
                "balance" => $balance,
                "article" => $article,
                "few" => $few,
                "few_very" => $few_very,
                "price" => intval($price),
                "weight" => $check['weight'] == 0 ? 0 : $check['weight'],
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Такой фасованный товар уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    $check = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $id");
    $check = mysqli_fetch_assoc($check);
    mysqli_query($connect, "UPDATE `goods` SET `id_product`=$product, `quantity`=$quantity,`balance`=$balance,`article`='$article',`few`=$few,`few_very`=$few_very, `price`=$price WHERE `id` = $id");
    $req = [
        "messages" => ['Фасованный товар успешно изменен'],
        "good" => [
            "id" => $id,
            "product" => $product,
            "warehouse" => $warehouse,
            "quantity" => $quantity,
            "balance" => $balance,
            "article" => $article,
            "few" => $few,
            "few_very" => $few_very,
            "price" => intval($price),
            "weight" => $check['weight'] == 0 ? 0 : $check['weight'],
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}