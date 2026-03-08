<?php
require_once __DIR__ . "/../../connect.php";

$date = date("Y-m-d");

$list = mysqli_query($connect, "SELECT * FROM `sales` WHERE `hidden` = 0 AND `date` >= '$date' ORDER BY `sales`.`date` ASC");

$list_old = mysqli_query($connect, "SELECT * FROM `sales` WHERE `hidden` = 0 AND `date` < '$date' ORDER BY `sales`.`id` ASC");

$new_list = [];

while ($item = mysqli_fetch_assoc($list)) {
    $id = $item['id'];

    $new_item = [
        "id" => $id,
        "title" => $item['title'],
        "keywords" => $item["keywords"],
        "sum" => $item["sum"],
        "sum_max" => $item["sum_max"],
        "date" => $item["date"],
        "date_start" => $item["date_start"],
        "active" => true,
        "list" => []
    ];

    $sale_list = mysqli_query($connect, "SELECT * FROM `sales_list` WHERE `id_sale` = $id");

    while ($sale_item = mysqli_fetch_assoc($sale_list)) {
        $new_item["list"][] = [
            "id" => $sale_item["id"],
            "good" => $sale_item["id_good"],
            "quantity" => $sale_item["quantity"],
        ];
    }
    $new_list[] = $new_item;
}

while ($item = mysqli_fetch_assoc($list_old)) {
    $id = $item['id'];

    $new_item = [
        "id" => $id,
        "title" => $item['title'],
        "keywords" => $item["keywords"],
        "sum" => $item["sum"],
        "sum_max" => $item["sum_max"],
        "date" => $item["date"],
        "date_start" => $item["date_start"],
        "active" => false,
        "list" => []
    ];

    $sale_list = mysqli_query($connect, "SELECT * FROM `sales_list` WHERE `id_sale` = $id");

    while ($sale_item = mysqli_fetch_assoc($sale_list)) {
        $new_item["list"][] = [
            "id" => $sale_item["id"],
            "good" => $sale_item["id_good"],
            "quantity" => $sale_item["quantity"],
        ];
    }
    $new_list[] = $new_item;
}

$req = [
    "messages" => ['Получен список наборов'],
    "sales_list" => $new_list
];
http_response_code(200);
echo json_encode($req);