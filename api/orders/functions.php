<?php
require_once __DIR__ . "/../connect.php";

function forming_instructions($connect, $order_id) {
    $goods = mysqli_query($connect, "SELECT * FROM `orders_good` WHERE `id_order` = '$order_id' AND `id_order_good_type` = 1");

    if(mysqli_num_rows($goods) > 0) {
        $products = [];
        while($good = mysqli_fetch_assoc($goods)) {
            $good_id = $good['id_good'];
            $good_item = mysqli_query($connect, "SELECT `id_product` FROM `goods` WHERE `id` = $good_id");
            if(mysqli_num_rows($good_item) > 0) {
                $products[] = mysqli_fetch_assoc($good_item)['id_product'];
            }
        }
        $products = array_unique($products);
        if(count($products) > 0) {
            $products = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` IN (" . join(', ', $products) . ") ORDER BY `products`.`sort` ASC");
            $final_instruction = '';

            while($product = mysqli_fetch_assoc($products)) {
                if(!empty($product['instruction'])) {
                    $title = $product['client_title'];
                    $instruction = $product['instruction'];
                    $final_instruction .= "<b>$title</b><br>$instruction<br><br>";
                }
            }
            if(!empty(trim($final_instruction))) {
                return $final_instruction;
            }

            return null;
        }
        return null;
    }
    return null;
}

function send_info_telegram($connect, $order_id, $text = null)
{
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $compositions = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $order_id ORDER BY `orders_composition`.`present` ASC");
    $i = 0;
    $track = $order['track'];
    $message = "Заказ $track \n\n";
    while ($composition = mysqli_fetch_array($compositions)) {
        $i++;
        $good_id = $composition['id_good'];
        $quantity = $composition['quantity'];
        $present = $composition['present'];
        $type = $composition['id_order_composition_type'];
        $present_text = $present == 1 ? ' Подарок:' : "";
        $quantity_text = $quantity > 1 ? " * $quantity" : "";
        if ($type == 1) {
            $packing = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id` = $good_id");
            $packing = mysqli_fetch_array($packing);
            $product_id = $packing['id_product'];
            $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
            $product = mysqli_fetch_array($product);
            $measure_id = $product['id_measure_unit'];
            $measure = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `id` = $measure_id");
            $measure = mysqli_fetch_array($measure);
            $product_title = $product['client_title'];
            $product_packing = $packing['packing'];
            $product_measure = $measure['title'];
            $message .= "$i.$present_text $product_title, $product_packing $product_measure $quantity_text\n";
        }
        if ($type == 2) {
            $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `id` = $good_id");
            $kit = mysqli_fetch_array($kit);
            $kit_title = $kit['title'];
            $message .= "$i.$present_text $kit_title $quantity_text\n";
        }
        if ($type == 3) {
            $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
            $other = mysqli_fetch_array($other);
            if($other['id_good_other_type'] == 2) {
                $i--;
                continue;
            }
            $other_title = $other['title'];
            $message .= "$i.$present_text $other_title $quantity_text\n";
        }
        if ($type == 4) {
            $present_text = " Акция: ";
            $sale = mysqli_query($connect, "SELECT * FROM `sales` WHERE `id` = $good_id");
            $sale = mysqli_fetch_array($sale);
            $sale_title = $sale['title'];
            $message .= "$i.$present_text $sale_title $quantity_text\n";
        }
    }
    $message .= "\n";

    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];

    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $delivery = $address['delivery'];
    $full_name = $order['full_name'];
    $phone = $client['phone'];
    $email = $client['email'];
    $address_text = $address['address'];

    $message .= "$delivery\n";
    $message .= "ФИО: $full_name\n";
    $message .= "Телефон: $phone\n";
    $message .= "Email: $email\n";
    $message .= "Адрес доставки: $address_text\n";
    $message .= "\n";

    $comment = $order['comment'];
    $messenger = $client['messenger'];

    if (!empty($comment)) {
        $message .= "\nКомментарий: $comment\n";
    }

    if (!empty($messenger)) {
        $message .= "\nМессенджер: $messenger\n";
    }
    $query = [
        "chat_id" => 5694172207,
        "text" => $message,
    ];

    $token = "7812122192:AAG9wt_CkT6G_VFZySLz2XjMWXNzrOlj900";

    if($text) {
        $query['text'] = $text . "\n" . $query['text'];
        $token = -4933799485;
    }

    $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_exec($ch);
    curl_close($ch);
}

function send_error_telegram($text)
{
    $query = [
        "chat_id" => -4933799485,
        "text" => $text,
        "parse_mode" => "html",
    ];
    $token = "7812122192:AAG9wt_CkT6G_VFZySLz2XjMWXNzrOlj900";
    $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_exec($ch);
    curl_close($ch);
}

