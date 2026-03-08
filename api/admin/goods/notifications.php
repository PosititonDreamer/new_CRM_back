<?php
require_once __DIR__ . "/../../connect.php";

if(isset($_GET["check"])){
    $goods = mysqli_query($connect, "SELECT * FROM `goods` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `weight` = 0 AND `hidden` = 0 LIMIT 1");
    $goods_consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `hidden` = 0 LIMIT 1");
    $goods_other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `hidden` = 0 LIMIT 1");
    $goods_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `composite` = 0 LIMIT 1");
    $req = [
        "messages" => ['Проверка малого количества'],
        "check" => mysqli_num_rows($goods) > 0 || mysqli_num_rows($goods_consumable) > 0 || mysqli_num_rows($goods_other) > 0 || mysqli_num_rows($goods_weight) > 0,
    ];
    http_response_code(200);
    echo json_encode($req);

} else {
    $warehouses = mysqli_query($connect, "SELECT * FROM `warehouses` WHERE `hidden` = 0");
    $products = mysqli_query($connect, "SELECT * FROM `products` WHERE `hidden` = 0 ORDER BY `products`.`sort` ASC");
    $measure_units = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `hidden` = 0");

    $new_list = [];
    $new_list_products = [];
    $new_list_measure_units = [];

    while($warehouse = mysqli_fetch_assoc($warehouses)){
        $new_item = [
            "id" => $warehouse['id'],
            "title" => $warehouse['title'],
            "goods" => [],
            "consumable" => [],
            "other" => [],
            "weight" => [],
        ];
        $id = $warehouse['id'];
        $goods = mysqli_query($connect, "SELECT * FROM `goods` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `weight` = 0 AND `hidden` = 0 AND `id_warehouse` = $id");
        $goods_consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `hidden` = 0 AND `id_warehouse` = $id ORDER BY `goods_consumable`.`sort` ASC");
        $goods_other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `hidden` = 0 AND `id_warehouse` = $id ORDER BY `goods_other`.`sort` ASC");
        $goods_weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE (`balance` <= `few` OR `balance` <= `few_very`) AND `composite` = 0 AND `id_warehouse` = $id");

        while($good = mysqli_fetch_assoc($goods)){
            $new_item["goods"][] = [
                "id" => $good['id'],
                "product" => $good['id_product'],
                "quantity" => $good['quantity'],
                "balance" => $good['balance'],
                "article" => $good['article'],
                "few" => $good['few'],
                "few_very" => $good['few_very'],
            ];
        }

        while($good_consumable = mysqli_fetch_assoc($goods_consumable)){
            $new_item["consumable"][] = [
                "id" => $good_consumable['id'],
                "title" => $good_consumable['title'],
                "balance" => $good_consumable['balance'],
                "few" => $good_consumable['few'],
                "few_very" => $good_consumable['few_very'],
            ];
        }

        while($good_other = mysqli_fetch_assoc($goods_other)){
            $new_item["other"][] = [
                "id" => $good_other['id'],
                "title" => $good_other['title'],
                "balance" => $good_other['balance'],
                "few" => $good_other['few'],
                "few_very" => $good_other['few_very'],
            ];
        }

        while($good_weight = mysqli_fetch_assoc($goods_weight)){
            $new_item["weight"][] = [
                "id" => $good_weight['id'],
                "product" => $good_weight['id_product'],
                "balance" => $good_weight['balance'],
                "few" => $good_weight['few'],
                "few_very" => $good_weight['few_very'],
            ];
        }
        $new_list[] = $new_item;
    }

    while($product = mysqli_fetch_assoc($products)){
        $new_list_products[] = [
            "id" => $product['id'],
            "title" => $product['title'],
            "show_title" => $product['show_title'],
            "measure_unit" => $product['id_measure_unit'],
        ];
    }

    while($measure_unit = mysqli_fetch_assoc($measure_units)){
        $new_list_measure_units[] = [
            "id" => $measure_unit['id'],
            "title" => $measure_unit['title'],
        ];
    }

    $new_warehouses = [];

    foreach($new_list as $item){
        if(count($item['goods']) > 0 || count($item['consumable']) > 0 || count($item['weight']) > 0 || count($item['other']) > 0){
            $new_warehouses[] = $item;
        }
    }

    $req = [
        "messages" => ["Получен список товаров с малым количеством"],
        "products" => $new_list_products,
        "measure_units" => $new_list_measure_units,
        "warehouses" => $new_warehouses,
    ];
    http_response_code(200);
    echo json_encode($req);
}