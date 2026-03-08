<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['title', 'text'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$title = $_POST['title'];
$text = $_POST['text'];

mysqli_query($connect, "INSERT INTO `mailings`(`title`, `text`) VALUES ('$title','$text')");

$last_id = mysqli_insert_id($connect);

$req = [
    "messages" => ['Рассылка успешно создана'],
    "mailing" => [
        "id" => $last_id,
        "text" => nl2br($text),
        "start_text" => $text,
        "title" => $title,
    ]
];
http_response_code(200);
echo json_encode($req);