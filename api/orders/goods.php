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

$products_list = [];
$goods_list = [];
$kits_list = [];
$boxes_list = [];
$sales_list = [];
$presents_list = [
    [
        "id" => 1,
        "title" => "Фирменный магнит",
        "type" => 'other'
    ],
    [
        "id" => 34,
        "title" => "Полынь капсулы, 100 капсул",
        "type" => 'good'
    ],
    [
        "id" => 32,
        "title" => "Пижма капсулы, 100 капсул",
        "type" => 'good'
    ],
    [
        "id" => 27,
        "title" => "Отборные шляпки красного, 25 грамм",
        "type" => 'good'
    ],
    [
        "id" => 47,
        "title" => "Чага крошка 1-3 мм, 100 грамм",
        "type" => 'good'
    ]
];

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

$req = [
    "messages" => ['Список данных для заказа успешно получен'],
    "products_list" => $products_list,
    "goods_list" => $goods_list,
    "kits_list" => $kits_list,
    "presents_list" => $presents_list,
    "boxes_list" => $boxes_list,
    "sales_list" => $sales_list,
];
http_response_code(200);
echo json_encode($req);
