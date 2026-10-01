<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";
require_once __DIR__ . "/functions_blank.php";

$id = $_POST['id'];

move_uploaded_file($_FILES["blank"]["tmp_name"], __DIR__ . "/../../files/$id.pdf");

$check_order = mysqli_query($connect, "SELECT `orders`.`id`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`delivery` = 'Яндекс Доставка' OR `delivery` = '5post(Пятерочка)') AND `orders`.`id` = $id");
if(mysqli_num_rows($check_order) > 0) {
    transformBlank($id);
}

$req = [
    "messages" => ['Бланк успешно добавлен'],
];
http_response_code(200);
echo json_encode($req);