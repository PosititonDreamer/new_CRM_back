<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$warehouse = $_GET['warehouse'];

$supplies_warehouse = mysqli_query($connect, "SELECT * FROM `supplies_warehouse` WHERE (`id_warehouse_receive` = $warehouse OR `id_warehouse_give` = $warehouse) AND `hidden` = 0");

$new_supplies_warehouse = [];
$new_supplies = [];
$new_types = [];

while ($supply = mysqli_fetch_assoc($supplies_warehouse)) {
    $id = $supply['id'];
    $id_warehouse_give = $supply['id_warehouse_give'];
    $id_warehouse_receive = $supply['id_warehouse_receive'];
    $new_warehouse = [
        "id" => $id,
        "warehouse_receive" => $supply['id_warehouse_receive'],
        "warehouse_give" => $supply['id_warehouse_give'],
        "list" => []
    ];

    $warehouse_receive = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `id` = $id_warehouse_receive");
    $warehouse_give = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `id` = $id_warehouse_give");
    $warehouse_receive = mysqli_fetch_assoc($warehouse_receive);
    $warehouse_give = mysqli_fetch_assoc($warehouse_give);

    $new_warehouse['warehouse_give_title'] = $warehouse_give['title'];
    $new_warehouse['warehouse_receive_title'] = $warehouse_receive['title'];

    $goods_receive = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods`.`quantity`, `goods`.`balance`, `goods`.`few`,`goods`.`few_very`, `goods`.`weight`, `goods`.`id_product` as `product`, `products`.`sort`,`products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure` FROM `supplies_warehouse_connection` JOIN `goods` ON `goods`.`id` = `supplies_warehouse_connection`.`id_good_receive` JOIN `products` ON `products`.`id` = `goods`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'good' ORDER BY `products`.`sort` ASC");
    $goods_give = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods`.`quantity`, `goods`.`balance`, `goods`.`weight`, `goods`.`id_product` as `product`, `products`.`sort`,`products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure` FROM `supplies_warehouse_connection` JOIN `goods` ON `goods`.`id` = `supplies_warehouse_connection`.`id_good_give` JOIN `products` ON `products`.`id` = `goods`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'good' ORDER BY `products`.`sort` ASC");

    $goods_weight_receive = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods_weight`.`balance`,  `goods_weight`.`few`,`goods_weight`.`few_very`, `goods_weight`.`id_product` as `product`, `products`.`sort`,`products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure` FROM `supplies_warehouse_connection` JOIN `goods_weight` ON `goods_weight`.`id` = `supplies_warehouse_connection`.`id_good_receive` JOIN `products` ON `products`.`id` = `goods_weight`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'weight' ORDER BY `products`.`sort` ASC");
    $goods_weight_give = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods_weight`.`balance`, `goods_weight`.`id_product` as `product`, `products`.`sort`,`products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure` FROM `supplies_warehouse_connection` JOIN `goods_weight` ON `goods_weight`.`id` = `supplies_warehouse_connection`.`id_good_give` JOIN `products` ON `products`.`id` = `goods_weight`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'weight' ORDER BY `products`.`sort` ASC");

    $goods_consumable_receive = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`,`goods_consumable`.`few`,`goods_consumable`.`few_very`, `goods_consumable`.`balance`, `goods_consumable`.`title` FROM `supplies_warehouse_connection` JOIN `goods_consumable` ON `goods_consumable`.`id` = `supplies_warehouse_connection`.`id_good_receive` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'consumable' ORDER BY `goods_consumable`.`sort` ASC");
    $goods_consumable_give = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods_consumable`.`balance`, `goods_consumable`.`title` FROM `supplies_warehouse_connection` JOIN `goods_consumable` ON `goods_consumable`.`id` = `supplies_warehouse_connection`.`id_good_give` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'consumable' ORDER BY `goods_consumable`.`sort` ASC");

    $goods_other_receive = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`,`goods_other`.`few`,`goods_other`.`few_very`, `goods_other`.`balance`, `goods_other`.`title` FROM `supplies_warehouse_connection` JOIN `goods_other` ON `goods_other`.`id` = `supplies_warehouse_connection`.`id_good_receive` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'other' ORDER BY `goods_other`.`sort` ASC");
    $goods_other_give = mysqli_query($connect, "SELECT `supplies_warehouse_connection`.`hidden`,`supplies_warehouse_connection`.`id`, `goods_other`.`balance`, `goods_other`.`title` FROM `supplies_warehouse_connection` JOIN `goods_other` ON `goods_other`.`id` = `supplies_warehouse_connection`.`id_good_give` WHERE `supplies_warehouse_connection`.`id_supply_warehouse` = $id AND `supplies_warehouse_connection`.`good_type` = 'other' ORDER BY `goods_other`.`sort` ASC");
    while ($good = mysqli_fetch_assoc($goods_give)) {
        $new_warehouse['list']['good']['give'][] = [
          "id" => $good['id'],
          "quantity" => $good['quantity'],
          "balance" => $good['balance'],
          "weight" => $good['weight'] == 1,
          "product" => $good['product'],
          "title" => $good['title'],
          "show_title" => $good['show_title'],
          "measure" => $good['measure'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while ($good = mysqli_fetch_assoc($goods_receive)) {
        $new_warehouse['list']['good']['receive'][] = [
            "id" => $good['id'],
            "quantity" => $good['quantity'],
            "balance" => $good['balance'],
            "weight" => $good['weight'] == 1,
            "product" => $good['product'],
            "title" => $good['title'],
            "show_title" => $good['show_title'],
            "measure" => $good['measure'],
            "few" => $good['few'],
            "few_very" => $good['few_very'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_weight_receive)) {
        $new_warehouse['list']['weight']['receive'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "product" => $good['product'],
            "title" => $good['title'],
            "show_title" => $good['show_title'],
            "measure" => $good['measure'],
            "few" => $good['few'],
            "few_very" => $good['few_very'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_weight_give)) {
        $new_warehouse['list']['weight']['give'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "product" => $good['product'],
            "title" => $good['title'],
            "show_title" => $good['show_title'],
            "measure" => $good['measure'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_consumable_receive)) {
        $new_warehouse['list']['consumable']['receive'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "title" => $good['title'],
            "few" => $good['few'],
            "few_very" => $good['few_very'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_consumable_give)) {
        $new_warehouse['list']['consumable']['give'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "title" => $good['title'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_other_receive)) {
        $new_warehouse['list']['other']['receive'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "title" => $good['title'],
            "few" => $good['few'],
            "few_very" => $good['few_very'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    while($good = mysqli_fetch_assoc($goods_other_give)) {
        $new_warehouse['list']['other']['give'][] = [
            "id" => $good['id'],
            "balance" => $good['balance'],
            "title" => $good['title'],
            "hidden" => $good['hidden'] == 1
        ];
    }

    $goods_weight = mysqli_query($connect, "SELECT DISTINCT `goods_weight`.`balance`, `goods`.`id_product` as `product`, `products`.`sort`,`products`.`title`, `products`.`show_title`, `measure_units`.`title` as `measure` FROM `supplies_warehouse_connection` JOIN `goods` ON `goods`.`id` = `supplies_warehouse_connection`.`id_good_give` JOIN `products` ON `products`.`id` = `goods`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` JOIN `goods_weight` ON `goods_weight`.`id_product` = `goods`.`id_product` WHERE `goods`.`weight` = 1 AND `supplies_warehouse_connection`.`id_supply_warehouse` = $id  AND `goods_weight`.`id_warehouse` = $id_warehouse_give AND `supplies_warehouse_connection`.`good_type` = 'good' ORDER BY `products`.`sort` ASC");
    while ($good = mysqli_fetch_assoc($goods_weight)) {
        $new_warehouse['list']['weight_give'][] = [
            "product" => $good['product'],
            "balance" => $good['balance'],
            "title" => $good['title'],
            "show_title" => $good['show_title'],
            "measure" => $good['measure'],
        ];
    }

    $supplies = mysqli_query($connect, "SELECT * FROM `supplies` WHERE `id_supply_warehouse` = $id ORDER BY `supplies`.`id` DESC");

    while ($item = mysqli_fetch_assoc($supplies)) {
        $supply_id = $item['id'];
        $length = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $supply_id");
        $length = mysqli_num_rows($length);
        $new_supplies[] = [
            "id" => $supply_id,
            "supply_warehouse" => $item['id_supply_warehouse'],
            "supply_status" => $item['id_supply_status'],
            "date" => $item['date'],
            "length" => $length,
        ];
    }
    $new_supplies_warehouse[] = $new_warehouse;
}

$types = mysqli_query($connect, "SELECT * FROM `supplies_process_status`");
while ($item = mysqli_fetch_assoc($types)) {
    $new_types[] = [
        "id" => $item['id'],
        "title" => $item['title'],
    ];
}

$req = [
    "messages" => ["Успешно получен список поставок для склада"],
    "warehouses_supply" => $new_supplies_warehouse,
    "supplies" => $new_supplies,
    "types" => $new_types,
];
http_response_code(200);
echo json_encode($req);