<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['id', 'title', 'date', 'date_start', 'list'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$title = $_POST['title'];
$keywords = $_POST['keywords'];
$sum = $_POST['sum'];
$date = $_POST['date'];
$date_start = $_POST['date_start'];
$sum_max = $_POST['sum_max'] ?? 'NULL';
$list = json_decode($_POST['list'], true);

$check = mysqli_query($connect, "SELECT * FROM `sales` WHERE `title` = '$title' AND `hidden` = 0");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `sales` SET `title`='$title',`keywords`='$keywords',`sum`=$sum,`date`='$date', `date_start`='$date_start', `sum_max` = $sum_max WHERE `id` = $id");
        mysqli_query($connect, "DELETE FROM `sales_list` WHERE `id_sale` = $id");
        $new_list = [];
        foreach ($list as $item) {
            $good = $item['good'];
            $quantity = $item['quantity'];
            mysqli_query($connect, "INSERT INTO `sales_list`(`id_sale`, `id_good`, `quantity`) VALUES ($id,$good,$quantity)");
            $item_id = mysqli_insert_id($connect);
            $new_list[] = [
                "id" => $item_id,
                "good" => $good,
                "quantity" => $quantity,
            ];
        }
        $req = [
            "messages" => ['Акция успешно изменена'],
            "sale" => [
                "id" => $id,
                "title" => $title,
                "keywords" => $keywords,
                "sum" => $sum,
                "sum_max" => $sum_max === 'NULL' ? null : $sum_max,
                "date" => $date,
                "date_start" => $date_start,
                "active" => true,
                "list" => $new_list
            ],
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Такая акция уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `sales` SET `title`='$title',`keywords`='$keywords',`sum`=$sum,`date`='$date', `date_start`='$date_start', `sum_max` = $sum_max WHERE `id` = $id");
    mysqli_query($connect, "DELETE FROM `sales_list` WHERE `id_sale` = $id");
    $new_list = [];
    foreach ($list as $item) {
        $good = $item['good'];
        $quantity = $item['quantity'];
        mysqli_query($connect, "INSERT INTO `sales_list`(`id_sale`, `id_good`, `quantity`) VALUES ($id,$good,$quantity)");
        $item_id = mysqli_insert_id($connect);
        $new_list[] = [
            "id" => $item_id,
            "good" => $good,
            "quantity" => $quantity,
        ];
    }
    $req = [
        "messages" => ['Акция успешно изменена'],
        "sale" => [
            "id" => $id,
            "title" => $title,
            "keywords" => $keywords,
            "sum" => $sum,
            "sum_max" => $sum_max === 'NULL' ? null : $sum_max,
            "date" => $date,
            "date_start" => $date_start,
            "active" => true,
            "list" => $new_list
        ],
    ];
    http_response_code(200);
    echo json_encode($req);
}