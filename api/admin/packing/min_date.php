<?php
require_once __DIR__ . "/../../connect.php";

$expense = mysqli_query($connect, "SELECT * FROM `expenses` ORDER BY `expenses`.`date` ASC LIMIT 1");
$expense = mysqli_fetch_assoc($expense);

$req = [
    "messages" => ['Минимальная дата успешно получена'],
    "min_date" => $expense['date'],
];
http_response_code(200);
echo json_encode($req);