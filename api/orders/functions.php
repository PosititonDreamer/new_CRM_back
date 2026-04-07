<?php
require_once __DIR__ . "/../connect.php";

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
require __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';

function send_info_mail($connect, $order_id)
{
    $theme = "Новый заказ на сайте ural-mhmr.shop";

    $message = "Здравствуйте!<br />";
    $message .= "Вы совершили покупку на нашем сайте, благодарим за доверие.<br />";
    $message .= "Скоро мы передадим посылку в службу доставки и пришлем второе письмо с трек-номером.<br /><br />";

    $message .= "Детали заказа:<br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $compositions = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $order_id ORDER BY `orders_composition`.`present` ASC");
    $i = 0;
    while ($composition = mysqli_fetch_array($compositions)) {
        $i++;
        $good_id = $composition['id_good'];
        $quantity = $composition['quantity'];
        $present = $composition['present'];
        $type = $composition['id_order_composition_type'];
        $present_text = $present == 1 ? ' Подарок:' : "";
        $quantity_text = $quantity > 1 ? " * $quantity" : "";
        if ($type == 1) {
            $packing = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id` = $good_id");
            $packing = mysqli_fetch_array($packing);
            $product_id = $packing['id_product'];
            $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
            $product = mysqli_fetch_array($product);
            $measure_id = $product['id_measure_unit'];
            $measure = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `id` = $measure_id");
            $measure = mysqli_fetch_array($measure);
            $product_title = $product['client_title'];
            $product_packing = $packing['packing'];
            $product_measure = $measure['title'];
            $message .= "$i.$present_text $product_title, $product_packing $product_measure $quantity_text<br/>";
        }
        if ($type == 2) {
            $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `id` = $good_id");
            $kit = mysqli_fetch_array($kit);
            $kit_title = $kit['title'];
            $message .= "$i.$present_text $kit_title $quantity_text<br/>";
        }
        if ($type == 3) {
            $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
            $other = mysqli_fetch_array($other);
            if($other['id_good_other_type'] == 2) {
                $i--;
                continue;
            }
            $other_title = $other['title'];
            $message .= "$i.$present_text $other_title $quantity_text<br/>";
        }
        if ($type == 4) {
            $present_text = " Акция: ";
            $sale = mysqli_query($connect, "SELECT * FROM `sales` WHERE `id` = $good_id");
            $sale = mysqli_fetch_array($sale);
            $sale_title = $sale['title'];
            $message .= "$i.$present_text $sale_title $quantity_text<br/>";
        }
    }
    $message .= "<br />";

    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];

    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $delivery = $address['delivery'];
    $full_name = $client['full_name'];
    $phone = $client['phone'];
    $email = $client['email'];
    $address_text = $address['address'];

    $message .= "$delivery<br />";
    $message .= "ФИО: $full_name<br />";
    $message .= "Телефон: $phone<br />";
    $message .= "Email: $email<br />";
    $message .= "Адрес доставки: $address_text<br />";
    $message .= "<br />";

    $message .= "Дополнительно:<br />";
    $message .= "1. Оставьте отзыв о нас на Яндекс, отправьте скриншот отзыва оператору и получите скидку 300 рублей на следующую покупку:<br />";
    $message .= "<a href='https://clck.ru/3GCDzy'>https://clck.ru/3GCDzy</a><br />";
    $message .= "2. Подпишитесь на наш телеграм-канал:<br />";
    $message .= "<a href='https://t.me/ural_mhmr_shop'>https://t.me/ural_mhmr_shop</a><br />";
    $message .= "3. По всем вопросом (просто для знакомства и получения лучших персональных предложений) пишите нашему оператору:<br />";
    $message .= "<a href='https://t.me/mhmr_shop_operator'>https://t.me/mhmr_shop_operator</a><br /><br />";
    $message .= "Спасибо! Ждём от вас новых покупок!<br />";
    $message .= "C уважением, команда интернет-магазина <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a>";

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
        $mail->addAddress("nulva12344@gmail.com", "$full_name");

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

    $mail = new PHPMailer(true);
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
    $mail->addAddress("nulva1@yandex.ru", "$full_name");

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = $theme;
    $mail->Body = $message;

    // Отправка письма
    $mail->send();
}

function send_track_mail($connect, $order_id)
{
    $theme = "Ваш заказ отправлен!";

    $message = "Здравствуйте!<br />";
    $message .= "Ваша посылка передана в доставку.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $client['full_name'];
    $email = $client['email'];
    $track = $order['track'];
    $delivery = $address['delivery'];

    $message .= "ТРЕК-НОМЕР ПОСЫЛКИ: ";
    $message .= "$track<br />";
    if ($delivery == "CDEK") {
        $message .= "Отследить можно на сайте или в приложении CDEK<br /><br />";
    } elseif ($delivery == "Яндекс Доставка") {
        $message .= "Отследить посылку можно в приложении <a href='https://go.yandex/'>Яндекс.Go</a><br /><br />";
    }
    else {
        $message .= "Отследить можно на сайте или в приложении Почты России<br /><br />";
    }

    $message .= "Будем признательны за ваш отзыв на Яндекс:<br />";
    $message .= "<a href='https://clck.ru/3GCDzy'>https://clck.ru/3GCDzy</a><br />";
    $message .= "<i>*если сделать скриншот отзыва и показать его нашему оператору, вы получите скидку 300 рублей на следующую покупку</i><br /><br />";
    $message .= "Спасибо за доверие!<br />";
    $message .= "С уважением, команда интернет-магазина <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a>";

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
        $mail->addAddress("nulva12344@gmail.com", "$full_name");

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

    $mail = new PHPMailer(true);
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
    $mail->addAddress("nulva1@yandex.ru", "$full_name");

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = $theme;
    $mail->Body = $message;

    // Отправка письма
    $mail->send();
}

function send_delivered_mail($connect, $order_id)
{
    $theme = "Посылка уже в пункте выдачи!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Ваша посылка успешно добралась до пункта выдачи и ожидает вас.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $client['full_name'];
    $email = $client['email'];
    $track = $order['track'];
    $delivery = $address['delivery'];
    $address_text = $address['address'];

    $message .= "ТРЕК-НОМЕР ПОСЫЛКИ: ";
    $message .= "$track<br /><br />";
    $message .= "$delivery<br />";
    $message .= "ФИО: $full_name<br />";
    $message .= "Адрес доставки: $address_text<br />";
    $message .= "<br />";

    $message .= "*БОНУС: Получите скидку 300 рублей за отзыв о нашей продукции. Оставьте свой отзыв на <a href='https://clck.ru/3GCDzy'>Яндекс</a>, сделайте скриншот отзыва и отправьте его оператору. Взамен получите разовый промокод на скидку для следующей покупки!<br /><br />";
    $message .= "Наш сайт: <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a><br />";
    $message .= "Наш новый телеграм-канал: <a href='https://t.me/ural_mhmr_shop'>https://t.me/ural_mhmr_shop</a><br />";
    $message .= "По всем вопросам пишите нашему оператору: <a href='https://t.me/mhmr_shop_operator'>https://t.me/mhmr_shop_operator</a><br />";
    $message .= "С уважением, команда интернет-магазина <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a>";

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
        $mail->addAddress("nulva12344@gmail.com", "$full_name");

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

    $mail = new PHPMailer(true);
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
    $mail->addAddress("nulva1@yandex.ru", "$full_name");

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = $theme;
    $mail->Body = $message;

    // Отправка письма
    $mail->send();

    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1 WHERE `id` = $order_id");
}

function send_keeped_mail($connect, $order_id)
{
    $theme = "Срочно заберите посылку!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Заканчивается срок хранения вашей посылки, постарайтесь поскорее забрать ее.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $client['full_name'];
    $email = $client['email'];
    $track = $order['track'];
    $delivery = $address['delivery'];
    $address_text = $address['address'];

    $message .= "ТРЕК-НОМЕР ПОСЫЛКИ: ";
    $message .= "$track<br /><br />";
    $message .= "$delivery<br />";
    $message .= "ФИО: $full_name<br />";
    $message .= "Адрес доставки: $address_text<br />";
    $message .= "<br />";

    $message .= "*БОНУС: Получите скидку 300 рублей за отзыв о нашей продукции. Оставьте свой отзыв на <a href='https://clck.ru/3GCDzy'>Яндекс</a>, сделайте скриншот отзыва и отправьте его оператору. Взамен получите разовый промокод на скидку для следующей покупки!<br /><br />";
    $message .= "Наш сайт: <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a><br />";
    $message .= "Наш новый телеграм-канал: <a href='https://t.me/ural_mhmr_shop'>https://t.me/ural_mhmr_shop</a><br />";
    $message .= "По всем вопросам пишите нашему оператору: <a href='https://t.me/mhmr_shop_operator'>https://t.me/mhmr_shop_operator</a><br />";
    $message .= "С уважением, команда интернет-магазина <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a>";

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
        $mail->addAddress("nulva12344@gmail.com", "$full_name");

        // Тема и тело письма
        $mail->isHTML(true);
        $mail->Subject = $theme;
        $mail->Body = $message;

        // Отправка письма
        $mail->send();
    } catch (Exception $e) {
        file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
        send_info_telegram($connect, $order_id, 'Не получилось отправить письмо на email у следующего заказа:');
    }

    $mail = new PHPMailer(true);
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
    $mail->addAddress("nulva1@yandex.ru", "$full_name");

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = $theme;
    $mail->Body = $message;

    // Отправка письма
    $mail->send();

    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
}

function send_info_telegram($connect, $order_id, $text = null)
{
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $compositions = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $order_id ORDER BY `orders_composition`.`present` ASC");
    $i = 0;
    $track = $order['track'];
    $message = "Заказ $track \n\n";
    while ($composition = mysqli_fetch_array($compositions)) {
        $i++;
        $good_id = $composition['id_good'];
        $quantity = $composition['quantity'];
        $present = $composition['present'];
        $type = $composition['id_order_composition_type'];
        $present_text = $present == 1 ? ' Подарок:' : "";
        $quantity_text = $quantity > 1 ? " * $quantity" : "";
        if ($type == 1) {
            $packing = mysqli_query($connect, "SELECT * FROM `products_packing` WHERE `id` = $good_id");
            $packing = mysqli_fetch_array($packing);
            $product_id = $packing['id_product'];
            $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
            $product = mysqli_fetch_array($product);
            $measure_id = $product['id_measure_unit'];
            $measure = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `id` = $measure_id");
            $measure = mysqli_fetch_array($measure);
            $product_title = $product['client_title'];
            $product_packing = $packing['packing'];
            $product_measure = $measure['title'];
            $message .= "$i.$present_text $product_title, $product_packing $product_measure $quantity_text\n";
        }
        if ($type == 2) {
            $kit = mysqli_query($connect, "SELECT * FROM `goods_kit` WHERE `id` = $good_id");
            $kit = mysqli_fetch_array($kit);
            $kit_title = $kit['title'];
            $message .= "$i.$present_text $kit_title $quantity_text\n";
        }
        if ($type == 3) {
            $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
            $other = mysqli_fetch_array($other);
            if($other['id_good_other_type'] == 2) {
                $i--;
                continue;
            }
            $other_title = $other['title'];
            $message .= "$i.$present_text $other_title $quantity_text\n";
        }
        if ($type == 4) {
            $present_text = " Акция: ";
            $sale = mysqli_query($connect, "SELECT * FROM `sales` WHERE `id` = $good_id");
            $sale = mysqli_fetch_array($sale);
            $sale_title = $sale['title'];
            $message .= "$i.$present_text $sale_title $quantity_text\n";
        }
    }
    $message .= "\n";

    $client_id = $order['id_client'];
    $address_id = $order['id_client_address'];

    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_array($client);
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $delivery = $address['delivery'];
    $full_name = $client['full_name'];
    $phone = $client['phone'];
    $email = $client['email'];
    $address_text = $address['address'];

    $message .= "$delivery\n";
    $message .= "ФИО: $full_name\n";
    $message .= "Телефон: $phone\n";
    $message .= "Email: $email\n";
    $message .= "Адрес доставки: $address_text\n";
    $message .= "\n";

    $comment = $order['comment'];
    $messenger = $client['messenger'];

    if (!empty($comment)) {
        $message .= "\nКомментарий: $comment\n";
    }

    if (!empty($messenger)) {
        $message .= "\nМессенджер: $messenger\n";
    }
    $query = [
        "chat_id" => 5694172207,
        "text" => $message,
    ];

    $token = "7812122192:AAG9wt_CkT6G_VFZySLz2XjMWXNzrOlj900";

    if($text) {
        $query['text'] = $text . "\n" . $query['text'];
        $token = -4933799485;
    }

    $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_exec($ch);
    curl_close($ch);
}

function send_error_telegram($text)
{
    $query = [
        "chat_id" => -4933799485,
        "text" => $text,
        "parse_mode" => "html",
    ];
    $token = "7812122192:AAG9wt_CkT6G_VFZySLz2XjMWXNzrOlj900";
    $ch = curl_init("https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($query));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_exec($ch);
    curl_close($ch);
}

