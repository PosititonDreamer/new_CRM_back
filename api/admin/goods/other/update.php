<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id', 'sort', 'warehouse', 'type', 'title', 'balance', 'few', 'few_very'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_POST['warehouse'];
$type = $_POST['type'];
$title = $_POST['title'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$id = $_POST['id'];
$sort = $_POST['sort'];

$current_balance = 0;

$check = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = '$warehouse' AND `id_good_other_type` = '$type' AND `title` = '$title'");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
        $current_balance = $check['balance'];
        mysqli_query($connect, "UPDATE `goods_other` SET `id_good_other_type`=$type,`title`='$title',`balance`=$balance,`few`=$few,`few_very`=$few_very,`sort`=$sort WHERE `id` = $id");
        $req = [
            "messages" => ['Коробка или магнит успешно изменены'],
            "good_other" => [
                "id" => $id,
                "sort" => $sort,
                "title" => $title,
                "balance" => $balance,
                "type" => $type,
                "few" => $few,
                "few_very" => $few_very,
                "warehouse" => $warehouse,
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            'messages' => ['Уже есть такая коробка или магнит']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    $check = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE $id = $id");
    $check = mysqli_fetch_assoc($check);
    $current_balance = $check['balance'];
    mysqli_query($connect, "UPDATE `goods_other` SET `id_good_other_type`=$type,`title`='$title',`balance`=$balance,`few`=$few,`few_very`=$few_very,`sort`=$sort WHERE `id` = $id");
    $req = [
        "messages" => ['Коробка или магнит успешно изменены'],
        "good_other" => [
            "id" => $id,
            "sort" => $sort,
            "title" => $title,
            "type" => $type,
            "balance" => $balance,
            "few" => $few,
            "few_very" => $few_very,
            "warehouse" => $warehouse,
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
        $check_magazine_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $id AND `type` = 'other'");
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
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'other',$count,'$type')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','hand','$warehouse')");
        $last_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'other',$count,'$type')");
    }
}