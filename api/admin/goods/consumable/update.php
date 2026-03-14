<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id', 'warehouse', 'title', 'balance', 'few', 'few_very', 'sort'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$warehouse = $_POST['warehouse'];
$title = $_POST['title'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$sort = $_POST['sort'];
$binding = json_decode($_POST['binding'], true);

$current_balance = 0;

$check = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `title` = '$title' AND `id_warehouse` = '$warehouse' AND `hidden` = 0");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
        $current_balance = $check['balance'];
        mysqli_query($connect, "UPDATE `goods_consumable` SET `title`='$title',`balance`=$balance,`few`=$few,`few_very`=$few_very,`sort`=$sort WHERE `id` = '$id'");
        mysqli_query($connect, "DELETE FROM `goods_consumable_binding` WHERE `id_good_consumable` = $id");
        $binding_list = [];
        foreach ($binding as $item) {
            mysqli_query($connect, "INSERT INTO `goods_consumable_binding`(`id_good_consumable`, `id_good`) VALUES ($id,$item)");
            $item_id = mysqli_insert_id($connect);
            $binding_list[] = [
                "id" => $item_id,
                "consumable" => $id,
                "good" => $item
            ];
        }
        $req = [
            "messages" => ['Расходник успешно изменен'],
            "good_consumable" => [
                "id" => $id,
                "title" => $title,
                "balance" => $balance,
                "few" => $few,
                "few_very" => $few_very,
                "sort" => $sort,
                "binding" => $binding_list
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Такой расходник уже успешно добавлен']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    $check = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE $id = $id");
    $check = mysqli_fetch_assoc($check);
    $current_balance = $check['balance'];
    mysqli_query($connect, "UPDATE `goods_consumable` SET `title`='$title',`balance`=$balance,`few`=$few,`few_very`=$few_very,`sort`=$sort WHERE `id` = '$id'");
    mysqli_query($connect, "DELETE FROM `goods_consumable_binding` WHERE `id_good_consumable` = $id");
    $binding_list = [];
    foreach ($binding as $item) {
        mysqli_query($connect, "INSERT INTO `goods_consumable_binding`(`id_good_consumable`, `id_good`) VALUES ($id,$item)");
        $item_id = mysqli_insert_id($connect);
        $binding_list[] = [
            "id" => $item_id,
            "consumable" => $id,
            "good" => $item
        ];
    }
    $req = [
        "messages" => ['Расходник успешно изменен'],
        "good_consumable" => [
            "id" => $id,
            "title" => $title,
            "balance" => $balance,
            "few" => $few,
            "few_very" => $few_very,
            "sort" => $sort,
            "binding" => $binding_list
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
        $check_magazine_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $id AND `type` = 'consumable'");
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
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'consumable',$count,'$type')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','hand','$warehouse')");
        $last_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'consumable',$count,'$type')");
    }
}