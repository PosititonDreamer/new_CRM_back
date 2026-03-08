<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['title', 'measure_unit', 'client_title'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$measure_unit = $_POST["measure_unit"];
$title = $_POST["title"];
$client_title = $_POST["client_title"];
$show_title = $_POST["show_title"] ?? '';

$check = find_product_title($connect,$title);
if ($check) {
    $req = [
        'messages' => ['Продукт с таким названием уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    $sort = 100;
    $last = mysqli_query($connect, "SELECT `sort` FROM `products` ORDER BY `products`.`sort` DESC LIMIT 1");
    if (mysqli_num_rows($last) > 0) {
        $last = mysqli_fetch_assoc($last);
        $sort = floor($last["sort"] / 100) * 100 + 100;
    }
    mysqli_query($connect, "INSERT INTO `products`(`id_measure_unit`, `title`, `show_title`, `client_title`, `sort`, `hidden`) VALUES ($measure_unit,'$title','$show_title', '$client_title' ,$sort, 0)");
    $last_id = mysqli_insert_id($connect);
    $req = [
        'messages' => ["Продукт успешно добавлен"],
        'product' => [
            'id' => $last_id,
            'title' => $title,
            'show_title' => $show_title,
            'measure_unit' => $measure_unit,
            'client_title' => $client_title,
            'sort' => $sort,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}