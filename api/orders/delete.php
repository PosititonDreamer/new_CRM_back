<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_POST['id'];

$check_multy = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $id AND `id_order_composition_type` = 5");
if(mysqli_num_rows($check_multy) > 0) {
    while ($item = mysqli_fetch_assoc($check_multy)) {
        $sale_id = $item['id_good'];
        mysqli_query($connect, "UPDATE `sales_multy_list` SET `used`=0 WHERE `id` = $sale_id");
    }
}

mysqli_query($connect, "DELETE FROM `orders` WHERE `id`=$id");
mysqli_query($connect, "DELETE FROM `orders_good` WHERE `id_order`=$id");
mysqli_query($connect, "DELETE FROM `orders_process` WHERE `id_order`=$id");
mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order`=$id");

$req = [
    "messages" => ['Заказ успешно удален']
];
http_response_code(200);
echo json_encode($req);
