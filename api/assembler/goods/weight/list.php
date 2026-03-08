<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$warehouse = $_GET['warehouse'];

$list = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_warehouse` = $warehouse");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $item_id = $item['id'];
    $new_item = [
        "id" => $item['id'],
        "product" => $item['id_product'],
        "warehouse" => $item['id_warehouse'],
        "balance" => $item['balance'],
        "few" => $item['few'],
        "few_very" => $item['few_very'],
        "composite" => $item['composite'],
    ];

    if($item['composite'] == 1) {
        $item['composite_list'] = [];
        $composite_id = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $item_id");
        $composite_id = mysqli_fetch_assoc($composite_id)['id'];
        $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $composite_id");
        while($composite_item = mysqli_fetch_assoc($composite_list)) {
            $new_item['composite_id'] = $composite_item['id_good_weight_composite'];
            $new_item['composite_list'][] = [
                "id" => $composite_item['id'],
                "composite_id" => $composite_item['id_good_weight_composite'],
                "weight" => $composite_item['id_good_weight'],
                "proportion" => $composite_item['proportion'],
            ];
        }
    }
    $new_list[] = $new_item;
}

$products = mysqli_query($connect, "SELECT * FROM `products` WHERE `hidden` = 0 ORDER BY `products`.`sort` ASC");

$new_products = [];

while ($item = mysqli_fetch_assoc($products)) {
    $new_products[] = [
        "id" => $item["id"],
        "measure_unit" => $item["id_measure_unit"],
        "title" => $item["title"],
        "show_title" => $item["show_title"],
        "sort" => $item["sort"]
    ];
}

$measures = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `hidden` = 0");

$new_measures = [];

while ($item = mysqli_fetch_assoc($measures)) {
    $new_measures[] = [
        "id" => $item["id"],
        "title" => $item["title"],
    ];
}

$req = [
    "messages" => ["Получен список весовых товаров"],
    "goods_weight" => $new_list,
    "products" => $new_products,
    "measure_units" => $new_measures,
];
http_response_code(200);
echo json_encode($req);