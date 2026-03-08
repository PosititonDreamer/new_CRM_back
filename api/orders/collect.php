<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id', 'boxes', 'worker'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$worker = $_POST['worker'];
$date = date("Y-m-d");
$time = date("H:i:s");
$boxes = json_decode($_POST['boxes']);

foreach ($boxes as $box) {
    mysqli_query($connect, "INSERT INTO `orders_good`(`id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($id,$box,2,1,0)");
}

$order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $id");
$order = mysqli_fetch_assoc($order);

if($order['id_order_status'] == 3) {
    mysqli_query($connect, "UPDATE `orders` SET `id_order_status` = 6 WHERE `id` = $id");
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,6,'$date', '$time')");
} else {
    mysqli_query($connect, "UPDATE `orders` SET `id_order_status` = 2 WHERE `id` = $id");
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,2,'$date', '$time')");
}

mysqli_query($connect, "INSERT INTO `salaries_assembler`(`id_order`, `id_worker`, `date`, `send`, `ready`) VALUES ($id,$worker,'$date',0,0)");

$list = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");

while ($item = mysqli_fetch_assoc($list)) {
    $item_id = $item['id'];
    $quantity = $item['quantity'];

    mysqli_query($connect, "UPDATE `orders_good` SET `ready`= 1 WHERE `id`=$item_id");

    $good_id = $item['id_good'];
    $type = $item['id_order_good_type'];

    if($type == 1){
        $check_good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
        $check_good = mysqli_fetch_assoc($check_good);
        mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$good_id,1,1,$quantity,'$date')");
        if($check_good['weight'] == 0) {
            mysqli_query($connect, "UPDATE `goods` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
            $consumable_list = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $good_id");
            while ($consumable = mysqli_fetch_assoc($consumable_list)) {
                $consumable_id = $consumable['id_good_consumable'];
                mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`-$quantity WHERE `id` = $consumable_id");
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$consumable_id,2,1,$quantity,'$date')");
            }
        } else {
            $consumable_list = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $good_id");
            while ($consumable = mysqli_fetch_assoc($consumable_list)) {
                $consumable_id = $consumable['id_good_consumable'];
                mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`-$quantity WHERE `id` = $consumable_id");
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$consumable_id,2,1,$quantity,'$date')");
            }

            $warehouse = $check_good['id_warehouse'];
            $product = $check_good['id_product'];
            $weight_quantity = intval($check_good['quantity']) * $quantity;

            $check_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
            $check_weight = mysqli_fetch_assoc($check_weight);
            $weight_id = $check_weight['id'];

            if($check_weight['composite'] == 0) {
                mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`-$weight_quantity WHERE `id` = $weight_id");
            } else {
                $composite_id = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id");
                $composite_id = mysqli_fetch_assoc($composite_id)['id'];

                $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $weight_id");

                while ($composite = mysqli_fetch_assoc($composite_list)) {
                    $composite_weight_id = $composite['id_good_weight'];
                    $composite_quantity = $weight_quantity / 100 * intval($composite['proportion']);
                    mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`-$composite_quantity WHERE `id` = $composite_weight_id");
                }
            }
        }
    }

    if($type == 2) {
        mysqli_query($connect, "UPDATE `goods_other` SET `balance`=`balance`-$quantity WHERE `id` = $good_id");
        mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($id,$good_id,3,1,$quantity,'$date')");
    }
}

$req = [
    "messages" => ['Заказ успешно собран'],
    "id" => $id,
];
http_response_code(200);
echo json_encode($req);
