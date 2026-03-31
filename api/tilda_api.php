<?php
require_once __DIR__ . "/connect.php";
require_once __DIR__ . "/orders/functions.php";
http_response_code(200);

if(isset($_GET['check_old'])) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_unprocessed` LIMIT 1");
    if(mysqli_num_rows($order) > 0) {
        $order = mysqli_fetch_assoc($order);
        $order_id = $order['id'];
        $id_unprocessed = $order_id;
        $_POST = json_decode($order['data'], true);
    } else {
        die();
    }
} else {
    file_put_contents("error.txt", print_r($_POST, true), FILE_APPEND);

}

$warehouse = 1;
$email = $_POST['Email'];
$phone = mb_eregi_replace('[^0-9]', '', $_POST['Phone']);;
$messenger = '';
$comment = '';

if (isset($_POST['messenger'])) {
    $messenger = $_POST['messenger'];
}

if (isset($_POST['Комментарий_для_ural-mhmr_shop'])) {
    $comment .= $_POST['Комментарий_для_ural-mhmr_shop'] . "\n";
}

$info = $_POST['payment'];
$full_name = mb_strtolower(trim($info['delivery_fio']));
$full_name = mb_convert_case($full_name, MB_CASE_TITLE, "UTF-8");
$delivery = $info['delivery'];

if (str_contains($delivery, "CDEK")) {
    $delivery = "CDEK";
} elseif(str_contains($delivery, "Почта России")) {
    $delivery = "Почта России";
} elseif(str_contains($delivery, "Яндекс.Доставка")) {
    $delivery = "Яндекс Доставка";
} elseif (str_contains($delivery, "Boxberry")) {
    $delivery = "Boxberry";
}

$amount = $info['amount'];
$address = $info['delivery_address'];
$comment .= $info['delivery_comment'];
$comment = trim($comment);
$amount = floatval($info['amount']);

if (strpos($phone, "7") === false || strpos($phone, "7") != 0) {
    $phone = "7$phone";
}

$products = [];

foreach ($info['products'] as $product) {
    $name = $product['name'];
    $quantity = $product['quantity'];
    if (str_contains(mb_strtolower($name), 'набор')) {
        if(str_contains($name, ':')) {
            $number = explode(" ", $name)[1];
            $number = preg_replace('/[^0-9]/', '', $number);
            $products[] = [
                "check_type" => 'number',
                "number" => $number,
                "quantity" => $quantity,
                "type" => "kit"
            ];
        } else {
            $products[] = [
                "check_type" => 'name',
                "name" => $name,
                "quantity" => $quantity,
                "type" => "kit"
            ];
        }

        continue;
    }

     if($name == 'Весы ювелирные') {
         $products[] = [
             "title" => $name,
             "quantity" => $quantity,
             "quantity_good" => 1,
             "type" => 'good',
             "present" => 0
         ];
         continue;
     }

    if (!isset($product['options'])) {
        $products[] = [
            "title" => $name,
            "quantity" => $quantity,
            "quantity_good" => null,
            "type" => 'good',
            "present" => 0
        ];
        continue;
    }

    if(isset($product['options'][1])) {
        $name .= " - " . mb_strtolower($product['options'][1]['variant']);
    }

    $quantity_good = intval(explode(" ", $product['options'][0]['variant'])[0]);
    $products[] = [
        "title" => $name,
        "quantity" => $quantity,
        "quantity_good" => $quantity_good,
        "type" => 'good',
        "present" => 0
    ];
}

if ($amount >= 8000 && isset($_POST['present-header-8000']) && !filter_var($_POST['present-header-8000'], FILTER_VALIDATE_URL)) {
    $products[] = [
        "title" => $_POST['present-header-8000'],
        "quantity" => 1,
        "quantity_good" => null,
        "type" => 'good',
        "present" => 1
    ];
}

if ($amount >= 15000 && isset($_POST['present-header-15000']) && !filter_var($_POST['present-header-15000'], FILTER_VALIDATE_URL)) {
    $products[] = [
        "title" => "Фирменный магнит",
        "quantity" => 1,
        "quantity_good" => null,
        "type" => 'other',
        "present" => 1
    ];
}

if ($amount >= 20000 && isset($_POST['present-header-20000']) && !filter_var($_POST['present-header-20000'], FILTER_VALIDATE_URL)) {
    $products[] = [
        "title" => $_POST['present-header-20000'],
        "quantity" => 1,
        "quantity_good" => null,
        "type" => 'good',
        "present" => 1
    ];
}

$goods = [];
$next = true;
$message = '';

foreach ($products as $product) {
    if ($product['type'] == 'good') {
        $title = $product['title'];
        $quantity = $product['quantity'];
        $product_item = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` LIKE '%$title%'");
        if (mysqli_num_rows($product_item) > 0) {
            $packing = $product['quantity_good'];
            if(!$packing) {
                $message .= "В заказе у товара нет опций у $title\n";
                $next = false;
                continue;
            }
            $product_item = mysqli_fetch_assoc($product_item);
            $product_id = $product_item['id'];
            $packing_item = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `packing` = $packing AND `id_product` = $product_id");
            if (mysqli_num_rows($packing_item) > 0) {
                $packing_item = mysqli_fetch_assoc($packing_item);
                $packing_id = $packing_item['id'];
                $goods[] = [
                    'good' => $packing_id,
                    'quantity' => $quantity,
                    'present' => $product['present'],
                    'type' => 'good'
                ];
            } else {
                mysqli_query($connect, "INSERT INTO `products_packing`(`id_product`, `packing`) VALUES ($product_id,$packing)");
                $packing_id = mysqli_insert_id($connect);
                $goods[] = [
                    'good' => $packing_id,
                    'quantity' => $quantity,
                    'present' => $product['present'],
                    'type' => 'good'
                ];
            }
        } else {
            $product_item = mysqli_query($connect, "SELECT * FROM `products_other` WHERE `title` = '$title'");
            if (mysqli_num_rows($product_item) > 0) {
                $product_item = mysqli_fetch_assoc($product_item);
                $packing_id = $product_item['id_packing'];
                if(!empty($packing_id)) {
                    $goods[] = [
                        'good' => $packing_id,
                        'quantity' => $quantity,
                        'present' => $product['present'],
                        'type' => 'good'
                    ];
                } else {
                    $message .= "Кривой товар не привязан к продукту: $title \n";
                    $next = false;
                }

            } else {
                mysqli_query($connect, "INSERT INTO `products_other`(`id_packing`, `title`) VALUES (NULL,'$title')");
                $message .= "Кривой товар не найден: $title \n";
                $next = false;
            }
        }
    }
    if ($product['type'] == 'kit') {
        $check_type = $product['check_type'];
        $quantity = $product['quantity'];
        if($check_type == 'number') {
            $number = $product['number'];
            $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `number` = $number");
            if (mysqli_num_rows($kit) > 0) {
                $kit = mysqli_fetch_assoc($kit);
                $goods[] = [
                    'good' => $kit['id'],
                    'quantity' => $quantity,
                    'present' => 0,
                    'type' => 'kit'
                ];

            } else {
                $message .= "Набор $number не найден \n";
                $next = false;
            }
        } else {
            $name = $product['name'];
            $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `title` LIKE '%$name%'");
            if (mysqli_num_rows($kit) > 0) {
                $kit = mysqli_fetch_assoc($kit);
                $goods[] = [
                    'good' => $kit['id'],
                    'quantity' => $quantity,
                    'present' => 0,
                    'type' => 'kit'
                ];
            } else {
                $message .= "Набор $name не найден \n";
                $next = false;
            }
        }
    }
    if($product['type'] == 'other') {
        $title = $product['title'];
        $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `title` = '$title' AND `id_warehouse`=$warehouse");
        if (mysqli_num_rows($other) > 0) {
            $other = mysqli_fetch_assoc($other);
            $goods[] = [
                'good' => $other['id'],
                'quantity' => 1,
                'present' => 1,
                'type' => 'other'
            ];
        } else {
            $message .= "Магнит не найден в базе \n";
            $next = false;
        }
    }
}

if(str_contains($delivery, 'nodelivery')) {
    $message .= "Пришел заказ без доставки\n";
    $next = false;
}
if(!$next) {
    if(isset($id_unprocessed)) {
        $message = "Нет возможности перебить заказ:\n\n" . $message;
        send_error_telegram($message);
    } else {
        $data = json_encode($_POST, JSON_UNESCAPED_UNICODE);
        mysqli_query($connect, "INSERT INTO `orders_unprocessed`(`data`) VALUES ('$data')");
        send_error_telegram($message);
    }
}

$client = $full_name;
$track = "";
$number = $info['orderid'];
$date = date("Y-m-d");
$time = date("H:i:s");

$check = mysqli_query($connect, "SELECT * FROM `orders` WHERE `number` = '$number'");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($connect, "DELETE FROM `orders_unprocessed` WHERE `id` = $id_unprocessed");
    if(isset($_GET['check_old'])) {
        header("Location: /api/tilda_api.php?check_old");
    }
    die();
}

if(!$next) {
    die();
}

$phone = "+$phone";
$client_item = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name`='$client'");
if(mysqli_num_rows($client_item) > 0) {
    $client_item = mysqli_fetch_assoc($client_item);
    $client_id = $client_item['id'];
    $client = $client_id;

    mysqli_query($connect, "UPDATE `clients` SET `phone`='$phone', `email`='$email' WHERE `id` = $client_id");

    if(!empty($messenger) && empty($client_item['messenger'])) {
        mysqli_query($connect, "UPDATE `clients` SET `messenger`='$messenger' WHERE `id` = $client_id");
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
} else {
    mysqli_query($connect, "INSERT INTO `clients`(`full_name`, `phone`, `email`, `messenger`) VALUES ('$client','$phone','$email', '$messenger')");
    $client_id = mysqli_insert_id($connect);
    mysqli_query($connect, "INSERT INTO `clients_address`(`id_client`, `address`, `delivery`) VALUES ($client_id,'$address','$delivery')");
    $address_id = mysqli_insert_id($connect);
    $client = $client_id;
    $address = $address_id;
}

mysqli_query($connect, "INSERT INTO `orders`(`id_warehouse`, `id_client`, `id_client_address`, `id_order_status`, `track`, `number`, `comment`, `date`) VALUES ($warehouse,$client,$address,3,'$track','$number','$comment','$date')");
$order_id = mysqli_insert_id($connect);


$sales = mysqli_query($connect, "SELECT * FROM `sales` WHERE '$comment' LIKE CONCAT('%', `keywords`, '%') AND `date` >= '$date' AND `date_start` <= '$date' AND ((`sum` <= $amount AND `sum_max` >= $amount) OR (`sum` <= $amount AND `sum_max` IS NULL)) AND `hidden` = 0");

while($sale = mysqli_fetch_assoc($sales)) {
    $goods[] = [
        'good' => $sale['id'],
        'quantity' => 1,
        'present' => 0,
        'type' => 'sale'
    ];
}

foreach ($goods as $comp) {
    $good = $comp['good'];
    $quantity = $comp['quantity'];
    $present = $comp['present'];
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
mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,3,'$date', '$time')");

send_info_mail($connect, $order_id);

if($delivery == 'CDEK') {
    require_once __DIR__ . "/orders/find_track.php";
} else {
    send_error_telegram("Новый заказ в $delivery");
}

if(isset($_GET['check_old'])) {
    mysqli_query($connect, "DELETE FROM `orders_unprocessed` WHERE `id` = $id_unprocessed");
    header("Location: /api/tilda_api.php?check_old");
}