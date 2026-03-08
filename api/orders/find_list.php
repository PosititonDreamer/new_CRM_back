<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['type', 'text'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$type = $_POST['type'];
$text = $_POST['text'];

if($type == 'track') {
    $list = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `track` LIKE '%$text%'");
    $new_orders = [];

    while ($item = mysqli_fetch_assoc($list)) {
        $id = $item['id'];
        $track = $item['track'];
        $comment = $item['comment'];
        $date = $item['date'];
        $client_id = $item['id_client'];
        $track = $item['track'];
        $delivery = $item['delivery'];
        $status = $item['id_order_status'];

        if(!empty(trim($track))) {
            if($delivery == 'Яндекс Доставка') {
                if(strpos($track, 'LO-')) {
                    $track_explode = explode('-', $track);
                    $track = $track_explode[0] . '-' . wordwrap($track_explode[1], 3, ' ', true);
                } else {
                    $track = wordwrap($track, 3, ' ', true);
                }
            } else {
                $track = wordwrap($track, 3, ' ', true);
            }
        }

        $quantity = mysqli_query($connect, "SELECT SUM(quantity) AS quantity FROM `orders_good` WHERE id_order = $id");
        $quantity = mysqli_fetch_assoc($quantity);

        $length_orders = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_client` = $client_id");
        $length_orders = mysqli_num_rows($length_orders);

        $length_goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");
        $length_goods = mysqli_num_rows($length_goods);

        $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id`= $client_id");
        $client = mysqli_fetch_assoc($client);

        $show_delivery = $delivery;
        if($delivery == 'CDEK') {
            $show_delivery = 'CD';
        } elseif($delivery == 'Почта России') {
            $show_delivery = 'ПТ';
        } elseif($delivery == 'Яндекс Доставка') {
            $show_delivery = 'ЯН';
        } else {
            $show_delivery = 'BB';
        }

        $new_orders[] = [
            "id" => $id,
            "track" => $track,
            "comment" => $comment,
            "date" => $date,
            "client" => $client['full_name'],
            "delivery" => $delivery,
            "show_delivery" => $show_delivery,
            "orders" => $length_orders,
            "quantity" => $quantity['quantity'],
            "status" => $status,
            "goods" => $length_goods
        ];
    }


    $req = [
        "messages" => ['Список заказов успешно получен'],
        "orders" => $new_orders,
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    $list = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name` LIKE '%$text%'");
    $orders = [];

    while ($item = mysqli_fetch_assoc($list)) {
        $id = $item['id'];
        $orders_list = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_client` = $id");
        while ($order = mysqli_fetch_assoc($orders_list)) {
            $orders[] = $order;
        }
    }

    $new_orders = [];

    foreach ($orders as $item)  {
        $id = $item['id'];
        $track = $item['track'];
        $comment = $item['comment'];
        $date = $item['date'];
        $client_id = $item['id_client'];
        $track = $item['track'];
        $delivery = $item['delivery'];
        $status = $item['id_order_status'];
        $quantity = mysqli_query($connect, "SELECT SUM(quantity) AS quantity FROM `orders_good` WHERE id_order = $id");
        $quantity = mysqli_fetch_assoc($quantity);

        $length_orders = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_client` = $client_id");
        $length_orders = mysqli_num_rows($length_orders);

        if(!empty(trim($track))) {
            if($delivery == 'Яндекс Доставка') {
                if(strpos($track, 'LO-')) {
                    $track_explode = explode('-', $track);
                    $track = $track_explode[0] . '-' . wordwrap($track_explode[1], 3, ' ', true);
                } else {
                    $track = wordwrap($track, 3, ' ', true);
                }
            } else {
                $track = wordwrap($track, 3, ' ', true);
            }
        }

        $length_goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");
        $length_goods = mysqli_num_rows($length_goods);

        $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id`= $client_id");
        $client = mysqli_fetch_assoc($client);

        $show_delivery = $delivery;
        if($delivery == 'CDEK') {
            $show_delivery = 'CD';
        } elseif($delivery == 'Почта России') {
            $show_delivery = 'ПТ';
        } elseif($delivery == 'Яндекс Доставка') {
            $show_delivery = 'ЯН';
        } else {
            $show_delivery = 'BB';
        }

        $new_orders[] = [
            "id" => $id,
            "track" => $track,
            "comment" => $comment,
            "date" => $date,
            "client" => $client['full_name'],
            "delivery" => $delivery,
            "show_delivery" => $show_delivery,
            "status" => $status,
            "orders" => $length_orders,
            "quantity" => $quantity['quantity'],
            "goods" => $length_goods
        ];
    }


    $req = [
        "messages" => ['Список заказов успешно получен'],
        "orders" => $new_orders,
    ];
    http_response_code(200);
    echo json_encode($req);
}
