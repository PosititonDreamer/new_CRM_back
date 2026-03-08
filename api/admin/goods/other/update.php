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

$check = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_warehouse` = '$warehouse' AND `id_good_other_type` = '$type' AND `title` = '$title'");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
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