<?php
require_once __DIR__ . '/functions.php';

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';

function info_messenger()
{
    return "<a href='https://t.me/mhmr_shop_operator'>Телеграм</a><br/><a href='https://vk.ru/away.php?to=https%3A%2F%2Fmax.ru%2Fu%2Ff9LHodD0cOLIxkhiuRVLsJVgmuIsMvu3-IyLMaGVUKkgUmMZ7-HB66vpXpw&utf=1'>Макс</a><br/><a href='https://m.vk.ru/uralmhmrshop'>ВК</a>";

}

function info_chanels() {
    return "<a href='https://t.me/ural_mhmr_shop'>Телеграм</a><br/><a href='https://clck.ru/3SnrSD'>Макс</a><br/>";
}
// Письма для новичка

function generate_code($connect, $type) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';

    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }

    $check_code = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `code`='$code'");
    if (mysqli_num_rows($check_code) > 0) {
        return generate_code($connect, $type);
    }

    $multy_sale = mysqli_query($connect, "SELECT * FROM `sales_multy` WHERE `type` = '$type'");

    $codes = [];

    while ($multy_sale_item = mysqli_fetch_assoc($multy_sale)) {
        $id_multy_sale = $multy_sale_item['id'];
        $days = $multy_sale_item['days'];
        $sum = $multy_sale_item['sum'];
        $date_start = date('Y-m-d');
        $date = new DateTime("$date_start");
        $interval = new DateInterval("P".$days."D");
        $date_end = $date->add($interval)->format('Y-m-d');
        $random_good = mysqli_query($connect, "SELECT * FROM `sales_multy_goods` WHERE `id_sale_multy` = $id_multy_sale ORDER BY RAND() LIMIT 1");
        $random_good = mysqli_fetch_assoc($random_good);
        $good_id = $random_good['id_good'];
        $quantity = $random_good['quantity'];
        mysqli_query($connect, "INSERT INTO `sales_multy_list`(`id_sale_multy`, `id_good`, `quantity`, `code`, `used`, `date_start`, `date_end`, `sum`) VALUES ($id_multy_sale,$good_id,$quantity,'$code',0,'$date_start','$date_end','$sum')");
        $last_id = mysqli_insert_id($connect);

        $codes[] = [
            "id" => $last_id,
            "good" => $good_id,
            "quantity" => $quantity,
            "date_start" => $date_start,
            "date_end" => $date_end,
            "days" => $days,
            "sum" => $sum
        ];
    }



    return [
        "code" => $code,
        "all_codes" => $codes
    ];
}

function order_composition($connect, $order_id) {
    $compositions = mysqli_query($connect, "SELECT * FROM `orders_composition` WHERE `id_order` = $order_id ORDER BY `orders_composition`.`present` ASC");
    $i = 0;
    $message = "";
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

        if($type == 5) {
            $multy_sale = mysqli_query($connect, "SELECT * FROM `sales_multy_list` WHERE `id` = $good_id");
            $multy_sale = mysqli_fetch_array($multy_sale);
            $code = $multy_sale['code'];
            $id_good = $multy_sale['id_good'];
            $good_item = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $id_good");
            $good_item = mysqli_fetch_array($good_item);
            $product_id = $good_item['id_product'];
            $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
            $product = mysqli_fetch_array($product);
            $measure_id = $product['id_measure_unit'];
            $measure = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `id` = $measure_id");
            $measure = mysqli_fetch_array($measure);
            $product_measure = $measure['title'];
            $product_title = $product['client_title'];
            $count = $good_item['quantity'];
            $message .= "$i.Индивидуальная акция $code($product_title, $count $product_measure $quantity_text)<br/>";
        }
    }
    return $message;
}

function order_create($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Первый заказ в URAL-MUHOMOR";
    $message = "Здравствуйте!<br />";
    $message .= "Вы совершили покупку на нашем сайте, благодарим за доверие.<br />";
    $message .= "Скоро мы передадим посылку в службу доставки и пришлем второе письмо с трек-номером.<br /><br />";

    $message .= "Детали заказа:<br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $message .= order_composition($connect, $order_id);
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

    $message_archive = "Благодарим за доверие! Мы получили ваш первый заказ и уже приступили к его подготовке, скоро начнем собирать посылку. Давайте еще раз проверим все детали. Если все указано верно, то просто ожидайте следующего письма, которое придет после передачи посылки в службу доставки.<br /><br />";
    $message_archive .= "Детали заказа:<br />";
    $message_archive .= order_composition($connect, $order_id);
    $message_archive .= "<br />";
    $message_archive .= "$delivery<br />";
    $message_archive .= "ФИО: $full_name<br />";
    $message_archive .= "Телефон: $phone<br />";
    $message_archive .= "Email: $email<br />";
    $message_archive .= "Адрес доставки: $address_text<br />";
    $message_archive .= "<br />";
    $message_archive .= "Если хотите дополнить заказ или заметили неточность, то напишите нашему оператору в любом удобном мессенджере:<br/>";
    $message_archive .= info_messenger();


    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-новичка"
    ];
}

function order_create_after_2_hours($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];
    $theme = "Первое знакомство";

    $message = "Здравствуйте, $full_name! Где-то в уральской тайге, о которой слагали сказы ещё до нас, мы собрали частицу леса и сейчас бережно отправляем её в путь — прямо к вам. Это письмо открывает целую цепочку встреч: расскажем о заказе, напомним о посылке, а порой удивим подарком, достойным хозяйки Медной горы. Так что не спешите отправлять нас в спам — лучше оставьте в разделе «приятные неожиданности». Чем чаще вы с нами, чем больше количество и сумма покупок, тем щедрее наша благодарность.";


    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_send($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];
    $theme = "Посылка уже в пути";

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
    } elseif ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
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

    $full_name = $order['full_name'];

    $message_archive = "Здравствуйте, $full_name! Пока вы читаете эти строки, ваша посылка уже покидает наш склад и отправляется в путешествие — не самое длинное, но с душой и теплом, которое мы теперь передали и вам. Мы знаем толк в скорой обработке: даже когда заказов становится больше, чем шишек в уральском бору по осени, умудряемся собирать и отправлять всё быстро и без суеты. Трек-номер для любопытных и нетерпеливых: $track. Следите за посылкой — она спешит к вам.<br/>";
    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message_archive .= "Уже сейчас вы можете открыть свое приложение ЯндексGo (там, где вызываете такси) и найти всю информацию об этой посылке.<br/><br/>";
    }
    $message_archive .= "А пока ждете — подпишитесь на нас и следите за анонсами акций, розыгрышей и подарков.<br/><br/>Каналы:<br/>";
    $message_archive .= info_chanels();
    $message_archive .= "<br/>Всё просто — нас покупают, потому что мы делаем всю огромную работу сами — от сбора в дикой тайге до конечной упаковки.";


    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-новичка"
    ];
}

function order_delivered($connect, $mail_id, $add_info)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Можно забирать! Посылка уже в пункте выдачи";

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



    $full_name = $order['full_name'];
    $message_archive = "Здравствуйте, $full_name! Ваша посылка прибыла в пункт выдачи.";
    if($delivery != 'Почта России') {
        $days = $add_info['days'];
        $date = $add_info['date'];
        $message_archive .= "Срок хранения: $days дней, до $date.";
    }
    $message_archive .= " <br /><br />";
    $message_archive .= "Оператор:<br/>";
    $message_archive .= info_messenger();

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-новичка"
    ];
}

function order_keep($connect, $mail_id, $add_info)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Посылка: «Я уже заждалась»";

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

    $days = $add_info['days'];
    $date = $add_info['date'];

    $message_archive = "Здравствуйте, $full_name! До окончания хранения осталось $days дн. Посылка хранится до $date. Пожалуйста, заберите заказ. <br /><br />";
    $message_archive .= "Оператор:<br/>";
    $message_archive .= info_messenger();

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-новичка"
    ];
}

function order_received($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];
    $theme = "Посылка получена!";

    $message = "Здравствуйте, $full_name! Наконец посылка попала в хорошие руки — поздравляем с получением. Отдельным письмом пришлём инструкции по товарам.<br /><br />Каналы:<br/>";
    $message .= info_chanels();


    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_2_hours($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Инструкции по применению";
    $message = "Инструкции по товарам из заказа:<br/>";
    $instructions = forming_instructions($connect, $order_id);
    $message .= $instructions['current_instructions'];

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_3_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $theme = "Как насчет скидки на следующий заказ?";
    $message = "Здравствуйте, $full_name! Прошло несколько дней с момента, как посылка оказалась у вас в руках — надеемся, пыль с коробки уже сдута и первое знакомство состоялось. Если что-то приглянулось особенно, нам будет приятно об этом узнать: мы читаем каждый отзыв и особенно радуемся тёплым словам. За отзыв о нас мы предлагаем скидку 300 рублей на следующий заказ. Наш оператор выдаст вам промокод. Для этого отправьте ему снимок экрана с вашим отзывом из Яндекс. Вот ссылка, где нужно оставить на отзыв:<br/>";
    $message .= "<a href='https://clck.ru/3GCDzy'>https://clck.ru/3GCDzy</a><br /><br />";
    $message .= "p.s.: Кстати, ваших отзывов может быть несколько - их можно писать с разных учетных записей. Естественно, скидку 300 рублей мы даем за каждый отзыв (один промокод на заказ).<br /><br />Ссылки на оператора, который ждет сообщения от вас:<br />";
    $message .= info_messenger();

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_7_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $code = generate_code($connect, 'newbie_7_days');
    $theme = "Секретный код – только для вас";
    $individual_code = $code['code'];
    $message = "Здравствуйте, $full_name! Прошла неделя, а нового заказа пока нет. Оно и понятно - для дегустации и первых выводов требуется время. Тем не менее хотим подогреть ваш интерес к нашей продукции, там еще столько всего нужно перепробовать!<br /><br />";
    $message .= "Рассказываем про нашу задумку:<br /><br />";
    $message .= "Используйте кодовое слово &laquo;$individual_code&raquo; в комментарии к заказу и получите отличный ценный подарок при любой покупке! Кодовое слово только ваше и действует только один раз в течение 14 дней. Ваш подарок, бесплатная доставка и другие акции суммируются, поэтому теперь вы можете делать больше частых маленьких покупок. После каждого заказа вы будете получать кодовое слово для получения подарка. Просто вставьте этот код в поле 'комментарий' при оформлении заказа на сайте.";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_21_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $code = generate_code($connect, 'newbie_21_days');
    $individual_code = $code['code'];
    $theme = "Короткая акция";

    $message = "$full_name, приветствуем вас! Мы заметили, что прошли уже три недели после вашей покупки — срок, за который даже самый задумчивый гриб успевает подрасти. За это время у нас на полках как раз подоспела свежая партия. Хочется, чтобы она попала в хорошие руки — например, в ваши.<br /><br />";
    $message .= "Прошлое наше предложение с подарком не было использовано, поэтому мы решили предложить вам кое-что более интересное. Подарок станет еще ценнее! Держите новое кодовое слово — &laquo;$individual_code&raquo; Всавьте его в комментарий к заказу в течение 5 дней. Предложение не повторяется, так что раздумывать особо некогда. Напомним, что при определённой сумме заказа вас ждут бесплатная доставка и ценные подарки на ваш выбор!<br /><br />";
    $message .= "А еще так хочется порекомендовать наш вкуснейший мёд с домашней пасеки! Его добывает потомственный пчеловод, который один живет целое лето со своими пчелами в лесу на пасеке, ну где еще такое найти? Если ещё не пробовали, то самое время!";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_30_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $code = generate_code($connect, 'newbie_30_days');
    $individual_code = $code['code'];
    $theme = "Последний шанс: максимальная скидка";

    $message = "Дорогой наш $full_name! Признаёмся честно и откровенно: это последнее письмо в нашей с вами маленькой саге, и мы решили не мелочиться — перед вами самое щедрое предложение, на которое мы вообще способны. В течение двух дней используйте кодовое слово &laquo;$individual_code&raquo; в комментарии к заказу — получите специальный большой подарок + можно получить бесплатную доставку и подарки на выбор в придачу, если соберете подходящую лесную корзинку товаров. Дальше мы умолкаем и просто надеемся, что однажды вы еще раз откроете нашу посылку, как сундук с чем-то интересным, качественным и добрым. В любом случае благодарим за оказанное доверие и желаем отличного здоровья!";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_180_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $code = generate_code($connect, 'after_180_days');
    $individual_code = $code['code'];

    $theme = "Мы скучаем: у нас кое-что обновилось";
    $message = "Здравствуйте, $full_name! Прошло ни много ни мало целых полгода с вашего последнего визита к нам! Это срок, за который даже самая основательная тайга успевает перемениться. У нас тоже так много обновилось. Если заглянете и оформите заказ, используйте код &laquo;$individual_code&raquo; в комментарии — получите гарантированный классный подарок. И, конечно, не забывайте про наши постоянные бонусы: подарок на выбор и бесплатную доставку при определённой сумме заказа и другие временные акции. Будем рады видеть вас снова!";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function order_received_after_360_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];

    $code = generate_code($connect, 'after_360_days');
    $individual_code = $code['code'];

    $theme = "Прошёл год — загляните на огонёк";
    $message = "Здравствуйте, $full_name! Прошёл ровно год с вашего последнего заказа — срок достаточный, чтобы многое изменилось: ассортимент, упаковка, а возможно, и ваше настроение. Загляните на <a href='https://ural-mhmr.shop'>ural-mhmr.shop</a> и посмотрите, что у нас нового. Используйте код &laquo;$individual_code&raquo; в комментарии к заказу и получите отличный подарок от нас — просто за то, что вернулись, пусть и спустя целый год.";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

// Для постоянников
function constant_order_create($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Ваш новый заказ на URAL-MUHOMOR.RU";
    $message = "Здравствуйте!<br />";
    $message .= "Вы совершили покупку на нашем сайте, благодарим за доверие.<br />";
    $message .= "Скоро мы передадим посылку в службу доставки и пришлем второе письмо с трек-номером.<br /><br />";

    $message .= "Детали заказа:<br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $message .= order_composition($connect, $order_id);
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

    $details = order_composition($connect, $order_id);
    $operator_info = info_messenger();
    $full_name = $order['full_name'];

    $delivery_info = "$delivery<br />";
    $delivery_info .= "ФИО: $full_name<br />";
    $delivery_info .= "Телефон: $phone<br />";
    $delivery_info .= "Email: $email<br />";
    $delivery_info .= "Адрес доставки: $address_text<br />";

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'create_order' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message_archive = nl2br($random_create_text['text']);
    $message_archive = str_replace('{Имя}', $full_name, $message_archive);
    $message_archive = str_replace('{Оператор}', "$operator_info", $message_archive);
    $message_archive = str_replace('{Детали заказа}', "Детали заказа:<br/>$details", $message_archive);
    $message_archive = str_replace('{Доставка}', "$delivery_info", $message_archive);

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-постоянник"
    ];
}

function constant_order_send($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];
    $theme = "Ваша посылка уже в пути";

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
    } elseif ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
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

    $full_name = $order['full_name'];
    $chanel = info_chanels();

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'send_order' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $yandex_text = $random_create_text['other_text'];
    $message_archive = nl2br($random_create_text['text']);
    $message_archive = str_replace('{Имя}', $full_name, $message_archive);
    $message_archive = str_replace('{Каналы}', $chanel, $message_archive);
    $message_archive = str_replace('{Трек}', $track, $message_archive);

    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message_archive = str_replace('{Яндекс}', "<br/>$yandex_text", $message_archive);
    } else {
        $message_archive = str_replace('{Яндекс}', "", $message_archive);
    }

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-постоянник"
    ];
}

function constant_order_delivered($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Посылка уже в пункте выдачи";

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

    $operator_info = info_messenger();
    $full_name = $order['full_name'];

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'delivery_order' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message_archive = nl2br($random_create_text['text']);
    $message_archive = str_replace('{Имя}', $full_name, $message_archive);
    $message_archive = str_replace('{Оператор}', "$operator_info", $message_archive);

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-постоянник"
    ];
}

function constant_order_keep($connect, $mail_id, $add_info)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Посылка: «Я уже заждалась»";

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

    $full_name = $order['full_name'];
    $operator_info = info_messenger();
    $days = $add_info['days'];
    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'keep_order' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message_archive = nl2br($random_create_text['text']);
    $message_archive = str_replace('{Имя}', $full_name, $message_archive);
    $message_archive = str_replace('{Оператор}', "$operator_info", $message_archive);
    $message_archive = str_replace('{N_ДНЕЙ}', "$days", $message_archive);

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиента-постоянник"
    ];
}

function constant_order_received_after_10_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);

    $code = generate_code($connect, 'constant_after_10_days');

    $full_name = $order['full_name'];
    $individual_code = $code['code'];
    $days = $code['all_codes'][0]['days'];
    $theme = "Ваш подарок растёт вместе с заказом";

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'order_receive_after_10_days' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message = nl2br($random_create_text['text']);
    $message = str_replace('{Имя}', $full_name, $message);
    $message = str_replace('{N_ДНЕЙ}', "$days", $message);
    $message = str_replace('{Код}', "$individual_code", $message);

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function constant_order_received_5_after_10_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);

    $code = generate_code($connect, 'constant_5_orders_after_10_days');

    $full_name = $order['full_name'];
    $individual_code = $code['code'];
    $days = $code['all_codes'][0]['days'];

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = '5_order_receive_after_10_days' AND `min_orders` = 5 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message = nl2br($random_create_text['text']);
    $message = str_replace('{Имя}', $full_name, $message);
    $message = str_replace('{N_ДНЕЙ}', "$days", $message);
    $message = str_replace('{Код}', "$individual_code", $message);

    $theme = "Особый знак внимания";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function constant_order_received_10_after_10_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);

    $code = generate_code($connect, 'constant_10_orders_after_10_days');

    $full_name = $order['full_name'];
    $individual_code = $code['code'];
    $days = $code['all_codes'][0]['days'];

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = '5_order_receive_after_10_days' AND `min_orders` = 10 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message = nl2br($random_create_text['text']);
    $message = str_replace('{Имя}', $full_name, $message);
    $message = str_replace('{N_ДНЕЙ}', "$days", $message);
    $message = str_replace('{Код}', "$individual_code", $message);

    $theme = "Редкое письмо для вас";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function constant_order_received_after_2_hours($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $instructions = forming_instructions($connect, $order_id);
    $current_instructions = $instructions['current_instructions'];
    $new_instructions = $instructions['new_instructions'];
    $old_instructions = $instructions['old_instructions'];
    $theme = "Инструкции по применению";

    $random_create_text = mysqli_query($connect, "SELECT * FROM `mails` WHERE `order_type` = 'constant' AND `mail_type` = 'instruction' AND `min_orders` = 0 ORDER BY RAND() LIMIT 1");
    $random_create_text = mysqli_fetch_array($random_create_text);
    $message = nl2br($random_create_text['text']);
    $message = str_replace('{Инструкции_ПРЕЖНИЕ}', $old_instructions, $message);
    $message = str_replace('{Инструкции_НОВЫЕ}', "$new_instructions", $message);
    $message = str_replace('{Инструкции}', "Инструкции:<br/>$current_instructions", $message);

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

// Для наложки
function no_payed_order_create($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Заказ принят: оплата при получении";
    $message = "Здравствуйте!<br />";
    $message .= "Вы совершили покупку на нашем сайте, благодарим за доверие.<br />";
    $message .= "Скоро мы передадим посылку в службу доставки и пришлем второе письмо с трек-номером.<br /><br />";

    $message .= "Детали заказа:<br />";

    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $message .= order_composition($connect, $order_id);
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

    $full_name = $order['full_name'];
    $track = $order['track'];

    $message_archive = "Здравствуйте, $full_name! Заказ принят. Оплата — при получении.<br /><br />";
    $message_archive .= "Детали заказа:<br />";
    $message_archive .= order_composition($connect, $order_id);
    $message_archive .= "<br />";
    $message_archive .= "$delivery<br />";
    $message_archive .= "ФИО: $full_name<br />";
    $message_archive .= "Телефон: $phone<br />";
    $message_archive .= "Email: $email<br />";
    $message_archive .= "Адрес доставки: $address_text<br />";
    $message_archive .= "<br />";
    $message_archive .= "Трек-номер: $track<br />";
    $message_archive .= "Пожалуйста, сохраните его и заберите посылку после прибытия.";

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиент с наложкой"
    ];
}

function no_payed_order_send($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];
    $theme = "Посылка отправлена: оплата при получении";

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
    } elseif ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
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

    $full_name = $order['full_name'];

    $message_archive = "Здравствуйте, $full_name! Ваша посылка передана в доставку и неспешно, но уверенно движется в сторону пункта выдачи. Напомним на всякий случай: поскольку заказ оформлен наложенным платежом, оплатить его нужно будет при получении — коробка откроется только после этого небольшого ритуала. <br/>Трек-номер: $track.";

    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message_archive .= "<br />Откройте свое приложение ЯндексGo и найдите всю информацию о статусе посылки.";
    }

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиент с наложкой"
    ];
}

function no_payed_order_delivered($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];

    $theme = "Посылка уже в пункте выдачи — не забудьте оплатить";

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

    $full_name = $order['full_name'];
    $track = $order['track'];

    $message_archive = "Здравствуйте, $full_name! Ваша посылка добралась до пункта выдачи и теперь терпеливо ждёт встречи с вами, как добрый знакомый у подъезда. Напоминаем: перед тем как забрать заказ, его нужно оплатить — это условие наложенного платежа, никак иначе.<br /> Трек-номер: $track.";

    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message_archive .= "<br />Откройте свое приложение ЯндексGo и найдите всю информацию о статусе посылки.";
    }

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиент с наложкой"
    ];
}

function no_payed_order_delivered_after_2_days($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);

    $order_id = $mail['id_order'];
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);

    $address_id = $order['id_client_address'];
    $address = mysqli_query($connect, "SELECT * FROM `clients_address` WHERE `id` = $address_id");
    $address = mysqli_fetch_array($address);

    $delivery = $address['delivery'];
    $full_name = $order['full_name'];
    $track = $order['track'];

    $theme = "Посылка ждёт вас в пункте выдачи";
    $message = "Здравствуйте, $full_name! Небольшое дружеское напоминание: ваша посылка уже пару дней скучает в пункте выдачи и, кажется, начинает сомневаться, не забыли ли мы вас предупредить. Спешим исправиться — пора её забрать и оплатить, пока она окончательно не заскучала.<br /> Трек-номер: $track.";

    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message .= "<br />Откройте свое приложение ЯндексGo и найдите всю информацию о статусе посылки.";
    }

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function no_payed_order_keep($connect, $mail_id, $add_info)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];


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

    $full_name = $order['full_name'];
    $track = $order['track'];
    $days = $add_info['days'];
    $date = $add_info['date'];

    $message_archive = "Здравствуйте, $full_name! До окончания хранения осталось $days дней — до $date. Пожалуйста, заберите посылку после оплаты при получении. Если нужно продлить срок хранения — напишите нам: <br />.";
    $message_archive .= info_messenger();
    $message_archive .= "<br />";
    $message_archive .= "Трек-номер: $track.";
    if ($delivery == "Яндекс Доставка" || $delivery == "5post(Пятерочка)") {
        $message_archive .= "<br />Откройте приложение ЯндексGo, чтобы проверить статус.";
    }

    $theme = "Осталось $days дней: заберите посылку";

    return [
        'theme' => $theme,
        'message' => $message,
        "message_archive" => $message_archive,
        "add_message_for_archive" => "<br/>Новый вид песем, клиент с наложкой"
    ];
}

function no_payed_order_last_day($connect, $mail_id, $add_info)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);
    $order_id = $mail['id_order'];
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);
    $full_name = $order['full_name'];
    $track = $order['track'];
    $days = $add_info['days'];
    $date = $add_info['date'];

    $message = "Здравствуйте, $full_name! До окончания хранения остался $days день — до $date. Если вы планируете забрать заказ, пожалуйста, сделайте это до указанной даты и оплатите его при получении. При необходимости продления напишите нам:<br/>."
         $message .= info_messenger();
    $message .= "<br />";
    $message .= "Трек-номер: $track.";
    $message .= "<br />";
    $message .= "Мы объясняем последствия невыкупа открыто: доставку туда и обратно оплачиваем мы, поэтому будем благодарны, если заказ получится забрать.";

    $theme = "Остался $days день до возврата посылки";
    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function no_payed_order_back($connect, $mail_id)
{
    $mail = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id` = $mail_id");
    $mail = mysqli_fetch_assoc($mail);

    $order_id = $mail['id_order'];
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_array($order);

    $full_name = $order['full_name'];

    $theme = "Посылка вернулась на склад";
    $message = "Здравствуйте, $full_name! Ваша посылка, не дождавшись встречи с вами, вернулась к нам на склад. Если обстоятельства изменятся и вы захотите оформить заказ заново, будем рады видеть вас снова — без лишних вопросов и упрёков.";

    return [
        'theme' => $theme,
        'message' => $message
    ];
}

function send_mail($connect, $order_id, $type_mail, $add_info = [])
{
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE id = $order_id");
    $order = mysqli_fetch_assoc($order);
    $full_name = $order['full_name'];
    $client_id = $order['id_client'];
    $mail_info = mysqli_query($connect, "SELECT * FROM `orders_mail` WHERE `id_order` = $order_id AND `id_client` = $client_id AND `type` = '$type_mail'");
    if (mysqli_num_rows($mail_info) > 0) {
        $mail_info = mysqli_fetch_assoc($mail_info);
        $mail_id = $mail_info['id'];
        $mail_function = $mail_info['name_function'];
        $order_id = $mail_info['id_order'];
        $email = $mail_info['email'];
        $info = $mail_function($connect, $mail_id, $add_info);
        $theme = $info['theme'];
        $message_archive = $info['add_message_for_archive'] ?? '';
        $message = $info['message'];

        try {
            if ($type_mail == 'order_create' || $type_mail == 'order_send' || $type_mail == 'order_delivered' || $type_mail == 'order_keep') {
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
                $mail->Body = "$message";

                // Отправка письма
                $mail->send();
            }
        } catch (Exception $e) {
            file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true) . "-client<br/>", FILE_APPEND);
            send_info_telegram($connect, $order_id, 'Не получилось отправить письмо на email у следующего заказа: ');
        }

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
            $message = isset($info['message_archive'])  ? $info['message_archive'] : $info['message'];
            $mail->Body = "$message<br/>$message_archive<br/>id Заказа - $order_id";

            // Отправка письма
            $mail->send();
        } catch (Exception $e) {
            file_put_contents("message_error_log.txt", print_r($mail->ErrorInfo, true) . "-archive<br/>", FILE_APPEND);
        }


        if ($type_mail == 'order_received') {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id AND `type` = 'order_back'");
        }

        if ($type_mail == 'order_back') {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id AND `type` = 'order_received'");
        }

        if($type_mail == 'order_received' || $type_mail == 'order_back') {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id AND `type` IN ('order_delivered', 'order_delivered_after_2_days', 'order_keep', 'order_last_day')");
        }

        mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id` = $mail_id");
    }
}

function start_mails($connect, $order_id)
{
    $date = new DateTime();
    $interval = new DateInterval('PT2H');
    $time = $date->add($interval)->format('H:i');
    $date = $date->format('Y-m-d');
    $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $order_id");
    $order = mysqli_fetch_assoc($order);
    $client_id = $order['id_client'];
    $orders = mysqli_query($connect, "SELECT `orders`.`id` FROM `orders` WHERE `id_client` = $client_id");
    $orders = mysqli_num_rows($orders);
    $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
    $client = mysqli_fetch_assoc($client);
    $email = $client['email'];
    $type = 'order_create';

    mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_client` = $client_id AND `type` IN ('order_received_after_3_days', 'order_received_after_7_days', 'order_received_after_10_days', 'order_received_after_21_days', 'order_received_after_30_days', 'order_received_after_180_days', 'order_received_after_360_days')");

    if (empty($order['number'])) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_create', 'order_create','$email')");
        if($orders == 1) {
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`, `date`, `time`) VALUES ($order_id,$client_id,'order_create_after_2_hours', 'order_create_after_2_hours','$email', '$date', '$time:00')");
        }
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_send', 'order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_delivered', 'order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_delivered_after_2_days', 'order_delivered_after_2_days','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_keep', 'order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_last_day', 'order_last_day','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'no_payed_order_back', 'order_back','$email')");
        if ($orders > 1) {
            if ($orders % 10 == 0) {
                mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_10_after_10_days', 'order_received_after_10_days','$email')");
            } elseif ($orders % 5 == 0) {
                mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_5_after_10_days', 'order_received_after_10_days','$email')");
            } else {
                mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_after_10_days', 'order_received_after_10_days','$email')");
            }
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_after_2_hours', 'order_received_after_2_hours','$email')");
        } else {
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received', 'order_received','$email')");
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_2_hours', 'order_received_after_2_hours','$email')");
        }
    }
    elseif ($orders > 1) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_create', 'order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_send', 'order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_delivered', 'order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_keep', 'order_keep','$email')");

        if ($orders % 10 == 0) {
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_10_after_10_days', 'order_received_after_10_days','$email')");
        } elseif ($orders % 5 == 0) {
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_5_after_10_days', 'order_received_after_10_days','$email')");
        } else {
            mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_after_10_days', 'order_received_after_10_days','$email')");
        }

        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'constant_order_received_after_2_hours', 'order_received_after_2_hours','$email')");

    } else {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_create', 'order_create','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`, `date`, `time`) VALUES ($order_id,$client_id,'order_create_after_2_hours', 'order_create_after_2_hours','$email', '$date', '$time:00')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_send', 'order_send','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_delivered', 'order_delivered','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_keep', 'order_keep','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received', 'order_received','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_2_hours', 'order_received_after_2_hours','$email')");
    }
    if($orders == 1) {
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_3_days', 'order_received_after_3_days','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_7_days', 'order_received_after_7_days','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_21_days', 'order_received_after_21_days','$email')");
        mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_30_days', 'order_received_after_30_days','$email')");
    }
    mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_180_days', 'order_received_after_180_days','$email')");
    mysqli_query($connect, "INSERT INTO `orders_mail`(`id_order`, `id_client`, `name_function`, `type`, `email`) VALUES ($order_id,$client_id,'order_received_after_360_days', 'order_received_after_360_days','$email')");

    send_mail($connect, $order_id, $type);
}