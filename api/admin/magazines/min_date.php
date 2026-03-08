<?php
require_once __DIR__ . "/../../connect.php";

$min_date = mysqli_query($connect, "SELECT * FROM `magazines` ORDER BY `magazines`.`date` ASC LIMIT 1");
$min_date = mysqli_fetch_assoc($min_date);

$req = [
    "messages" => ['Минимальная дата успешно получена'],
    "min_date" => $min_date['date']
];
http_response_code(200);
echo json_encode($req);