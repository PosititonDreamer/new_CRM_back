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

$check = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `title` = '$title' AND `id_warehouse` = '$warehouse' AND `hidden` = 0");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
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