<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['title', 'list'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$title = $_POST['title'];
$list = json_decode($_POST['list'], true);

mysqli_query($connect, "INSERT INTO `products_connection`(`title`) VALUES ('$title')");
$last_id = mysqli_insert_id($connect);

$new_list = [];

foreach ($list as $value) {
    mysqli_query($connect, "INSERT INTO `products_connection_list`(`id_product`, `id_product_connection`) VALUES ($value,$last_id)");
    $new_id = mysqli_insert_id($connect);
    $new_list[] = [
        "id" => $new_id,
        "product" => $value,
    ];
}

$req = [
    "messages" => ['Связь продуктов успешно создана'],
    "products_connection" => [
        "id" => $last_id,
        "title" => $title,
        "list" => $new_list,
    ]
];
http_response_code(200);
echo json_encode($req);