<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];
$date = date("Y-m-d");

$supply = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id` = $id");
$supply = mysqli_fetch_assoc($supply);

$supply_warehouse_id = $supply['id_supply_warehouse'];
$warehouse_receive = mysqli_query($connect, "SELECT `id_warehouse_receive` FROM `supplies_warehouse` WHERE `id` = $supply_warehouse_id");
$warehouse_receive = mysqli_fetch_assoc($warehouse_receive)["id_warehouse_receive"];

mysqli_query($connect, "UPDATE `supplies` SET `id_supply_status` = 3 WHERE `id` = $id");
mysqli_query($connect, "INSERT INTO `supplies_process`(`id_supply`, `id_supply_process_status`, `date`) VALUES ($id,3,'$date')");

$list = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $id");

$check = mysqli_query($connect, "SELECT * FROM `magazines` WHERE `date` = '$date' AND `type` = 'supply' AND `supply_type` = 'income' AND `id_warehouse` = $warehouse_receive LIMIT 1");
if(mysqli_num_rows($check) > 0){
    $check = mysqli_fetch_assoc($check);
    $last_id = $check['id'];
} else {
    mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`, `supply_type`) VALUES ('$date','supply',$warehouse_receive, 'income')");
    $last_id = mysqli_insert_id($connect);
}

while ($item = mysqli_fetch_assoc($list)) {
    $id = $item['id'];
    $supply_warehouse_connection = $item['id_supply_warehouse_connection'];
    $quantity = $item['quantity'];

    $good = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id` = $supply_warehouse_connection");
    $good = mysqli_fetch_assoc($good);

    $good_id = $good['id_good_receive'];
    $good_type = $good['good_type'];

    if($good_type == "good"){
        mysqli_query($connect, "UPDATE `goods` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        $balance = mysqli_query($connect, "SELECT `balance` FROM `goods` WHERE `id` = $good_id");
        $balance = mysqli_fetch_assoc($balance)['balance'];
        $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'good'");
        if(mysqli_num_rows($check_good) > 0){
            $check_good = mysqli_fetch_assoc($check_good);
            $item_id = $check_good['id'];
            mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $quantity WHERE `id` = $item_id");
        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'good',$quantity)");
        }
    }

    if($good_type == 'consumable') {
        mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        $balance = mysqli_query($connect, "SELECT `balance` FROM `goods_consumable` WHERE `id` = $good_id");
        $balance = mysqli_fetch_assoc($balance)['balance'];
        $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'consumable'");
        if(mysqli_num_rows($check_good) > 0){
            $check_good = mysqli_fetch_assoc($check_good);
            $item_id = $check_good['id'];
            mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $quantity WHERE `id` = $item_id");
        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'consumable',$quantity)");
        }
    }

    if($good_type == 'other') {
        mysqli_query($connect, "UPDATE `goods_other` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        $balance = mysqli_query($connect, "SELECT `balance` FROM `goods_other` WHERE `id` = $good_id");
        $balance = mysqli_fetch_assoc($balance)['balance'];
        $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'other'");
        if(mysqli_num_rows($check_good) > 0){
            $check_good = mysqli_fetch_assoc($check_good);
            $item_id = $check_good['id'];
            mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $quantity WHERE `id` = $item_id");
        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'other',$quantity)");
        }
    }

    if($good_type == 'weight') {
        mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        $balance = mysqli_query($connect, "SELECT `balance` FROM `goods_weight` WHERE `id` = $good_id");
        $balance = mysqli_fetch_assoc($balance)['balance'];
        $check_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $good_id AND `type` = 'weight'");
        if(mysqli_num_rows($check_good) > 0){
            $check_good = mysqli_fetch_assoc($check_good);
            $item_id = $check_good['id'];
            mysqli_query($connect, "UPDATE `magazines_good` SET `balance` = `balance` + $quantity WHERE `id` = $item_id");
        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'weight',$quantity)");
        }
    }
}

$req = [
    "messages" => ['Поставка успешно принята'],
    "supply_status" => 3
];
http_response_code(200);
echo json_encode($req);