<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id', 'balance'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$balance = $_POST['balance'];

$good = mysqli_query($connect, "SELECT `balance`, `id_warehouse` FROM `goods_weight` WHERE `id` = $id");
$good = mysqli_fetch_assoc($good);
$current_balance = $good['balance'];
$warehouse = $good['id_warehouse'];

mysqli_query($connect, "UPDATE `goods_weight` SET `balance` = $balance WHERE `id` = $id");
$req = [
    "messages" => ["Остаток весового товара успешно изменен"],
    "balance" => $balance
];
http_response_code(200);
echo json_encode($req);

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
        $check_magazine_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $id AND `type` = 'weight'");
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
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'weight',$count,'$type')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','hand','$warehouse')");
        $last_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'weight',$count,'$type')");
    }
}