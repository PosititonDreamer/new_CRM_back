<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['title', 'measure_unit', 'client_title', 'weight', 'id', 'sort'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST["id"];
$measure_unit = $_POST["measure_unit"];
$title = $_POST["title"];
$client_title = $_POST["client_title"];
$show_title = $_POST["show_title"] ?? '';
$sort = $_POST["sort"];
$weight = $_POST["weight"];

$check = find_product_title($connect, $title);
if ($check) {
    if ($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `products` SET `id_measure_unit`=$measure_unit,`title`='$title',`show_title`='$show_title',`sort`=$sort,`weight`=$weight, `client_title` = '$client_title' WHERE `id` = '$id'");
        $req = [
            'messages' => ["Продукт успешно изменен"],
            'product' => [
                'id' => $id,
                'title' => $title,
                'show_title' => $show_title,
                'measure_unit' => $measure_unit,
                'client_title' => $client_title,
                'sort' => $sort,
                'weight' => $weight,
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            'messages' => ["Такое название уже есть"]
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    mysqli_query($connect, "UPDATE `products` SET `id_measure_unit`=$measure_unit,`title`='$title',`show_title`='$show_title',`sort`=$sort,`weight`=$weight , `client_title` = '$client_title' WHERE `id` = '$id'");
    $req = [
        'messages' => ["Продукт успешно изменен"],
        'product' => [
            'id' => $id,
            'title' => $title,
            'show_title' => $show_title,
            'measure_unit' => $measure_unit,
            'client_title' => $client_title,
            'sort' => $sort,
            'weight' => $weight,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}