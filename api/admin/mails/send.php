<?php
require_once __DIR__ . '/../../connect.php';
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../../libraries/PHPMailer/src/Exception.php';
require __DIR__ . '/../../libraries/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../libraries/PHPMailer/src/SMTP.php';

$id = $_POST['id'];
$theme = "Тестовое письмо";

$mail = mysqli_query($connect, "SELECT * FROM `mails` WHERE `id` = $id");
$mail = mysqli_fetch_assoc($mail);

$message = nl2br($mail["text"]);

$mail = new PHPMailer(true);
try {
    $mail->CharSet = 'UTF-8';
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host = 'mail.hosting.reg.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'info@ural-muhomor.ru'; // Ваш email
    $mail->Password = 'fC3bX0mI6eeV9iG9'; // Ваш пароль
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    // Отправитель и получатель
    $mail->setFrom('info@ural-muhomor.ru', 'ural-mhmr.shop');
    $mail->addAddress("archive@ural-muhomor.ru", "Тестовое письмо");

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = $theme;
    $mail->Body = $message;

    // Отправка письма
    $mail->send();
} catch (Exception $e) {
    file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
    http_response_code(404);
    $req = [
        'messages' => ['Тестовое письмо не отправлено']
    ];
    echo json_encode($req);
    die();
}

http_response_code(200);
$req = [
    'messages' => ['Тестовое письмо отправлено']
];
echo json_encode($req);