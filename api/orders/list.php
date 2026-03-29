<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['status'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$status = $_GET['status'];

if($status == 6) {
    $response = "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE  (`id_order_status` = 6) OR (`id_order_status` = 7)";
} elseif($status == 3) {
    $response = "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE  (`id_order_status` = 6) OR (`id_order_status` = 3)";
} elseif($status == 4) {
    $find_status = $_GET['find_status'];

    $find_text = "id_order_status = $find_status";

    if($find_status == 2) {
        $find_text = '(id_order_status = 2 OR id_order_status = 6 OR id_order_status = 7)';
    }

    $response = " SELECT  orders.id, orders.id_warehouse, orders.id_client, orders.id_client_address, orders.id_order_status, orders.track, orders.number, orders.comment, clients_address.delivery, op.date AS date FROM orders JOIN clients_address  ON clients_address.id = orders.id_client_address JOIN ( SELECT id_order, MAX(date) AS date FROM orders_process WHERE $find_text GROUP BY id_order ) op  ON op.id_order = orders.id WHERE orders.id_order_status = $status ";
} else {
    $response = "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE  `id_order_status` = $status";
}


if (isset($_GET['date_start']) && $_GET['date_start'] != null) {
    $date_start = $_GET['date_start'];

    if($status == 4) {
        $response .= " AND `op`.`date` >= '$date_start'";
    } else {
        $response .= " AND `date` >= '$date_start'";
    }
}

if (isset($_GET['date_end']) && $_GET['date_end'] != null) {
    $date_end = $_GET['date_end'];

    if($status == 4) {
        $response .= " AND `op`.`date` <= '$date_end'";
    } else {
        $response .= " AND `date` <= '$date_end'";
    }
}

if (isset($_GET['delivery']) && $_GET['delivery'] != null) {
    $delivery = $_GET['delivery'];
    $response .= " AND `delivery` = '$delivery'";
}

if (isset($_GET['sort']) && $_GET['sort'] != null) {
    $sort = $_GET['sort'] == 'old' ? 'ASC' : 'DESC';
    $response .= " ORDER BY `id` $sort";
}

$max_length = mysqli_query($connect, $response);
$max_length = mysqli_num_rows($max_length);

$next_page = null;

if ($max_length > (intval($_GET['page']) * 50 + 50)) {
    $next_page = intval($_GET['page']) + 1;
}

$response .= " LIMIT " . intval($_GET['page']) * 50 . ", 50";

$list = mysqli_query($connect, $response);
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
    }

    $length_goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");
    $length_goods = mysqli_num_rows($length_goods);

    $quantity = mysqli_query($connect, "SELECT SUM(quantity) AS quantity FROM `orders_good` WHERE id_order = $id");
    $quantity = mysqli_fetch_assoc($quantity);

    $sale = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $id AND `id_order_composition_type` = 4");
    $sale = mysqli_num_rows($sale) > 0;
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id`= $client_id");
    $client = mysqli_fetch_assoc($client);

    $length_orders = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_client` = $client_id");
    $length_orders = mysqli_num_rows($length_orders);
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
        "sale" => $sale,
        "date" => $date,
        "client" => $client['full_name'],
        "delivery" => $delivery,
        "show_delivery" => $show_delivery,
        "status" => $status,
        "goods" => $length_goods,
        "orders" => $length_orders,
        "quantity" => $quantity['quantity'],
        "blank" => file_exists(__DIR__ . "/../../files/$id.pdf")
    ];
}


$req = [
    "messages" => ['Список заказов успешно получен'],
    "orders" => $new_orders,
    "next_page" => $next_page,
];
http_response_code(200);
echo json_encode($req);