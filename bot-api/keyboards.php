<?php 
    $start_keyboard = [
        [
            [
                "text" => "Сайт - для покупки",
                "callback_data" => "",
                "url" => "https://ural-mhmr.shop"
            ],
        ],
        [
            [
                "text" => "Прайс",
                "callback_data" => "",
                "url" => "https://ural-mhmr.shop/#products"
            ],
            [
                "text" => "ПРОМОКОД",
                "callback_data" => "give_promo",
            ],

        ],
        [
            [
                "text" => "Канал",
                "callback_data" => "",
                "url" => "https://t.me/ural_mhmr_shop/2"
            ],
            [
                "text" => "Чат",
                "callback_data" => "",
                "url" => "https://t.me/ural_mhm_chat"
            ],
            [
                "text" => "Оператор",
                "callback_data" => "",
                "url" => "https://t.me/mhmr_shop_operator"

            ],
        ],
        [
            [
                "text" => "О нас",
                "callback_data" => "give_info",
            ],
        ],
        [
            [
                "text" => "Брошюра",
                "callback_data" => "give_book",
            ],
                        [
                "text" => "Наложка",
                "callback_data" => "give_superimposes",

            ],
        ],
        [
            [
                "text" => "Прочтите отзывы о нас в Яндекс и Telegram",
                "callback_data" => "take_reviews",
            ],
        ],
        [
            [
                "text" => "Часто задаваемые вопросы",
                "callback_data" => "",
                "url" => "https://ural-mhmr.shop/faq"
            ],
        ],
    ];

    $reviews_keyboard = [
        [
            [
                "text" => "Яндекс",
                "callback_data" => "",
                "url" => "https://yandex.ru/maps/org/ural_mhmr_shop/217923426154/reviews/"
            ],
            [
                "text" => "Telegram",
                "callback_data" => "",
                "url" => "https://t.me/ural_mhm_feedback"
            ],
        ],
        [
            [
                "text" => "⬅️ Назад в главное меню",
                "callback_data" => "start",
            ],
        ]
    ];

    $info_keyboard = [
        [
            [
                "text" => "⬅️ Назад в главное меню",
                "callback_data" => "start",
            ],
        ],
    ];
    $follow_keyboard = [
        [
            [
                "text" => "Наш канал",
                "callback_data" => "",
                "url" => "https://t.me/ural_mhmr_shop/2"
            ],
        ],
        [
            [
                "text" => "✅ Я подписался на канал",
                "callback_data" => "give_promo",
            ],
        ],
        [
            [
                "text" => "⬅️ Назад в главное меню",
                "callback_data" => "start",
            ],
        ],
    ];
    $message_promo_keyboard = [
        [
            [
                "text" => "🔴 Получить промокод",
                "callback_data" => "give_message_promo",
            ],
        ],
    ];
    $message_promo_follow_keyboard = [
        [
            [
                "text" => "Наш канал",
                "callback_data" => "",
                "url" => "https://t.me/ural_mhmr_shop/2"
            ],
        ],
        [
            [
                "text" => "✅ Я подписался на канал",
                "callback_data" => "give_message_promo",
            ],
        ],
    ];