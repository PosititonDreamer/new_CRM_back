<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['supply', 'list', 'worker'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$supply = $_POST['supply'];
$worker = $_POST['worker'];
$list = json_decode($_POST['list'], true);

$date = date("Y-m-d");

mysqli_query($connect, "INSERT INTO `supplies`(`id_supply_warehouse`, `id_supply_status`,  `date`) VALUES ($supply,1,  '$date')");
$last_id = mysqli_insert_id($connect);
mysqli_query($connect, "INSERT INTO `supplies_process`(`id_supply`, `id_supply_process_status`, `id_worker`, `date`) VALUES ($last_id,1,$worker,'$date')");

foreach ($list as $item) {
    $warehouse_connection = $item['warehouse_connection'];
    $quantity = $item['quantity'];
    mysqli_query($connect, "INSERT INTO `supplies_list`( `id_supply`, `id_supply_warehouse_connection`, `quantity`, `ready`) VALUES ($last_id,$warehouse_connection,$quantity,0)");
    $item_id = mysqli_insert_id($connect);
}

$length = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $last_id");
$count = 0;
while($product_item = mysqli_fetch_assoc($length)) {
    $count++;
    $supply_connection_id = $product_item['id_supply_warehouse_connection'];
    $supply_connection = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `id` = '$supply_connection_id'");
    $supply_item = mysqli_fetch_assoc($supply_connection);
    $supply_good_id = $supply_item['id_good_receive'];

    if($supply_item['good_type'] == 'good') {
        $product_data = mysqli_query($connect, "SELECT `goods`.`quantity`, `products`.`title`, `products`.`show_title`, `measure_units`.`title` AS `measure` FROM `goods` JOIN `products` ON `products`.`id` = `goods`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `goods`.id = $supply_good_id");
        $product_data = mysqli_fetch_assoc($product_data);
        $products_list[] = ($product_data['show_title'] ?? $product_data['title']) . ", " . $product_data['quantity'] . " " . $product_data['measure'] . " - " . $product_item['quantity'] . " шт.";
    }

    if($supply_item['good_type'] == 'consumable') {
        $product_data = mysqli_query($connect, "SELECT `goods_consumable`.`title` FROM `goods_consumable`  WHERE `goods_consumable`.id = $supply_good_id");
        $product_data = mysqli_fetch_assoc($product_data);
        $products_list[] = $product_data['title'] . " - " . $product_item['quantity'] . " шт.";
    }

    if($supply_item['good_type'] == 'other') {
        $product_data = mysqli_query($connect, "SELECT `goods_other`.`title` FROM `goods_other`  WHERE `goods_other`.id = $supply_good_id");
        $product_data = mysqli_fetch_assoc($product_data);
        $products_list[] = $product_data['title'] . " - " . $product_item['quantity'] . " шт.";
    }
    if($supply_item['good_type'] == 'weight') {
        $product_data = mysqli_query($connect, "SELECT `goods_weight`.`id`, `products`.`title`, `products`.`show_title`, `measure_units`.`title` AS `measure` FROM `goods_weight` JOIN `products` ON `products`.`id` = `goods_weight`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `goods_weight`.id = $supply_good_id");
        $product_data = mysqli_fetch_assoc($product_data);
        $products_list[] = ($product_data['show_title'] ?? $product_data['title']) . " - " . $product_item['quantity'] . " " . $product_data['measure'];
    }
}
$length = $count;

$req = [
    "messages" => ["Поставка успешно создана"],
    "supply" => [
        "id" => $last_id,
        "supply_warehouse" => $supply,
        "supply_status" => 1,
        "date" => $date,
        "length" => $length,
        "products_list" => $products_list,
    ]
];
http_response_code(200);
echo json_encode($req);