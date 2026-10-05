<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';

// Письма для новичка

function newbie_order_create ($connect, $mail_id) {
    $theme = "newbie_order_create";
    $message = "Это письмо для созданого заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_send ($connect, $mail_id) {
    $theme = "newbie_order_send";
    $message = "Это письмо для отправленного заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_delivered ($connect, $mail_id) {
    $theme = "newbie_order_delivered";
    $message = "Это письмо для заказа, который пришел в пункт выдапчи у клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_keep ($connect, $mail_id) {
    $theme = "newbie_order_keep";
    $message = "Это письмо что истекает срок хранения заказа у клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_received ($connect, $mail_id) {
    $theme = "newbie_order_received";
    $message = "Это письмо после получения заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_after_3_days_received ($connect, $mail_id) {
    $theme = "newbie_order_after_3_days_received";
    $message = "Это письмо 3 дня спустя после получения заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_after_7_days_received ($connect, $mail_id) {
    $theme = "newbie_order_after_7_days_received";
    $message = "Это письмо 7 дней спустя после получения заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_after_21_days_received ($connect, $mail_id) {
    $theme = "newbie_order_after_21_days_received";
    $message = "Это письмо 21 день спустя после получения заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function newbie_order_after_30_days_received ($connect, $mail_id) {
    $theme = "newbie_order_after_30_days_received";
    $message = "Это письмо 30 дней спустя после получения заказа клиента-новичка";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}

// Письма для постоянника
function constant_order_create ($connect, $mail_id) {
    $theme = "constant_order_create";
    $message = "Это письмо для созданого заказа клиента-постоянника";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function constant_order_send ($connect, $mail_id) {
    $theme = "constant_order_send";
    $message = "Это письмо для отправленного заказа клиента-постоянника";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function constant_order_delivered ($connect, $mail_id) {
    $theme = "constant_order_delivered";
    $message = "Это письмо для заказа, который пришел в пункт выдапчи у клиента-постоянника";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function constant_order_keep ($connect, $mail_id) {
    $theme = "constant_order_keep";
    $message = "Это письмо что истекает срок хранения заказа у клиента-постоянника";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function constant_order_received ($connect, $mail_id) {
    $theme = "constant_order_received";
    $message = "Это письмо после получения заказа клиента-постоянника";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}

// Письма для наложки
function no_payed_order_create ($connect, $mail_id) {
    $theme = "no_payed_order_create";
    $message = "Это письмо для созданого заказа клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_send ($connect, $mail_id) {
    $theme = "no_payed_order_send";
    $message = "Это письмо для отправленного заказа клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_delivered ($connect, $mail_id) {
    $theme = "no_payed_order_delivered";
    $message = "Это письмо для заказа, который пришел в пункт выдапчи у клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_after_1_days_delivered ($connect, $mail_id) {
    $theme = "no_payed_order_after_1_days_delivered";
    $message = "Это письмо для заказа, который пришел в пункт выдапчи 1 день спустя у клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_keep ($connect, $mail_id) {
    $theme = "no_payed_order_keep";
    $message = "Это письмо что истекает срок хранения заказа у клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_received ($connect, $mail_id) {
    $theme = "no_payed_order_received";
    $message = "Это письмо после получения заказа клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}
function no_payed_order_back ($connect, $mail_id) {
    $theme = "no_payed_order_back";
    $message = "Это письмо что посылка отправилась обратно клиента с наложкой";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}


function send_mail($connect, $order_id, $type_mail) {
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_assoc($order);
    $full_name = $order['full_name'];
    $client_id = $order['id_client'];
    $mail_id = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id_order` = $order_id AND `id_client` = $client_id AND `type` = '$type_mail'");
    $mail = mysqli_fetch_assoc($mail_id)['id'];
    $info = $type_mail($connect, $mail_id);
    $theme = $info['theme'];
    $message = $info['message'];
    try {
        $mail = new PHPMailer(true);
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
        $mail->addAddress("archive@ural-muhomor.ru", "$full_name");

        // Тема и тело письма
        $mail->isHTML(true);
        $mail->Subject = $theme;
        $mail->Body = $message;

        // Отправка письма
        $mail->send();
    } catch (Exception $e) {
        file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
        send_info_telegram($connect, $order_id, 'Не получилось отправить письмо на email у следующего заказа: ');
    }
}

function start_mails ($connect, $order_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $order_id");
    $order = mysqli_fetch_assoc($order);
    $client_id = $order['id_client'];
    $orders = mysqli_query($connect, "SELECT `orders`.`id` FROM `orders` WHERE `id_client` = $client_id");
    $orders = mysqli_num_rows($orders);
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_assoc($client);
    $email = $client['email'];

    if(empty($order['number'])) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_after_1_days_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_back','$email')");
        $type = 'no_payed_order_create';
    } elseif ($orders > 1) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received','$email')");
        $type = 'constant_order_create';
    } else {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_3_days_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_7_days_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_21_days_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_30_days_received','$email')");
        $type = 'newbie_order_create';
    }

    send_mail($connect, $order_id, $type);
}