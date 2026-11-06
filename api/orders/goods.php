<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";

$messages = check_data(['warehouse'], $_GET);

require_once __DIR__ . "/../helpers/check_messages.php";

$id = $_GET['warehouse'];
$date = date("Y-m-d");

$products = mysqli_query($connect, "SELECT `products`.`id`,`products`.`hidden`, `products`.`title`, `products`.`show_title`, `products`.`sort`, `measure_units`.`title` AS `measure` FROM `products` JOIN `measure_units` ON `products`.`id_measure_unit` = `measure_units`.id ORDER BY `products`.`sort` ASC;");
$packings = mysqli_query($connect, "SELECT * FROM `products_packing` ORDER BY `products_packing`.`packing` ASC");
$kits = mysqli_query($connect, "SELECT * FROM `goods_kit`");
$boxes = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id_good_other_type` = 2 AND `id_warehouse` = $id AND `balance` > 0 ORDER BY `goods_other`.`sort` ASC");
$sales = mysqli_query($connect, "SELECT * FROM `sales` WHERE `date` >= '$date' AND `date_start` <= '$date'");
$old_sales = mysqli_query($connect, "SELECT * FROM `sales` WHERE `date` < '$date'");
$presents = mysqli_query($connect, "SELECT `presents`.`id`, `presents`.`id_product_packing`,`products_packing`.`packing`, `products`.`title`, `products`.`show_title`, `measure_units`.`title` AS `measure` FROM `presents` JOIN `products_packing` ON `products_packing`.`id` = `presents`.`id_product_packing` JOIN `products` ON `products`.`id` = `products_packing`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` ORDER BY `products`.`sort` ASC");
$multy_sales = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` >= '$date'");
$multy_sales_closed = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` < '$date'");

$products_list = [];
$goods_list = [];
$kits_list = [];
$boxes_list = [];
$sales_list = [];
$sales_multy_list = [];

$presents_list = [
    [
        "id" => 1,
        "title" => "Фирменный магнит",
        "type" => 'other'
    ],
];

while($present = mysqli_fetch_assoc($presents)) {
    $presents_list[] = [
        "id" => $present['id_product_packing'],
        "title" => ($present['show_title'] ?? $present['title']) . ", " . $present['packing'] . " " . $present['measure'],
        "type" => 'good'
    ];
}

while ($product = mysqli_fetch_assoc($products)) {
    $products_list[] = [
        "id" => $product['id'],
        "measure" => $product['measure'],
        "title" => $product['title'],
        "show_title" => $product['show_title'],
        "sort" => $product['sort'],
        "hidden" => $product['hidden'],
    ];
}

while ($packing = mysqli_fetch_assoc($packings)) {
    $goods_list[] = [
        "id" => $packing['id'],
        "product" => $packing['id_product'],
        "packing" => $packing['packing'],
    ];
}

while ($kit = mysqli_fetch_assoc($kits)) {
    $kits_list[] = [
        "id" => $kit['id'],
        "title" => $kit['title'],
        "hidden" => $kit['hidden'],
    ];
}

while ($box = mysqli_fetch_assoc($boxes)) {
    $boxes_list[] = [
        "id" => $box['id'],
        "title" => $box['title'],
        "balance" => $box['balance'],
        "few" => $box['balance'] <= $box['few'],
        "few_very" => $box['balance'] <= $box['few_very'],
        "hidden" => $box['hidden'],
    ];
}

while ($sale = mysqli_fetch_assoc($sales)) {
    $sales_list[] = [
        "id" => $sale['id'],
        "title" => $sale['title'],
        "sum" => $sale['sum'],
        "sum_max" => $sale['sum_max'], 
        "hidden" => $sale['hidden'],
        "active" => true,
    ];
}

while ($sale = mysqli_fetch_assoc($old_sales)) {
    $sales_list[] = [
        "id" => $sale['id'],
        "title" => $sale['title'],
        "sum" => $sale['sum'],
        "sum_max" => $sale['sum_max'],
        "hidden" => $sale['hidden'],
        "active" => false,
    ];
}

while ($multy_item = mysqli_fetch_assoc($multy_sales)) {
    $sales_multy_list[] = [
        "id" => $multy_item['id'],
        "sale_multy" => $multy_item['id_sale_multy'],
        "good" => $multy_item['id_good'],
        "code" => $multy_item['code'],
        "used" => $multy_item['used'] == 1,
        "quantity" => $multy_item['quantity'],
        "denied" => false,
        "date_start" => $multy_item['date_start'],
        "date_end" => $multy_item['date_end'],
    ];
}

while ($multy_item = mysqli_fetch_assoc($multy_sales_closed)) {
    $sales_multy_list[] = [
        "id" => $multy_item['id'],
        "sale_multy" => $multy_item['id_sale_multy'],
        "good" => $multy_item['id_good'],
        "code" => $multy_item['code'],
        "used" => $multy_item['used'] == 1,
        "quantity" => $multy_item['quantity'],
        "denied" => true,
        "date_start" => $multy_item['date_start'],
        "date_end" => $multy_item['date_end'],
    ];
}

$req = [
    "messages" => ['Список данных для заказа успешно получен'],
    "products_list" => $products_list,
    "goods_list" => $goods_list,
    "kits_list" => $kits_list,
    "presents_list" => $presents_list,
    "boxes_list" => $boxes_list,
    "sales_multy_list" => $sales_multy_list,

];
http_response_code(200);
echo json_encode($req);
