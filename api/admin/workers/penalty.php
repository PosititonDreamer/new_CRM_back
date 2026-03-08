<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['worker', 'description', 'sum', 'date'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$worker = $_POST["worker"];
$description = $_POST["description"];
$sum = $_POST["sum"];
$date = $_POST["date"];
mysqli_query($connect, "INSERT INTO `salaries_penalty`(`id_worker`, `description`, `sum`, `date`, `ready`) VALUES ($worker,'$description',$sum, '$date',0)");

$req = [
    "messages" => ['Штраф успешно добавлен']
];
http_response_code(200);
echo json_encode($req);