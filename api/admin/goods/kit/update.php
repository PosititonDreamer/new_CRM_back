<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id','warehouse', 'number', 'title', 'list'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$warehouse = $_POST['warehouse'];
$number = $_POST['number'];
$title = $_POST['title'];
$list = json_decode($_POST['list'], true);
$view_comment = $_POST['view_comment'] == 'true' ? 1 : 0;
$comment = '';

if($view_comment) {
    $messages = check_data(['comment'], $_POST);
    require_once __DIR__ . "/../../../helpers/check_messages.php";
    $comment = $_POST['comment'];
}

$check = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `number` = $number AND `title` = '$title' AND `id_warehouse` = $warehouse");
if(mysqli_num_rows($check) > 0){
    $check = mysqli_fetch_assoc($check);
    if($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `goods_kit` SET `number`=$number,`title`='$title', `view_comment`= $view_comment, `comment` = '$comment' WHERE `id` = $id");
        mysqli_query($connect, "DELETE FROM `goods_kit_list` WHERE `id_good_kit` = $id");
        $new_list = [];
        foreach ($list as $item) {
            $good = $item['good'];
            $quantity = $item['quantity'];
            mysqli_query($connect, "INSERT INTO `goods_kit_list`(`id_good_kit`, `id_good`, `quantity`) VALUES ($id,$good,$quantity)");
            $item_id = mysqli_insert_id($connect);
            $new_list[] = [
                "id" => $item_id,
                "good" => $good,
                "quantity" => $quantity,
            ];
        }
        $req = [
            "messages" => ['Набор успешно изменен'],
            "good_kit" => [
                "id" => $id,
                "number" => $number,
                "title" => $title,
                "warehouse" => $warehouse,
                "view_comment" => $view_comment == 1,
                "comment" => $comment,
                "list" => $new_list
            ],
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Такой набор уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `goods_kit` SET `number`=$number,`title`='$title', `view_comment`= $view_comment, `comment` = '$comment' WHERE `id` = $id");
    mysqli_query($connect, "DELETE FROM `goods_kit_list` WHERE `id_good_kit` = $id");
    $new_list = [];
    foreach ($list as $item) {
        $good = $item['good'];
        $quantity = $item['quantity'];
        mysqli_query($connect, "INSERT INTO `goods_kit_list`(`id_good_kit`, `id_good`, `quantity`) VALUES ($id,$good,$quantity)");
        $item_id = mysqli_insert_id($connect);
        $new_list[] = [
            "id" => $item_id,
            "good" => $good,
            "quantity" => $quantity,
        ];
    }
    $req = [
        "messages" => ['Набор успешно изменен'],
        "good_kit" => [
            "id" => $id,
            "number" => $number,
            "title" => $title,
            "warehouse" => $warehouse,
            "view_comment" => $view_comment == 1,
            "comment" => $comment,
            "list" => $new_list
        ],
    ];
    http_response_code(200);
    echo json_encode($req);
}