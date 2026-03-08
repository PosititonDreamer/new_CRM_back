<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['warehouse', 'client', 'phone', 'address', 'delivery', 'track', 'composition'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";
require_once __DIR__ . "/functions.php";

$warehouse = $_POST['warehouse'];
$client = trim($_POST['client']);
$address = $_POST['address'];
$track = str_replace(" ", "", $_POST['track']);
$comment = trim($_POST['comment']);
$phone = $_POST['phone'];
$delivery = $_POST['delivery'];
$email = $_POST['email'];
$payed = $_POST['payed'] == 'true' ? 1 : 0;
$number = $payed == 1 ? -1 : NULL;
$date = date("Y-m-d");
$time = date("H:i:s");
$composition = json_decode($_POST['composition'], true);
$phone = mb_eregi_replace('[^0-9]', '', $phone);
$worker = $_POST['worker'];

if(strpos($phone, "8") == 0) {
    $phone = "7".substr($phone, 1);
}

if (strpos($phone, "7") === false || strpos($phone, "7") != 0) {
    $phone = "7$phone";
}

$phone = "+$phone";

if($phone == '+7') {
    $phone = '';
}

$check = mysqli_query($connect, "SELECT * FROM `orders` WHERE `track`='$track'");

if(mysqli_num_rows($check) > 0){
    $req = [
        "messages" => ['Заказ с таким трек-номером уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
    die();
}
$client = strtolower(trim($client));
$client = mb_convert_case($client, MB_CASE_TITLE, "UTF-8");
$client_item = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name`='$client'");
if(mysqli_num_rows($client_item) > 0) {
    $client_item = mysqli_fetch_assoc($client_item);
    $client_id = $client_item['id'];
    $client = $client_id;
    mysqli_query($connect, "UPDATE `clients` SET `phone`='$phone', `email`='$email' WHERE `id` = $client_id");

    $address_item = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id_client`=$client_id AND `delivery` = '$delivery' AND `address`='$address'");
    if(mysqli_num_rows($address_item) > 0) {
        $address_item = mysqli_fetch_assoc($address_item);
        $address_id = $address_item['id'];
        $address = $address_id;
    } else {
        mysqli_query($connect, "INSERT INTO `clients_address`(`id_client`, `address`, `delivery`) VALUES ($client_id,'$address','$delivery')");
        $address_id = mysqli_insert_id($connect);
        $address = $address_id;
    }
} else {
    mysqli_query($connect, "INSERT INTO `clients`(`full_name`, `phone`, `email`, `messenger`) VALUES ('$client','$phone','$email', '')");
    $client_id = mysqli_insert_id($connect);
    mysqli_query($connect, "INSERT INTO `clients_address`(`id_client`, `address`, `delivery`) VALUES ($client_id,'$address','$delivery')");
    $address_id = mysqli_insert_id($connect);
    $client = $client_id;
    $address = $address_id;
}
if($number == -1) {
    mysqli_query($connect, "INSERT INTO `orders`(`id_warehouse`, `id_client`, `id_client_address`, `id_order_status`, `track`, `number`, `comment`, `date`, `id_worker`) VALUES ($warehouse,$client,$address,1,'$track','$number','$comment','$date',$worker)");
} else {
    mysqli_query($connect, "INSERT INTO `orders`(`id_warehouse`, `id_client`, `id_client_address`, `id_order_status`, `track`, `comment`, `date`, `id_worker`) VALUES ($warehouse,$client,$address,1,'$track','$comment','$date',$worker)");
}
$sale = false;
$order_id = mysqli_insert_id($connect);
foreach ($composition as $comp) {
    $good = $comp['good'];
    $quantity = $comp['quantity'];
    $present = $comp['present'] ? 1 : 0;
    $type = $comp['type'];

    if($type == 'good') {
        mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id, $good,1, $quantity, $present)");

        $packing = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id`=$good");
        $packing = mysqli_fetch_assoc($packing);
        $packing_quantity = intval($packing['packing']);
        $product_id = $packing['id_product'];
        $good_items = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product`=$product_id AND `id_warehouse`=$warehouse ORDER BY `goods`.`quantity` DESC");

        while($good_item = mysqli_fetch_assoc($good_items)) {
            if($good_item['quantity'] > $packing_quantity) continue;
            if($packing_quantity <= 0) break;
            $good_quantity = $good_item['quantity'];
            $good_id = $good_item['id'];
            if($packing_quantity % $good_quantity == 0) {
                $count = $packing_quantity / $good_quantity * $quantity;
                mysqli_query($connect, "INSERT INTO `orders_good`( `id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_id,1,$count,0)");
                $packing_quantity = 0;
            } else {
                $count = floor($packing_quantity / $good_quantity) * $quantity;
                mysqli_query($connect, "INSERT INTO `orders_good`( `id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_id,1,$count,0)");
                $packing_quantity -= floor($packing_quantity / $good_quantity) * $good_quantity;
            }
        }
    }

    if($type == 'kit') {
        mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id,$good,2,$quantity,0)");

        $kit_list = mysqli_query($connect, "SELECT * FROM `goods_kit_list` WHERE `id_good_kit` = $good");
        while($item = mysqli_fetch_assoc($kit_list)) {
            $good_id = $item['id_good'];
            $item_quantity = $item['quantity'];
            $count = $item_quantity * $quantity;
            mysqli_query($connect, "INSERT INTO `orders_good`( `id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_id,1,$count,0)");
        }
    }

    if($type == 'sale') {
        mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id,$good,4,$quantity,0)");

        $sale = true;
        $kit_list = mysqli_query($connect, "SELECT * FROM `sales_list` WHERE `id_sale` = $good");
        while($item = mysqli_fetch_assoc($kit_list)) {
            $good_id = $item['id_good'];
            $item_quantity = $item['quantity'];
            $count = $item_quantity * $quantity;
            mysqli_query($connect, "INSERT INTO `orders_good`( `id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_id,1,$count,0)");
        }
    }

    if($type == 'other') {
        mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id,$good,3,$quantity,$present)");
        $good_id = $good;
        $count = $quantity;
        mysqli_query($connect, "INSERT INTO `orders_good`( `id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_id,2,$count,0)");
    }
}
mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,1,'$date', '$time')");

$order = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`id_warehouse`, `orders`.`id_client`, `orders`.`id_client_address`, `orders`.`id_order_status`, `orders`.`track`, `orders`.`number`, `orders`.`comment`, `orders`.`date`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id` = $order_id");
$order = mysqli_fetch_assoc($order);

$id = $order['id'];
$track = $order['track'];
$comment = $order['comment'];
$date = $order['date'];
$client_id = $order['id_client'];
$track = $order['track'];
$delivery = $order['delivery'];
$status = $order['id_order_status'];

$length_goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = $id");
$length_goods = mysqli_num_rows($length_goods);

$client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id`= $client_id");
$client = mysqli_fetch_assoc($client);

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

if(!empty($email)) {
    send_info_mail($connect, $id);
}

$quantity = mysqli_query($connect, "SELECT SUM(quantity) AS quantity FROM `orders_good` WHERE id_order = $id");
$quantity = mysqli_fetch_assoc($quantity);

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

$new_order = [
    "id" => $id,
    "track" => $track,
    "comment" => $comment,
    "sale" => $sale,
    "date" => $date,
    "client" => $client['full_name'],
    "delivery" => $delivery,
    "show_delivery" => $show_delivery,
    "status" => $status,
    "orders" => $length_orders,
    "quantity" => $quantity['quantity'],
    "goods" => $length_goods
];

$req = [
    "messages" => ['Заказ успешно создан'],
    "order" => $new_order,
];
http_response_code(200);
echo json_encode($req);