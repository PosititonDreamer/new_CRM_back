<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_GET['id'];

$order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $id");
$order = mysqli_fetch_assoc($order);

$client_id = $order['id_client'];
$address_id = $order['id_client_address'];
$status = $order['id_order_status'];
$track = $order['track'];
$comment = $order['comment'];
$site_comment = '';
$number = $order['number'];

$new_status_list = [];
$new_composition_list = [];
$new_good_list = [];

$status_list = mysqli_query($connect, "SELECT * FROM `orders_process` WHERE `id_order` = $id");

while ($status_item = mysqli_fetch_assoc($status_list)) {
    $new_status_list[] = [
        "status" => $status_item['id_order_status'],
        "date" => $status_item['date'],
        "time" => $status_item['time'],
    ];
}

$composition_list = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $id ORDER BY `orders_composition`.`present` ASC");

while ($composition = mysqli_fetch_assoc($composition_list)) {
    $type = "good";

    if ($composition['id_order_composition_type'] == 2) {
        $type = "kit";
        $kit_id = $composition['id_good'];
        $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `id` = $kit_id");
        $kit = mysqli_fetch_assoc($kit);
        if($kit['view_comment'] == 1) {
            $site_comment .= $kit['comment'] . "\n ";
        }
    }

    if ($composition['id_order_composition_type'] == 3) {
        $type = "other";
    }

    if ($composition['id_order_composition_type'] == 4) {
        $type = "sale";
    }

    $new_composition_list[] = [
        "id" => $composition['id'],
        "good" => $composition['id_good'],
        "quantity" => $composition['quantity'],
        "present" => $composition['present'],
        "type" => $type,
    ];
}

$goods_list = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");

while ($good = mysqli_fetch_assoc($goods_list)) {
    if ($good['id_order_good_type'] == 1) {
        $good_id = $good['id_good'];
        $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
        $good_item = mysqli_fetch_assoc($good_item);
        if (isset($new_good_list["good-$good_id"])) {
            $new_good_list["good-$good_id"]['quantity'] += $good['quantity'];
        } else {
            $new_good_list["good-$good_id"] = [
                "id" => $good_id,
                "quantity" => $good['quantity'],
                "packing" => $good_item['quantity'],
                "product" => $good_item['id_product'],
                "ready" => $good['ready'],
                "type" => "good"
            ];
        }
    } else {
        $good_id = $good['id_good'];
        $good_item = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
        $good_item = mysqli_fetch_assoc($good_item);
        if (isset($new_good_list["other-$good_id"])) {
            $new_good_list["other-$good_id"]['quantity'] += $good['quantity'];
        } else {
            $new_good_list["other-$good_id"] = [
                "id" => $good_id,
                "quantity" => $good['quantity'],
                "title" => $good_item['title'],
                "ready" => $good['ready'],
                "type" => "other"
            ];
        }
    }
}

$client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
$client = mysqli_fetch_assoc($client);
$address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
$address = mysqli_fetch_assoc($address);

$orders_length = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id_client` = $client_id");
$orders_length = mysqli_num_rows($orders_length);

if(!empty(trim($track))) {
    if ($address['delivery'] == 'Яндекс Доставка') {
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

$req = [
    "messages" => ['Информация о заказе успешно получена'],
    "order" => [
        "id" => $id,
        "track" => $track,
        "comment" => $comment,
        "site_comment" => nl2br($site_comment),
        "status" => $status,
        "status_list" => $new_status_list,
        "composition_list" => $new_composition_list,
        "number" => $number,
        "goods_list" => [],
        "client" => [
            "id" => $client['id'],
            "full_name" => $client['full_name'],
            "phone" => $client['phone'],
            "email" => $client['email'],
            "orders_length" => $orders_length,
        ],
        "address" => [
            "id" => $address['id'],
            "address" => $address['address'],
            "delivery" => $address['delivery'],
        ],
        "assembler" => '',
        "creator" => '',
        "payed" => is_null($order['number']) ? 0 : 1
    ]
];

foreach ($new_good_list as $good) {
    $req['order']['goods_list'][] = $good;
}

$assembler = mysqli_query($connect, "SELECT * FROM `salaries_assembler` WHERE `id_order` = $id");
if (mysqli_num_rows($assembler) > 0) {
    $assembler = mysqli_fetch_assoc($assembler);
    $worker_id = $assembler['id_worker'];
    $worker = mysqli_query($connect, "SELECT * FROM `workers` WHERE `id` = $worker_id");
    $worker = mysqli_fetch_assoc($worker);
    $req['order']['assembler'] = $worker['name'];
}

if (!empty($order['id_worker'])) {
    $worker_id = $order['id_worker'];
    $creator = mysqli_query($connect, "SELECT * FROM `workers` WHERE `id` = $worker_id");
    if (mysqli_num_rows($creator) > 0) {
        $creator = mysqli_fetch_assoc($creator);
        $req['order']['creator'] = $creator['name'];
    }
} else {
    $req['order']['creator'] = 'Сайт';
}

http_response_code(200);
echo json_encode($req);