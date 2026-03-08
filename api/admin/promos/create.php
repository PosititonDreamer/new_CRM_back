<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";
$messages = check_data(['title', 'date_start', 'date_end'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$title = $_POST['title'];
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];

$check = mysqli_query($connect, "SELECT * FROM promos WHERE title = '$title' AND date_start = '$date_start' AND date_end = '$date_end'");

if(mysqli_num_rows($check) > 0){
    $req = [
        "messages" => ['Такой промокод уже есть'],
    ];
    http_response_code(404);
    echo json_encode($req);
} else {
    mysqli_query($connect, "INSERT INTO `promos`(`title`, `date_start`, `date_end`) VALUES ('$title','$date_start','$date_end')");
    $last_id = mysqli_insert_id($connect);

    $req = [
        "messages" => ['Промокод успешно создан'],
        "promo" => [
            "id" => $last_id,
            "title" => $title,
            "date_start" => $date_start,
            "date_end" => $date_end,
        ]
    ];
    http_response_code(200);
    echo json_encode($req);
}

