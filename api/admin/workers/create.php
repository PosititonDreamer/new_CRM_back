<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
require_once __DIR__ . "/functions.php";

$messages = check_data(['rule', 'name', 'description', 'salary', 'warehouses'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$rule = $_POST['rule'];
$name = $_POST['name'];
$description = $_POST['description'];
$salary = $_POST['salary'];
$token = generate_token();
$warehouses = json_decode($_POST['warehouses'], true);

$check = mysqli_query($connect, "SELECT * FROM `workers` WHERE `name` = '$name'");
if (mysqli_num_rows($check) > 0) {
    $req = [
        "messages" => ['Работник с таким именем уже есть']
    ];
    http_response_code(400);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `workers`(`id_worker_rule`, `name`, `description`, `salary`, `token`, `hidden`) VALUES ($rule,'$name','$description',$salary,'$token',0)");
    $last_id = mysqli_insert_id($connect);
    $list_warehouses = [];
    foreach ($warehouses as $warehouse) {
        mysqli_query($connect, "INSERT INTO `workers_warehouse`(`id_worker`, `id_warehouse`) VALUES ($last_id,$warehouse)");
        $warehouse_id = mysqli_insert_id($connect);
        $list_warehouses[] = [
            "id" => $warehouse_id,
            "worker" => $last_id,
            "warehouse" => $warehouse
        ];
    }

    $req = [
        "messages" => ['Работник успешно добавлен'],
        "warehouses" => $list_warehouses,
        "worker" => [
            "id" => $last_id,
            "rule" => $rule,
            "name" => $name,
            "description" => $description,
            "salary" => $salary,
            "token" => $token,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}