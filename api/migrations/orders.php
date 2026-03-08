<?php
$connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_crm_system');
$old_connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_default');

mysqli_query($old_connect, "UPDATE `order_products` SET `accounting_id`='5' WHERE `accounting_id` = 11 AND `type` = 'box'");
mysqli_query($old_connect, "UPDATE `order_products` SET `accounting_id`='6' WHERE `accounting_id` = 12 AND `type` = 'box'");
mysqli_query($old_connect, "UPDATE `order_products` SET `accounting_id`='9' WHERE `accounting_id` = 13 AND `type` = 'box'");
mysqli_query($old_connect, "UPDATE `order_products` SET `accounting_id`='2' WHERE `accounting_id` = 14 AND `type` = 'box'");

$orders = mysqli_query($old_connect, "SELECT * FROM orders WHERE `status` != 'finished'");

$new_orders = [];

while ($order = mysqli_fetch_assoc($orders)) {
    $order_id = $order['id'];
    $goods = [];
    $new_statuses = [];
    $status_collect = "";

    $accounting = mysqli_query($old_connect, "SELECT * FROM `order_products` WHERE `order_id` = '$order_id'");
    while ($account = mysqli_fetch_assoc($accounting)) {
        $account_id = $account['accounting_id'];
        $account_quantity = $account['count'];
        $type = $account['type'];

        if ($type == 'accounting') {
            $product = mysqli_query($old_connect, "SELECT * FROM `accounting` WHERE `id` = $account_id");
            if(mysqli_num_rows($product) > 0) {
                $product = mysqli_fetch_assoc($product);
                $product_account_id = $product['id'];
                $product_id = $product['product_id'];
                $product_title = mysqli_query($old_connect, "SELECT * FROM `products` WHERE `id` = $product_id");
                $product_title = mysqli_fetch_assoc($product_title);
                $goods[] = [
                    "type" => 'good',
                    "product" => $product_title['title'],
                    "count" => $product['count'],
                    "quantity" => $account_quantity,
                    "ready" => $account['ready'],
                ];
            }
        } else {
            $individual = mysqli_query($old_connect, "SELECT * FROM `accounting_individual` WHERE `id` = $account_id");
            if(mysqli_num_rows($individual) > 0) {
                $individual = mysqli_fetch_assoc($individual);
                $goods[] = [
                    "type" => "other",
                    "title" => $individual['title'],
                    "quantity" => $account['count'],
                    "ready" => $account['ready'],
                ];
            }
        }
    }

    $statuses = mysqli_query($old_connect, "SELECT * FROM `order_status` WHERE `order_id` = $order_id");
    while ($status = mysqli_fetch_assoc($statuses)) {
        if($status['status'] == 'assembled') {
            $status_collect = $status['date'];
            $new_statuses[] = [
                "date" => $status['date'],
                "status" => 2
            ];
        }

        if($status['status'] == 'created') {
            $new_statuses[] = [
                "date" => $status['date'],
                "status" => 1
            ];
        }

        if($status['status'] == 'sended') {
            $new_statuses[] = [
                "date" => $status['date'],
                "status" => 4
            ];
        }
        if($status['status'] == 'returned') {
            $new_statuses[] = [
                "date" => $status['date'],
                "status" => 5
            ];
        }
        if($status['status'] == 'proccessed') {
            $new_statuses[] = [
                "date" => $status['date'],
                "status" => 3
            ];
        }
    }

    $new_orders[] = [
        "track" => $order['track'],
        "goods" => $goods,
        "statuses" => $new_statuses,
        "status_collect" => $status_collect,
        "client" => $order['name'],
        "address" => $order['address'],
        "delivery" => $order['delivery'],
        "status" => $order['status'],
        "date" => $order['date_created'],
        "number" => $order['number'],
        "email" => $order['email'],
        "comment" => $order['comment'],
    ];
}



$warehouse = 1;

foreach ($new_orders as $order) {
    $client = trim($order['client']);
    $client = strtolower(trim($client));
    $client = mb_convert_case($client, MB_CASE_TITLE, "UTF-8");
    $address = trim($order['address']);
    $delivery = $order['delivery'];
    $date = $order['date'];
    $track = $order['track'];
    $number = $order['number'];
    $comment = $order['comment'];
    $date_collect = $order['status_collect'];

    $client_id = mysqli_query($connect, "SELECT * FROM `clients` WHERE `full_name` = '$client'");
    $client_id = mysqli_fetch_assoc($client_id)['id'];
    $address_id = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `address` = '$address' AND `id_client`=$client_id AND `delivery`='$delivery'");
    $address_id = mysqli_fetch_assoc($address_id)['id'];

    $status = 1;

    if($order['status'] == 'created') {
        $status = 1;
    } elseif($order['status'] == 'sended') {
        $status = 4;
    } elseif ($order['status'] == 'returned') {
        $status = 5;
    } elseif ($order['status'] == 'assembled') {
        $status = 2;
    } elseif ($order['status'] == 'proccessed') {
        $status = 3;
    }

    mysqli_query($connect, "INSERT INTO `orders`(`id_warehouse`, `id_client`, `id_client_address`, `id_order_status`, `track`, `number`, `comment`, `date`) VALUES ($warehouse,$client_id,$address_id,$status,'$track','$number','$comment','$date')");
    $order_id = mysqli_insert_id($connect);

    foreach ($order['goods'] as $good) {
        $type = $good['type'];
        $quantity = $good['quantity'];
        $ready = $good['ready'];
        if($type == 'good') {
            $product = $good['product'];
            $pack = $good['count'];

            $product_id = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` = '$product'");
            $product_id = mysqli_fetch_assoc($product_id)['id'];
            $packing = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id_product` = '$product_id' AND `packing` = $pack");
            if(mysqli_num_rows($packing) > 0) {
                $packing = mysqli_fetch_assoc($packing);
                $packing_id = $packing['id'];
                mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id, $packing_id ,1,$quantity,0)");
            } else {
                mysqli_query($connect, "INSERT INTO `products_packing`(`id_product`, `packing`) VALUES ($product_id,$pack)");
                $packing_id = mysqli_insert_id($connect);
                mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id, $packing_id ,1,$quantity,0)");
            }

            $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id_product` = $product_id AND `quantity` = $pack AND `id_warehouse` = $warehouse");
            $good_item = mysqli_fetch_assoc($good_item);
            $good_item_id = $good_item['id'];
            mysqli_query($connect, "INSERT INTO `orders_good`(`id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$good_item_id,1,$quantity,$ready)");
            if($ready == 1) {
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($order_id,$good_item_id,1,1,$quantity,'$date')");
                $consumables = mysqli_query($connect, "SELECT * FROM `goods_consumable_binding` WHERE `id_good` = $good_item_id");
                while ($consumable = mysqli_fetch_assoc($consumables)) {
                    $consumable_id = $consumable['id_good_consumable'];
                    mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($order_id,$consumable_id,2,1,$quantity,'$date')");
                }
            }

        } else {
            $title = $good['title'];
            $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `title` = '$title' AND `id_warehouse` = $warehouse");
            $other = mysqli_fetch_assoc($other);
            $other_id = $other['id'];
            $other_type = $other['id_good_other_type'];
            $present = $other_type == 1 ? 1 : 0;
            if($other_type == 1) {
                mysqli_query($connect, "INSERT INTO `orders_composition`(`id_order`, `id_good`, `id_order_composition_type`, `quantity`, `present`) VALUES ($order_id, $other_id, 3,$quantity,$present)");
            }
            mysqli_query($connect, "INSERT INTO `orders_good`(`id_order`, `id_good`, `id_order_good_type`, `quantity`, `ready`) VALUES ($order_id,$other_id,2,$quantity,$ready)");
            if($ready == 1) {
                mysqli_query($connect, "INSERT INTO `expenses`(`id_order_or_supply`, `id_good`, `id_expense_good_type`, `id_expense_type`, `quantity`, `date`) VALUES ($order_id,$other_id,3,1,$quantity,'$date')");
            }
        }
    }

    foreach ($order['statuses'] as $status) {
        $status_date = $status['date'];
        $status_id = $status['status'];
        mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`) VALUES ($order_id,$status_id,'$status_date')");
    }
}