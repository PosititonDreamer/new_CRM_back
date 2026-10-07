<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';

// Письма для новичка

function newbie_order_create ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

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
    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-новичка'
    ];
}
function newbie_order_send ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];
    $theme = "Ваш заказ отправлен!";

    $message = "Здравствуйте!<br />";
    $message .= "Ваша посылка передана в доставку.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-новичка'
    ];
}
function newbie_order_delivered ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Посылка уже в пункте выдачи!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Ваша посылка успешно добралась до пункта выдачи и ожидает вас.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-новичка'
    ];
}
function newbie_order_keep ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Срочно заберите посылку!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Заканчивается срок хранения вашей посылки, постарайтесь поскорее забрать ее.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-новичка'
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
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

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
    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-постоянник'
    ];
}
function constant_order_send ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];
    $theme = "Ваш заказ отправлен!";

    $message = "Здравствуйте!<br />";
    $message .= "Ваша посылка передана в доставку.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-постоянник'
    ];
}
function constant_order_delivered ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Посылка уже в пункте выдачи!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Ваша посылка успешно добралась до пункта выдачи и ожидает вас.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-постоянник'
    ];
}
function constant_order_keep ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Срочно заберите посылку!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Заканчивается срок хранения вашей посылки, постарайтесь поскорее забрать ее.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиента-постоянник'
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
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

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
    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиент с наложкой'
    ];
}
function no_payed_order_send ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];
    $theme = "Ваш заказ отправлен!";

    $message = "Здравствуйте!<br />";
    $message .= "Ваша посылка передана в доставку.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиент с наложкой'
    ];
}
function no_payed_order_delivered ($connect, $mail_id) {
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Посылка уже в пункте выдачи!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Ваша посылка успешно добралась до пункта выдачи и ожидает вас.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиент с наложкой'
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
    $order = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $order = mysqli_fetch_assoc($order);
    $order_id = $order['id'];

    $theme = "Срочно заберите посылку!";

    $message = "Приветствует команда <a href='https://ural-mhmr.shop'>ural-mhmr.shop!</a><br />";
    $message .= "Заканчивается срок хранения вашей посылки, постарайтесь поскорее забрать ее.<br /><br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $full_name = $order['full_name'];
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

    return [
        'theme' => $theme,
        'message' => $message,
        "add_message_for_archive" => 'Новый вид песем, клиент с наложкой'
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
    $mail_info = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id_order` = $order_id AND `id_client` = $client_id AND `type` = '$type_mail'");
    if(mysqli_num_rows($mail_info) > 0) {
        $mail_info = mysqli_fetch_assoc($mail_info);
        $mail_id = $mail_info['id'];
        $mail_function = $mail_info['name_function'];
        $order_id = $mail_info['id_order'];
        $email = $mail_info['email'];
        $info = $mail_function($connect, $mail_id);
        $theme = $info['theme'];
        $message = $info['message'];
        $message_archive = $info['add_message_for_archive'];
        if($type_mail == 'order_delivered') {
            mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1 WHERE `id` = $order_id");
        }
        if($type_mail == 'order_keep') {
            mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
        }
        try {
            if($type_mail == 'order_create' || $type_mail == 'order_send' || $type_mail == 'order_delivered' || $type_mail == 'order_keep') {
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
                $mail->addAddress("$email", "$full_name");

                // Тема и тело письма
                $mail->isHTML(true);
                $mail->Subject = $theme;
                $mail->Body = $message;

                // Отправка письма
                $mail->send();
            }

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
            $mail->Body = $message . "\n$message_archive";

            // Отправка письма
            $mail->send();
        } catch (Exception $e) {
            file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true), FILE_APPEND);
            send_info_telegram($connect, $order_id, 'Не получилось отправить письмо на email у следующего заказа: ');
        }

        if($type_mail == 'order_received') {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id AND `type` = 'order_back'");
        }

        if($type_mail == 'order_back') {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id AND `type` = 'order_received'");
        }

        mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id` = $mail_id");
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
    $type = 'order_create';

    if(empty($order['number'])) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_create', 'order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_send', 'order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_delivered', 'order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_after_1_days_delivered', 'order_after_1_days_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_keep', 'order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_received', 'order_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_back', 'order_back','$email')");
    } elseif ($orders > 1) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_create', 'order_create' ,'$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_send', 'order_send' ,'$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_delivered', 'order_delivered' ,'$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_keep', 'order_keep' ,'$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received', 'order_received' ,'$email')");
    } else {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_create', 'order_create', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_send', 'order_send', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_delivered', 'order_delivered', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_keep', 'order_keep', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_received', 'order_received', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_3_days_received', 'order_after_3_days_received', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_7_days_received', 'order_after_7_days_received', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_21_days_received', 'order_after_21_days_received', '$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'newbie_order_after_30_days_received', 'order_after_30_days_received', '$email')");
    }

    send_mail($connect, $order_id, $type);
}