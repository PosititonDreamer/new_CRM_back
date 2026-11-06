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

$sales_multy = mysqli_query($connect, "SELECT * FROM `sales_multy` ORDER BY `sales_multy`.`sort` ASC");
$new_list_multy = [];
while ($item = mysqli_fetch_assoc($sales_multy)) {
    $id = $item['id'];
    $multy_goods = mysqli_query($connect, "SELECT * FROM `sales_multy_goods` WHERE `id_sale_multy` = $id");
    $new_multy_goods = [];

    while ($item_multy = mysqli_fetch_assoc($multy_goods)) {
        $new_multy_goods[] = [
            "id" => $item_multy['id'],
            "good" => $item_multy['id_good'],
            "quantity" => $item_multy['quantity'],
        ];
    }

    $multy_list = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` >= '$date'");
    $multy_list_closed = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id_sale_multy` = $id AND `date_end` < '$date'");

    $new_multy_list = [];

    while ($multy_item = mysqli_fetch_assoc($multy_list)) {
        $new_multy_list[] = [
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

    while ($multy_item = mysqli_fetch_assoc($multy_list_closed)) {
        $new_multy_list[] = [
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

    $new_list_multy[] = [
        "id" => $id,
        "title" => $item['title'],
        "sum" => $item['sum'],
        "days" => $item['days'],
        "goods" => $new_multy_goods,
        "list" => $new_multy_list,
    ];
}

$req = [
    "messages" => ['Получен список наборов'],
    "sales_list" => $new_list,
    "sales_multy" => $new_list_multy,
];
http_response_code(200);
echo json_encode($req);