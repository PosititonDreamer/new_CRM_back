<?php
require_once __DIR__ . "/../connect.php";
$date = date("Y-m-d");

$warehouses = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `hidden` = 0");
while ($warehouse = mysqli_fetch_assoc($warehouses)) {
    $warehouse_id = $warehouse['id'];
    $check = mysqli_query($connect, "SELECT * FROM magazines WHERE `date` = '$date' AND `type` = 'everyday' AND `id_warehouse` = '$warehouse_id'");
    if(mysqli_num_rows($check) == 0){
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','everyday',$warehouse_id)");
        $last_id = mysqli_insert_id($connect);
        $goods = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_warehouse` = $warehouse_id AND `weight` = 0 AND hidden = 0");
        $goods_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_warehouse` = $warehouse_id AND `composite` = 0");
        $goods_consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id_warehouse` = $warehouse_id AND `hidden` = 0");
        $goods_other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = $warehouse_id AND `hidden` = 0");

        while ($good = mysqli_fetch_assoc($goods)) {
            $good_id = $good['id'];
            $balance = $good['balance'];
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'good',$balance)");
        }

        while ($good = mysqli_fetch_assoc($goods_weight)) {
            $good_id = $good['id'];
            $balance = $good['balance'];
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'weight',$balance)");
        }

        while ($good = mysqli_fetch_assoc($goods_consumable)) {
            $good_id = $good['id'];
            $balance = $good['balance'];
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'consumable',$balance)");
        }

        while ($good = mysqli_fetch_assoc($goods_other)) {
            $good_id = $good['id'];
            $balance = $good['balance'];
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`) VALUES ($last_id,$good_id,'other',$balance)");
        }
    }
}


