<?php
//require_once __DIR__ . "/../api/connect.php";
//require_once __DIR__ . "/basic_function.php";
//require_once __DIR__ . "/keyboards.php";
//$chat_list = mysqli_query($connect, "SELECT * FROM `telegram_clients`");
//$count = 0;
//$chanel_id = -1003442849406;
//$message = "⚡️-15%, бесплатная доставка и подарки!\n\nМикродозинг, шляпки, чай и мед \nот ural-mhmr.shop \n\nСобственный сбор и производство, адекватные цены, поддержка и сопровождение по курсу \n\n<a href='https://clck.ru/3GCDzy'>Отзывы на Яндекс</a>";
//while ($chat = mysqli_fetch_assoc($chat_list)) {
//    $chat_id = $chat['id_chat'];
//    $result = json_decode(checkFollow([
//        "chat_id" => $chanel_id,
//        "user_id" => $chat_id
//    ]), true);
//    if($result['result']['status'] === 'left') {
//        sendMessage([
//            "chat_id" => $chat_id,
//            "text" => $message,
//            "parse_mode" => "html",
//            "reply_markup" => json_encode([
//                "inline_keyboard" => $message_promo_keyboard
//            ])
//        ]);
//    }
//}
echo "<h1>Рассылка отправлена</h1>";
