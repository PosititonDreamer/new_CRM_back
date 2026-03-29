<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['title', 'list', 'id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$title = $_POST['title'];
$list = json_decode($_POST['list'], true);

mysqli_query($connect, "UPDATE `products_connection` SET `title`='$title' WHERE `id` = $id");
mysqli_query($connect, "DELETE FROM `products_connection_list` WHERE `id_product_connection` = $id");

$new_list = [];

foreach ($list as $value) {
    mysqli_query($connect, "INSERT INTO `products_connection_list`(`id_product`, `id_product_connection`) VALUES ($value,$id)");

    $new_id = mysqli_insert_id($connect);
    $new_list[] = [
        "id" => $new_id,
        "product" => $value,
    ];
}

$req = [
    "messages" => ['Связь продуктов успешно изменена'],
    "products_connection" => [
        "id" => $id,
        "title" => $title,
        "list" => $new_list,
    ]
];
http_response_code(200);
echo json_encode($req);