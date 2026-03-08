<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['text'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$text = $_POST['text'];

$list = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name` LIKE '%$text%'");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $new_item = [
        "id" => $item["id"],
        "full_name" => $item["full_name"],
        "email" => $item["email"],
        "phone" => $item["phone"],
        "address" => [],
        "orders" => []
    ];

    $address_list = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id_client` = " . $item["id"]);
    while ($address_item = mysqli_fetch_assoc($address_list)) {
        $new_item["address"][] = [
            "id" => $address_item["id"],
            "address" => $address_item["address"],
            "delivery" => $address_item["delivery"],
        ];
    }

    $orders_list = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_client` = " . $item["id"]);

    while ($order_item = mysqli_fetch_assoc($orders_list)) {
        $order_id = $order_item["id"];
        $address_id = $order_item["id_client_address"];
        $length_goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $order_id");
        $length_goods = mysqli_num_rows($length_goods);

        $track = $order_item["track"];
        $delivery = $order_item["delivery"];

        if ($delivery == 'Яндекс Доставка') {
            if(strpos($track, 'LO-')) {
                $track_explode = explode('-', $track);
                $track = $track_explode[0] . '-' . wordwrap($track_explode[1], 3, ' ', true);
            } else {
                $track = wordwrap($track, 3, ' ', true);
            }
        } else {
            $track = wordwrap($track, 3, ' ', true);
        }

        $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id`=$address_id");
        $address = mysqli_fetch_assoc($address);

        $new_item["orders"][] = [
            "id" => $order_id,
            "track" => $track,
            "comment" => $order_item['comment'],
            "date" => $order_item['date'],
            "client" => $item["full_name"],
            "address" => $address['address'],
            "delivery" => $delivery,
            "status" => $order_item['id_order_status'],
            "goods" => $length_goods,
            "blank" => false
        ];
    }

    $new_list[] = $new_item;
}
$req = [
    "messages" => ["Получен список клиентов"],
    "clients" => $new_list
];

http_response_code(200);
echo json_encode($req);