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

$current_balance = 0;

$check = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product` = $product AND `id_warehouse` = $warehouse AND `quantity` = $quantity");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        $current_balance = $check['balance'];
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
    $current_balance = $check['balance'];
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
if($current_balance !== $balance) {
    if($current_balance < $balance) {
        $type = 'green';
        $count = $balance - $current_balance;
    } else {
        $type = 'red';
        $count = $current_balance - $balance;
    }
    $date = date("Y-m-d");
    $check_magazine = mysqli_query($connect, "SELECT * FROM `magazines` WHERE `id_warehouse` = $warehouse AND `type` = 'hand' AND `date` = '$date'");
    if (mysqli_num_rows($check_magazine) > 0) {
        $check_magazine = mysqli_fetch_assoc($check_magazine);
        $last_id = $check_magazine['id'];
        $check_magazine_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $id AND `type` = 'good'");
        if (mysqli_num_rows($check_magazine_good) > 0) {
            $check_magazine_good = mysqli_fetch_assoc($check_magazine_good);
            $magazine_good_id = $check_magazine_good['id'];
            $magazine_balance = $check_magazine_good['balance'];
            if($count > $magazine_balance) {
                $count = $count - $magazine_balance;
            } else {
                $count = $magazine_balance - $count;
            }
            if($count == 0) {
                mysqli_query($connect, "DELETE FROM `magazines_good` WHERE `id` = $magazine_good_id");
            } else {
                mysqli_query($connect, "UPDATE `magazines_good` SET `balance`= $count, `type_view` = '$type' WHERE `id` = $magazine_good_id");
            }

        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'good',$count,'$type')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','hand','$warehouse')");
        $last_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'good',$count,'$type')");
    }
}