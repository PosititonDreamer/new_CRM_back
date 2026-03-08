<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$date = date("Y-m-d");
$time = date("H:i:s");

$order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $id");
$order = mysqli_fetch_assoc($order);

mysqli_query($connect, "UPDATE `orders` SET `id_order_status` = 5 WHERE `id` = $id");
mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,5,'$date', '$time')");

$list = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_order_or_supply` = $id AND `id_expense_type` = 1 ORDER BY `expenses`.`id_expense_good_type` ASC");

while ($item = mysqli_fetch_assoc($list)) {
    $item_id = $item['id'];
    $quantity = $item['quantity'];
    $good_id = $item['id_good'];
    $type = $item['id_expense_good_type'];

    if($type == 1){
        mysqli_query($connect, "DELETE FROM `expenses` WHERE `id` = $item_id");
        $check_good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
        $check_good = mysqli_fetch_assoc($check_good);
        if($check_good['weight'] == 0) {
            mysqli_query($connect, "UPDATE `goods` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");

        } else {
            $warehouse = $check_good['id_warehouse'];
            $product = $check_good['id_product'];
            $weight_quantity = intval($check_good['quantity']) * $quantity;

            $check_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
            $check_weight = mysqli_fetch_assoc($check_weight);
            $weight_id = $check_weight['id'];

            if($check_weight['composite'] == 0) {
                mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`+$weight_quantity WHERE `id` = $weight_id");
            } else {
                $composite_id = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id");
                $composite_id = mysqli_fetch_assoc($composite_id)['id'];

                $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $weight_id");

                while ($composite = mysqli_fetch_assoc($composite_list)) {
                    $composite_weight_id = $composite['id_good_weight'];
                    $composite_quantity = $weight_quantity / 100 * intval($composite['proportion']);
                    mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`+$composite_quantity WHERE `id` = $composite_weight_id");
                }
            }
        }

    }

    if($type == 2) {
        mysqli_query($connect, "DELETE FROM `expenses` WHERE `id` = $item_id");
        mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");

    }

    if($type == 3) {
        $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
        $other = mysqli_fetch_assoc($other);
        if($other['id_good_other_type'] == 1) {
            mysqli_query($connect, "DELETE FROM `expenses` WHERE `id` = $item_id");
            mysqli_query($connect, "UPDATE `goods_other` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        }
    }
}
$req = [
    "messages" => ['Заказ успешно возвращен']
];
http_response_code(200);
echo json_encode($req);
