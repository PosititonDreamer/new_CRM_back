<?php
require_once __DIR__ . "/../../connect.php";

$list = mysqli_query($connect, "SELECT * FROM `presents`");

$presents_list = [];

while ($present = mysqli_fetch_assoc($list)) {
    $presents_list[] = [
        "id" => $present["id"],
        "packing" => $present["id_product_packing"],
    ];
}

$req = [
    'messages' => ["Подарок успешно добавлен"],
    'presents' => $presents_list
];
http_response_code(200);
echo json_encode($req);