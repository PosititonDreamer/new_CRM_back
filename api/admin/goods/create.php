<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['product', 'warehouse', 'quantity', 'balance', 'few', 'few_very'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

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
    $req = [
        "messages" => ['Такой товар уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    $weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse LIMIT 1");
    $weight = mysqli_num_rows($weight);
    if($weight == 1) {
        $balance = 0;
    }
    mysqli_query($connect, "INSERT INTO `goods`(`id_product`, `id_warehouse`, `quantity`, `balance`, `article`, `few`, `few_very`, `weight`, `price`, `hidden`) VALUES ($product,$warehouse,$quantity,$balance,'$article', $few, $few_very, $weight,$price, 0)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        "messages" => ["Фасованный товар успешно добавлен"],
        "good" => [
            "id" => $last_id,
            "product" => $product,
            "warehouse" => $warehouse,
            "quantity" => $quantity,
            "balance" => $balance,
            "article" => $article,
            "few" => $few,
            "few_very" => $few_very,
            "price" => intval($price),
            "weight" => $weight,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}
