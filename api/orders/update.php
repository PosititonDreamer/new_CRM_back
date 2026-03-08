<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['id', 'warehouse', 'client', 'phone', 'address', 'delivery', 'track', 'composition'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$order_id = $_POST['id'];
$warehouse = $_POST['warehouse'];
$client = trim($_POST['client']);
$address = $_POST['address'];
$track = str_replace(" ", "", $_POST['track']);
$comment = trim($_POST['comment']);
$phone = $_POST['phone'];
$delivery = $_POST['delivery'];
$email = $_POST['email'];
$date = date("Y-m-d");
$time = date("H:i:s");
$composition = json_decode($_POST['composition'], true);

$client_item = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name`='$client'");
if(mysqli_num_rows($client_item) > 0) {
    $client_item = mysqli_fetch_assoc($client_item);
    $client_id = $client_item['id'];
    $client = $client_id;

    if(!empty($phone) && empty($client_item['phone'])) {
        mysqli_query($connect, "UPDATE `clients` SET `phone`='$phone' WHERE `id` = $client_id");
    }

    if(!empty($email) && empty($client_item['email'])) {
        mysqli_query($connect, "UPDATE `clients` SET `email`='$email' WHERE `id` = $client_id");
    }

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
}

$order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id`=$order_id");
$order = mysqli_fetch_assoc($order);
$change_status = false;
if($order['id_order_status'] == 2) {
    $change_status = true;
    $comment .= "\n Заказ был изменен после сборки";
    mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,1,'$date', '$time')");

    $list = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `id_order_or_supply` = $order_id AND `id_expense_type` = 1 ORDER BY `expenses`.`id_expense_good_type` ASC");

    while ($item = mysqli_fetch_assoc($list)) {
        $item_id = $item['id'];
        $quantity = $item['quantity'];
        $good_id = $item['id_good'];
        $type = $item['id_expense_good_type'];

        if($type == 1){
            $check_good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
            $check_good = mysqli_fetch_assoc($check_good);
            if($check_good['weight'] == 0) {
                mysqli_query($connect, "UPDATE `goods` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");

            } else {
                $warehouse = $check_good['id_warehouse'];
                $product = $check_good['id_product'];
                $weight_quantity = intval($check_good['quantity']) * $quantity;

                $check_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
                $check_weight = mysqli_fetch_assoc($check_weight);
                $weight_id = $check_weight['id'];

                if($check_weight['composite'] == 0) {
                    mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`+$weight_quantity WHERE `id` = $weight_id");
                } else {
                    $composite_id = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id");
                    $composite_id = mysqli_fetch_assoc($composite_id)['id'];

                    $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $weight_id");

                    while ($composite = mysqli_fetch_assoc($composite_list)) {
                        $composite_weight_id = $composite['id_good_weight'];
                        $composite_quantity = $weight_quantity / 100 * intval($composite['proportion']);
                        mysqli_query($connect, "UPDATE `goods_weight` SET `balance`=`balance`+$composite_quantity WHERE `id` = $composite_weight_id");
                    }
                }
            }

        }

        if($type == 2) {
            mysqli_query($connect, "UPDATE `goods_consumable` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        }

        if($type == 3) {
            $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
            $other = mysqli_fetch_assoc($other);
            mysqli_query($connect, "UPDATE `goods_other` SET `balance`=`balance`+$quantity WHERE `id` = $good_id");
        }
    }
}
if($_POST['payed'] == 'true') {
    $order_number = $order['number'];
    if(empty($order_number)) {
        mysqli_query($connect, "UPDATE `orders` SET `id_client_address`=$address,`id_order_status`=1,`track`='$track',`comment`='$comment', `number` = -1 WHERE `id` = $order_id");
    } else {
        mysqli_query($connect, "UPDATE `orders` SET `id_client_address`=$address,`id_order_status`=1,`track`='$track',`comment`='$comment' WHERE `id` = $order_id");
    }
} else {
    mysqli_query($connect, "UPDATE `orders` SET `id_client_address`=$address,`id_order_status`=1,`track`='$track',`comment`='$comment', `number` = NULL WHERE `id` = $order_id");
}

mysqli_query($connect, "DELETE FROM `orders_composition` WHERE `id_order` = $order_id");
mysqli_query($connect, "DELETE FROM `orders_good` WHERE `id_order` = $order_id");
mysqli_query($connect, "DELETE FROM `expenses` WHERE `id_order_or_supply` = $order_id AND `id_expense_type` = 1");
mysqli_query($connect, "DELETE FROM `salaries_assembler` WHERE `id_order`=$order_id");

$sale = false;
foreach ($composition as $comp) {
    $good = $comp['good'];
    $quantity = $comp['quantity'];
    $present = $comp['present'];
    $type = $comp['type'];

    if($type == 'good') {
        mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id,$good,1,$quantity,$present)");

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
    "messages" => ['Заказ успешно изменен'],
    "order" => $new_order,
    "change_status" => $change_status,
];
http_response_code(200);
echo json_encode($req);