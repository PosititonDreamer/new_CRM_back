<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];

$check = mysqli_query($connect, "SELECT * FROM `salaries_assembler` WHERE `id_worker` = $id");
$check_2 = mysqli_query($connect, "SELECT * FROM `salaries_penalty` WHERE `id_worker` = $id");

if(
    mysqli_num_rows($check) > 0 ||
    mysqli_num_rows($check_2) > 0
){
    mysqli_query($connect, "UPDATE `workers` SET `hidden`=1 WHERE `id` = '$id'");
    $req = [
        'messages' => ['Работник успешно скрыт, так как он привязан к собранным заказам']
    ];
    http_response_code(200);
    echo json_encode($req);
} else {
    mysqli_query($connect, "DELETE FROM `workers` WHERE `id` = '$id'");
    mysqli_query($connect, "DELETE FROM `workers_warehouse` WHERE `id_worker` = '$id'");
    $req = [
        'messages' => ['Работник успешно удален']
    ];
    http_response_code(200);
    echo json_encode($req);
}
