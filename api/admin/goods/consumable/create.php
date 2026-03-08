<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['warehouse', 'title', 'balance', 'few', 'few_very'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_POST['warehouse'];
$title = $_POST['title'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$binding = json_decode($_POST['binding'], true);

$check = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `title` = '$title' AND `id_warehouse` = '$warehouse' AND `hidden` = 0");
if (mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ['Такой расходник уже добавлен']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    $sort = 100;
    $last = mysqli_query($connect, "SELECT `sort` FROM `goods_consumable` ORDER BY `goods_consumable`.`sort` DESC LIMIT 1");
    if (mysqli_num_rows($last) > 0) {
        $last = mysqli_fetch_assoc($last);
        $sort = floor($last["sort"] / 100) * 100 + 100;
    }

    mysqli_query($connect, "INSERT INTO `goods_consumable`(`id_warehouse`, `title`, `balance`, `few`, `few_very`, `sort`, `hidden`) VALUES ($warehouse,'$title',$balance,$few,$few_very,$sort,0)");
    $last_id = mysqli_insert_id($connect);
    $binding_list = [];
    foreach ($binding as $item) {
        mysqli_query($connect, "INSERT INTO `goods_consumable_binding`(`id_good_consumable`, `id_good`) VALUES ($last_id,$item)");
        $item_id = mysqli_insert_id($connect);
        $binding_list[] = [
            "id" => $item_id,
            "consumable" => $last_id,
            "good" => $item
        ];
    }

    $req = [
        "messages" => ['Расходник успешно добавлен'],
        "goods_consumable" => [
            "id" => $last_id,
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

