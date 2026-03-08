<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['worker'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$worker_id = $_POST['worker'];

$worker = mysqli_query($connect, "SELECT * FROM `workers` WHERE `id` = $worker_id");
$worker = mysqli_fetch_assoc($worker);

$rule = $worker['id_worker_rule'];

if($rule == 2) {
    $messages = check_data(['salaries'], $_POST);
    require_once __DIR__ . "/../../helpers/check_messages.php";

    $salaries = json_decode($_POST['salaries'], true);

    foreach ($salaries as $salary) {
        mysqli_query($connect, "UPDATE `salaries_assembler` SET `ready`= 1 WHERE `id`=$salary");
    }

    if(isset($_POST['penalties'])) {
        $penalties = json_decode($_POST['penalties'], true);

        foreach ($penalties as $penalty) {
            mysqli_query($connect, "UPDATE `salaries_penalty` SET `ready`= 1 WHERE `id`=$penalty");
        }
    }
    $req = [
        "messages" => ['Зарплата сборщику успешно выдана']
    ];
    http_response_code(200);
    echo json_encode($req);
}

if($rule == 3) {
    $messages = check_data(['date_start', 'date_end', 'description', 'sum'], $_POST);
    require_once __DIR__ . "/../../helpers/check_messages.php";

    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $description = $_POST['description'];
    $sum = $_POST['sum'];
    mysqli_query($connect, "INSERT INTO `salaries_operator`(`id_worker`, `date_start`, `date_end`, `description`, `sum`) VALUES ($worker_id,'$date_start','$date_end','$description',$sum)");
    $last_id = mysqli_insert_id($connect);

    if(isset($_POST['penalties'])) {
        $penalties = json_decode($_POST['penalties'], true);

        foreach ($penalties as $penalty) {
            mysqli_query($connect, "UPDATE `salaries_penalty` SET `ready`= 1 WHERE `id`=$penalty");
        }
    }

    $req = [
        "messages" => ['Зарплата оператору успешно выдана'],
        "salary" => [
            'id' => $last_id,
            "date_start" => $date_start,
            "date_end" => $date_end,
            "description" => $description,
            "sum" => $sum,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
    die();
}