<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];

mysqli_query($connect, "DELETE FROM `products_connection` WHERE `id` = $id");
mysqli_query($connect, "DELETE FROM `products_connection_list` WHERE `id_product_connection` = $id");

$req = [
  "messages" => ['Связь продуктов успешно удалена']
];
http_response_code(200);
echo json_encode($req);
