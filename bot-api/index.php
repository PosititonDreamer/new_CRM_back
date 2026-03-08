<?php
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        header("Location: /");
    }

    require_once __DIR__ . "/basic_function.php";
    require_once __DIR__ . "/products.php";
    require_once __DIR__ . "/keyboards.php";
    require_once __DIR__ . "/../api/connect.php";

    $price_keyboard = [];

    foreach($products as $key => $product) {
        $price_keyboard[] = [
                [
                "text" => $product['name'],
                "callback_data" => "products_$key"
            ]
        ];
    }

    $price_keyboard[] = [
        [
            "text" => "⬅️ Назад в главное меню",
            "callback_data" => "start",
        ]
    ];

    $data = file_get_contents("php://input");
    $data = json_decode($data, true);
    file_put_contents(__DIR__ . "/test.txt", print_r($data, true));

    if(isset($data['message'])) {
        $message_id = $data["message"]["message_id"];
        $chat_id = $data["message"]["chat"]["id"];
        $chat = mysqli_query($connect, "SELECT * FROM `telegram_clients` WHERE `id_chat` = '$chat_id'");
        if(mysqli_num_rows($chat) == 0) {
            mysqli_query($connect, "INSERT INTO `telegram_clients`(`id_chat`) VALUES ('$chat_id')");
        }

        if($data['message']['text'] === '/start hello' || $data['message']['text'] === '/start') {
            sendMessage([
                "chat_id" => $chat_id,
                "text" => "ural-mhmr.shop приветствует вас. Этот бот решит почти все ваши вопросы, с остальными поможет наш оператор. \nПриятных покупок!",
                "parse_mode" => "html",
                "reply_markup" => json_encode([
                    "inline_keyboard" => $start_keyboard
                ]),
                "disable_web_page_preview" => 1
            ]);
        } else {
            deleteMessage([
                "chat_id" => $chat_id,
                "message_id" => $message_id
            ]);
        }
        
    } elseif(isset($data['callback_query'])) {
        $message_id = $data["callback_query"]["message"]["message_id"];
        $chat_id = $data["callback_query"]["message"]["chat"]["id"];
        $user_id = $data["callback_query"]["from"]["id"];
        $callback = $data["callback_query"]["data"];
        $product = null;
        $chat = mysqli_query($connect, "SELECT * FROM `telegram_clients` WHERE `id_chat` = '$chat_id'");
        if(mysqli_num_rows($chat) == 0) {
            mysqli_query($connect, "INSERT INTO `telegram_clients`(`id_chat`) VALUES ('$chat_id')");
        }

        if(preg_match('/products_/', $callback)) {
            $explode_callback = explode("_", $callback);
            $callback = "give_price";
            $product = $explode_callback[1];
        }

    
        deleteMessage([
            "chat_id" => $chat_id,
            "message_id" => $message_id
        ]);

        switch($callback) {
            case "give_info":
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "Кратко о нас:\n\nМы — команда из Пермского края, живём в уральской тайге. Миссия: мы заготавливаем очень сильные грибы и травы, чтобы давать пользу людям! \n\n4 года на рынке. Мы профи в заготовке лесных продуктов, поэтому не имеем негативных отзывов. Мы не занимаемся псевдошаманизмом и рассказами о волшебстве. Производим полный цикл — от сбора до продажи. Также стараемся держать приятные цены и проводим вкусные акции.\n\n👍🏾 Мы продаем только наши уральские грибы последнего года сбора (то есть самые сильные и свежие), эффект проверяем лично.\n\nТысячи наших покупателей высоко ценят качество и сервис.\n\n👉🏼🍄 Мы часто проводим акции для подписчиков нашего канала, дарим подарки и промокоды. А ещё интересно пишем!\n\n<b>ПОДПИШИТЕСЬ, БУДЕМ НА СВЯЗИ</b> 👍🏼💚",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $info_keyboard
                    ]),
                    "disable_web_page_preview" => 1
                ]);
                break;
            case "give_book":
                sendDocument([
                    "chat_id" => $chat_id,
                    "caption" => "",
                    "document" => curl_file_create(__DIR__ . '/book.pdf', 'application/pdf' , 'Брошюра.pdf'),
                ]);
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "⬆️ Это наша небольшая брошюра, для удобства её можно распечатать. \n\nСовсем скоро мы будем печатать ее и вкладывать в посылки обновленную брошюру. Спасибо за интерес к нашей компании 🫱🏼‍🫲🏽💚",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $start_keyboard
                    ]),
                    "disable_web_page_preview" => 1
                ]);
                break;
            case "give_promo": 
                $chanel_id = -1003442849406;
                $result = json_decode(checkFollow([
                    "chat_id" => $chanel_id,
                    "user_id" => $user_id
                ]), true);

                if($result['result']['status'] === 'left') {
                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => "Вы не подписаны на канал, подпишитесь по кнопке ниже, чтобы получить промокод",
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => $follow_keyboard
                        ]),
                        "disable_web_page_preview" => 1
                    ]);
                } else {
                    $date = date("Y-m-d");
                    $promo = mysqli_query($connect, "SELECT * FROM `promos` WHERE `date_start` <= '$date' AND `date_end` >= '$date'");
                    $promo = mysqli_fetch_assoc($promo)['title'];
                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => "Держи ЕЖЕМЕСЯЧНЫЙ промокод на скидку 2%:\n\n<code>$promo</code>\n\nПромокод можно скопировать нажав по нему. Он обновляется каждый месяц.\n\nПромокод нужно вставить в поле «Промокод» на <a href='https://www.ural-mhmr.shop/'>нашем сайте</a>  при оформлении заказа",
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => $start_keyboard
                        ]),
                        "disable_web_page_preview" => 1
                    ]);
                }
                break;
            case "take_reviews":
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "<b>Скидка за отзыв</b>\n\nТакже вы можете получить подарок - скидку за отзыв о нас в Яндекс. Для этого перейдите по ссылке, напишите отзыв, сделайте снимок экрана и отправьте его нашему оператору. В ответ он пришлет вам промокод на 300 рублей.",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $reviews_keyboard
                    ]),
                    "disable_web_page_preview" => 1
                ]);
                break;
            case "give_gifts":
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "При оформлении заказа автоматически будут предложены следующие подарки. Подарки на выбор суммируются! \n\nПри заказе\nот 4к — доставка по РФ бесплатно\nот 10к — скидка 350 рублей\nот 11к — ценный подарок №1\nот 15к — фирменный магнит\nот 20к — скидка 3%\nот 22к — ценный подарок №2\nот 30к — скидка 4%\nот 50к — скидка 5%\nот 100к — скидка 6%\n\nПромокод суммируется с общей скидкой\n\nПринимаем оптовые заказы на специальных условиях. 🚚",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [
                                [
                                    "text" => "⬅️ Назад в главное меню",
                                    "callback_data" => "start",
                                ]
                            ],
                        ]
                    ]),
                    "disable_web_page_preview" => 1
                ]);
                break;
            case "give_superimposes":
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "Для оформления заказа наложенным платежом, напишите нашему <a href='https://t.me/mhmr_shop_operator'>оператору</a>:\n\nЭто можно скопировать:\n\n<code>1. Список товаров для покупки\n2. Службу доставки: CDEK, Яндекс доставка или почта России\n3. ФИО получателя\n4. Город и адрес пункта выдачи службы доставки\n5. Номер телефона получателя</code>\n\nПри оформлении наложенным платежом, к стоимости заказа добавляется комиссия службы доставки",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => [
                            [
                                [
                                    "text" => "⬅️ Назад в главное меню",
                                    "callback_data" => "start",
                                ]
                            ],
                        ]
                    ]),
                    "disable_web_page_preview" => 1
                ]);
                break;
            case "give_price":
                if($product) {
                    $product_info = $products[$product];
                    $final_text = "<b>" . $product_info['name'] . "</b>\n\n";
                    $final_text .= $product_info['descroption'];
                    foreach($product_info['price_list'] as $value) {
                        $final_text .= "<b>" . $value['name'] . "</b> - " . $value['price'] . "₽\n";
                    }

                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => $final_text,
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => [
                                [
                                    [
                                        "text" => "Купить товар",
                                        "callback_data" => "",
                                        "url" => $product_info['url']
                                    ],
                                ],
                                [
                                    [
                                        "text" => "⬅️ Вернуться в прайс-лист",
                                        "callback_data" => "give_price",
                                    ],
                                ],
                            ]
                        ]),
                        "disable_web_page_preview" => 1
                    ]);

                } else {
                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => "Выберите интересующий вас товар ниже:",
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => $price_keyboard
                        ]),
                        "disable_web_page_preview" => 1
                    ]);
                }
                break;
            case "give_message_promo":
                $chanel_id = -1003442849406;
                $result = json_decode(checkFollow([
                    "chat_id" => $chanel_id,
                    "user_id" => $user_id
                ]), true);

                if($result['result']['status'] === 'left') {
                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => "Вы не подписаны на канал, подпишитесь по кнопке ниже, чтобы получить промокод",
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => $message_promo_follow_keyboard
                        ]),
                        "disable_web_page_preview" => 1
                    ]);
                } else {
                    sendMessage([
                        "chat_id" => $chat_id,
                        "text" => "Держи промокод, успей воспользоваться до 4 марта! Приятной весны!🌷\n\n<code>ПОДПИСКА15</code>\n\nПромокод нужно ввести на сайте при оформлении заказа: https://www.ural-mhmr.shop\n\n❗️ Время действия промокода ограничено.\n🕙 Акция действует до 04 марта 2026 г. (23:59 мск).",
                        "parse_mode" => "html",
                        "reply_markup" => json_encode([
                            "inline_keyboard" => $info_keyboard
                        ]),
                        "disable_web_page_preview" => 1
                    ]);
                }
                break;
            default:
                sendMessage([
                    "chat_id" => $chat_id,
                    "text" => "ural-mhmr.shop приветствует вас. Этот бот решит почти все ваши вопросы, с остальными поможет наш оператор. \nПриятных покупок!",
                    "parse_mode" => "html",
                    "reply_markup" => json_encode([
                        "inline_keyboard" => $start_keyboard
                    ]),
                    "disable_web_page_preview" => 1
                ]);
        }
    }

    