<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['id', 'type'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../../libraries/PHPMailer/src/Exception.php';
require __DIR__ . '/../../libraries/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../../libraries/PHPMailer/src/SMTP.php';

$id = $_POST['id'];
$type = $_POST['type'];
$test = $_POST["test"];

$mailing = mysqli_query($connect, "SELECT * FROM `mailings` WHERE id = '$id'");
$mailing = mysqli_fetch_assoc($mailing);

$text = $mailing["text"];
$title = $mailing["title"];

if ($type === 'telegram') {
    $info_keyboard = [
        [
            [
                "text" => "⬅️ Назад в главное меню",
                "callback_data" => "start",
            ],
        ],
    ];
    $message = "<b>$title</b>\n\n$text";
    if ($test == 'true') {
        $query = [
            "chat_id" => 5694172207,
            "text" => $message,
            "parse_mode" => "html",
            "reply_markup" => json_encode([
                "inline_keyboard" => $info_keyboard
            ]),
            "disable_web_page_preview" => 1
        ];
        $token = "7941326159:AAGQnE2IqhWiVGJCDW-pSSt4DRuXkXqoGm4";
        $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    } else {
        $list = mysqli_query($connect, "SELECT * FROM `telegram_clients`");
        while ($item = mysqli_fetch_assoc($list)) {
            $query = [
                "chat_id" => $item["id_chat"],
                "text" => $message,
                "parse_mode" => "html",
                "reply_markup" => json_encode([
                    "inline_keyboard" => $info_keyboard
                ]),
                "disable_web_page_preview" => 1
            ];
            $token = "7941326159:AAGQnE2IqhWiVGJCDW-pSSt4DRuXkXqoGm4";
            $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_exec($ch);
            curl_close($ch);
        }
    }
} elseif ($type === 'email') {
    if ($test == 'true') {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            // Настройки SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.mail.ru';
            $mail->SMTPAuth = true;
            $mail->Username = 'noreply@ural-mhmr.shop'; // Ваш email
            $mail->Password = 'bsNH2vjTwPEyMRbzHcN5'; // Ваш пароль
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            // Отправитель и получатель
            $mail->setFrom('noreply@ural-mhmr.shop', 'ural-mhmr.shop');
            $mail->addAddress('nulva1@yandex.ru');
            $mail->addAddress('aleksmerz@mail.ru');

            $mail->isHTML(true);
            $mail->Subject = $title;
            $mail->Body = nl2br($text);

            // Отправка письма
            $mail->send();
        } catch (Exception $e) {
            file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
        }
    } else {
        $clients = mysqli_query($connect, "SELECT DISTINCT `email` FROM `clients` WHERE `email` != ''");
        while ($client = mysqli_fetch_array($clients)) {
            $mail = new PHPMailer(true);
            try {
                $mail->CharSet = 'UTF-8';
                // Настройки SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.mail.ru';
                $mail->SMTPAuth = true;
                $mail->Username = 'noreply@ural-mhmr.shop'; // Ваш email
                $mail->Password = 'bsNH2vjTwPEyMRbzHcN5'; // Ваш пароль
                $mail->SMTPSecure = "tls";
                $mail->Port = 587;

                // Отправитель и получатель
                $mail->setFrom('noreply@ural-mhmr.shop', 'ural-mhmr.shop');

                $mail->addAddress($client['email']);

                $mail->isHTML(true);
                $mail->Subject = $title;
                $mail->Body = nl2br($text);

                // Отправка письма
                $mail->send();
            } catch (Exception $e) {
                file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
            }
        }
    }
}

$req = [
    'messages' => ['Рассылка успешно отправлена']
];
http_response_code(200);
echo json_encode($req);