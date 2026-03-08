<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['product', 'warehouse', 'balance', 'few', 'few_very', 'composite'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$product = $_POST['product'];
$warehouse = $_POST['warehouse'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$composite = $_POST['composite'] == 'true' ? 1 : 0;

if($composite) {
    $messages = check_data(['composite_list'], $_POST);
    require_once __DIR__ . "/../../../helpers/check_messages.php";
}

$composite_list = $_POST['composite_list'];

$check = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
if (mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ['Такой весовой товар уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `goods_weight`(`id_product`, `id_warehouse`, `balance`, `few`, `few_very`, `composite`) VALUES ($product,$warehouse,$balance,$few,$few_very,$composite)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        "messages" => ["Весовой товар успешно добавлен"],
        "good_weight" => [
            "id" => $last_id,
            "product" => $product,
            "warehouse" => $warehouse,
            "balance" => $balance,
            "few" => $few,
            "few_very" => $few_very,
            "composite" => $composite,
        ]
    ];
    mysqli_query($connect, "UPDATE `goods` SET `weight`= 1, `balance` = 0 WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
    if($composite) {
        mysqli_query($connect, "INSERT INTO `goods_weight_composite`(`id_good_weight`) VALUES ($last_id)");
        $last_id_composite = mysqli_insert_id($connect);
        $req['good_weight']['composite_list'] = [];
        $composite_goods = json_decode($composite_list, true);
        foreach ($composite_goods as $composite_good) {
            $weight_composite = $composite_good['weight_composite'];
            $proportion = $composite_good['proportion'];
            mysqli_query($connect, "INSERT INTO `goods_weight_composite_proportion`(`id_good_weight_composite`, `id_good_weight`, `proportion`) VALUES ($last_id_composite ,$weight_composite, $proportion)");
            $last_id_proportion = mysqli_insert_id($connect);
            $req['good_weight']['composite_list'][] = [
              "id" => $last_id_proportion,
              "composite_id" => $last_id_composite,
              "weight" => $weight_composite,
              "proportion" => $proportion
            ];
        }
    }
    http_response_code(200);
    echo json_encode($req);
}