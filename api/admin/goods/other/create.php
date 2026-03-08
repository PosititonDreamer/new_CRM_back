<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['warehouse', 'type', 'title', 'balance', 'few', 'few_very'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_POST['warehouse'];
$type = $_POST['type'];
$title = $_POST['title'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];

$check = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = '$warehouse' AND `id_good_other_type` = '$type' AND `title` = '$title'");
if (mysqli_num_rows($check) > 0) {
    $req = [
        'messages' => ['Уже есть такая коробка или магнит']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    $sort = 100;
    $last = mysqli_query($connect, "SELECT `sort` FROM `goods_other` ORDER BY `goods_other`.`sort` DESC LIMIT 1");
    if (mysqli_num_rows($last) > 0) {
        $last = mysqli_fetch_assoc($last);
        $sort = floor($last["sort"] / 100) * 100 + 100;
    }
    mysqli_query($connect, "INSERT INTO `goods_other`(`id_warehouse`, `id_good_other_type`, `title`, `balance`, `few`, `few_very`, `sort`, `hidden`) VALUES ($warehouse,$type,'$title',$balance,$few,$few_very,$sort,0)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        "messages" => ['Коробка или магнит добавлены'],
        "good_other" => [
            "id" => $last_id,
            "sort" => $sort,
            "title" => $title,
            "type" => $type,
            "balance" => $balance,
            "few" => $few,
            "few_very" => $few_very,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}