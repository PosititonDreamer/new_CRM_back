<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST["id"];

$item = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id` = $id");

if (mysqli_num_rows($item) > 0) {
    $item = mysqli_fetch_assoc($item);

    $product = $item["id_product"];
    $warehouse = $item["id_warehouse"];

    mysqli_query($connect, "UPDATE `goods` SET `weight`= 0 WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
    mysqli_query($connect, "DELETE FROM `goods_weight` WHERE `id` = $id");
    $composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $id");
    if (mysqli_num_rows($composite) > 0) {
        $composite_id = mysqli_fetch_assoc($composite)['id'];
        mysqli_query($connect, "DELETE FROM `goods_weight_composite` WHERE `id` = $composite_id");
        mysqli_query($connect, "DELETE FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $composite_id");
    }
}

$req = [
    "messages" => ['Весовой товар успешно удален']
];
http_response_code(200);
echo json_encode($req);
