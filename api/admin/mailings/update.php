<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id', 'title', 'text'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$id = $_POST['id'];
$title = $_POST['title'];
$text = $_POST['text'];

mysqli_query($connect, "UPDATE `mailings` SET `title`='$title',`text`='$text' WHERE `id` = $id");

$req = [
    "messages" => ['Рассылка успешно изменена'],
    "mailing" => [
        "id" => $id,
        "text" => nl2br($text),
        "start_text" => $text,
        "title" => $title,
    ]
];
http_response_code(200);
echo json_encode($req);