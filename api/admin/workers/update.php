<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id', 'rule', 'name', 'description', 'salary', 'warehouses'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$rule = $_POST['rule'];
$name = $_POST['name'];
$description = $_POST['description'];
$salary = $_POST['salary'];
$warehouses = json_decode($_POST['warehouses'], true);

$check = mysqli_query($connect, "SELECT * FROM `workers` WHERE `name` = '$name'");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        mysqli_query($connect, "UPDATE `workers` SET `id_worker_rule`='$rule',`name`='$name',`description`='$description',`salary`=$salary WHERE `id` = '$id'");
        mysqli_query($connect,"DELETE FROM `workers_warehouse` WHERE `id_worker` = '$id'");

        $list_warehouses = [];
        foreach ($warehouses as $warehouse) {
            mysqli_query($connect, "INSERT INTO `workers_warehouse`(`id_worker`, `id_warehouse`) VALUES ($id,$warehouse)");
            $warehouse_id = mysqli_insert_id($connect);
            $list_warehouses[] = [
                "id" => $warehouse_id,
                "worker" => $id,
                "warehouse" => $warehouse
            ];
        }

        $req = [
            "messages" => ['Работник успешно изменен'],
            "warehouses" => $list_warehouses,
            "worker" => [
                "id" => $id,
                "rule" => $rule,
                "name" => $name,
                "description" => $description,
                "salary" => $salary,
                "token" => $check['token'],
            ]
        ];
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Работник с таким именем уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    $check = mysqli_query($connect, "SELECT `token` FROM `workers` WHERE `id` = '$id'");
    $check = mysqli_fetch_assoc($check);
    mysqli_query($connect, "UPDATE `workers` SET `id_worker_rule`='$rule',`name`='$name',`description`='$description',`salary`=$salary WHERE `id` = '$id'");
    mysqli_query($connect,"DELETE FROM `workers_warehouse` WHERE `id_worker` = '$id'");
    $list_warehouses = [];
    foreach ($warehouses as $warehouse) {
        mysqli_query($connect, "INSERT INTO `workers_warehouse`(`id_worker`, `id_warehouse`) VALUES ($id,$warehouse)");
        $warehouse_id = mysqli_insert_id($connect);
        $list_warehouses[] = [
            "id" => $warehouse_id,
            "worker" => $id,
            "warehouse" => $warehouse
        ];
    }

    $req = [
        "messages" => ['Работник успешно изменен'],
        "warehouses" => $list_warehouses,
        "worker" => [
            "id" => $id,
            "rule" => $rule,
            "name" => $name,
            "description" => $description,
            "salary" => $salary,
            "token" => $check['token'],
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}
