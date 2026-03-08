<?php
require_once __DIR__ . "/../../connect.php";
require_once __DIR__ . "/../../helpers/check_data.php";

$messages = check_data(['date_start', 'date_end'], $_POST);

require_once __DIR__ . "/../../helpers/check_messages.php";

$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];

$expenses = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `date` >= '$date_start' AND `date` <= '$date_end' ORDER BY `expenses`.`date` ASC");

$new_expenses = [
    'goods' => [],
    'consumable' => [],
    'other' => [],
    'orders' => [
        'count' => [],
        'dates' => []
    ]
];

$start = new DateTime("$date_start");
$end = new DateTime("$date_end");
// Создать объект DateInterval с интервалом 1 день
$interval = new DateInterval('P1D');
$dates = [];
for($i = $start; $i <= $end; $i->add($interval)){
    $date = $i->format('Y-m-d');
    $dates[$date] = [
        "date" => $date,
    ];
}
$new_expenses['orders']['dates'] = $dates;
while ($expense = mysqli_fetch_assoc($expenses)) {
    $quantity = $expense['quantity'];
    $date = $expense['date'];
    $good_id = $expense['id_good'];
    $id_order = $expense['id_order_or_supply'];
    $type = $expense['id_expense_type'];

    if($type == 1) {
        $new_expenses['orders']['count'][] = "$id_order $type";
        if(isset($new_expenses['orders']['dates']["$date"]['count'])) {
            $new_expenses['orders']['dates']["$date"]['count'][] = "$id_order $type";
        } else {
            $new_expenses['orders']['dates']["$date"] = [
                'date' => $date,
                "count" => ["$id_order $type"]
            ];
        }
    }

    if($expense['id_expense_good_type'] == 1) {
        $good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
        $good = mysqli_fetch_assoc($good);
        $product_id = $good['id_product'];
        $count = $good['quantity'];
        $amount = $count * $quantity;
        if(isset($new_expenses['goods'][$product_id])) {
            $new_expenses['goods'][$product_id]['quantity'] += $amount;
            if(isset($new_expenses['goods'][$product_id]['types']["$count"])) {
                $new_expenses['goods'][$product_id]['types']["$count"]['quantity'] += $quantity;
                $new_expenses['goods'][$product_id]['types']["$count"]['orders'][] = "$id_order $type";
            } else {
                $new_expenses['goods'][$product_id]['types']["$count"] = [
                    "count" => $count,
                    "quantity" => $quantity,
                    "orders" => ["$id_order $type"]
                ];
            }
            $new_expenses['goods'][$product_id]['orders'][] = "$id_order $type";
            if(isset($new_expenses['goods'][$product_id]['dates']["$date"]["quantity"])) {
                $new_expenses['goods'][$product_id]['dates']["$date"]['quantity'] += $amount;
                $new_expenses['goods'][$product_id]['dates']["$date"]['orders'][] = "$id_order $type";
                if(isset($new_expenses['goods'][$product_id]['dates']["$date"]['types']["$count"])) {
                    $new_expenses['goods'][$product_id]['dates']["$date"]['types']["$count"]['quantity'] += $quantity;
                    $new_expenses['goods'][$product_id]['dates']["$date"]['types']["$count"]['orders'][] = "$id_order $type";
                } else {
                    $new_expenses['goods'][$product_id]['dates']["$date"]['types']["$count"] = [
                        "count" => $count,
                        "quantity" => $quantity,
                        "orders" => ["$id_order $type"]
                    ];
                }
            } else {
                $new_expenses['goods'][$product_id]['dates']["$date"] = [
                    "date" => $date,
                    "quantity" => $amount,
                    "orders" => ["$id_order $type"],
                    "types" => [
                        "$count" => [
                            "count" => $count,
                            "quantity" => $quantity,
                            "orders" => ["$id_order $type"]
                        ]
                    ]
                ];
            }
        } else {
            $new_expenses['goods'][$product_id] = [
                "product_id" => $product_id,
                "quantity" => $amount,
                "composition_quantity" => 0,
                "types" => [
                    "$count" => [
                        "count" => $count,
                        "quantity" => $quantity,
                        "orders" => ["$id_order $type"]
                    ]
                ],
                "orders" => [
                    "$id_order $type"
                ],
                "dates" => $dates
            ];
            $new_expenses['goods'][$product_id]["dates"]["$date"] = [
                "date" => $date,
                "quantity" => $amount,
                "orders" => ["$id_order $type"],
                "types" => [
                    "$count" => [
                        "count" => $count,
                        "quantity" => $quantity,
                        "orders" => ["$id_order $type"]
                    ]
                ]
            ];
        }
        if($good['weight'] == 1) {
            $warehouse = $good['id_warehouse'];
            $weight = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product_id AND `id_warehouse` = $warehouse");
            $weight = mysqli_fetch_assoc($weight);
            if($weight['composite'] == 1) {
                $weight_id = $weight['id'];
                $composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $weight_id ");
                $composite = mysqli_fetch_assoc($composite);
                $composite_id = $composite['id'];
                $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $composite_id");

                while($composite_item = mysqli_fetch_assoc($composite_list)) {
                    $proportion = $composite_item['proportion'];
                    $weight_id = $composite_item['id_good_weight'];
                    $product_id = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id` = '$weight_id'");
                    $product_id = mysqli_fetch_assoc($product_id)['id_product'];

                    if(isset($new_expenses['goods'][$product_id])) {
                        $new_expenses['goods'][$product_id]['composition_quantity'] += $amount / 100 * $proportion;
                    } else {
                        $new_expenses['goods'][$product_id] = [
                            "product_id" => $product_id,
                            "quantity" => 0,
                            "composition_quantity" => $amount / 100 * $proportion,
                            "types" => [],
                            "orders" => [],
                            "dates" => [],
                        ];
                    }
                }
            }
        }
        continue;
    }

    if($expense['id_expense_good_type'] == 2) {
        $consumable = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `id` = $good_id");
        $consumable = mysqli_fetch_assoc($consumable);
        $title = $consumable['title'];
        if(isset($new_expenses['consumable']["$title"])) {
            $new_expenses['consumable']["$title"]['quantity'] += $quantity;
        } else {
            $new_expenses['consumable']["$title"] = [
                "title" => $title,
                "quantity" => $quantity,
            ];
        }
        continue;
    }

    if($expense['id_expense_good_type'] == 3) {
        $other = mysqli_query($connect, "SELECT * FROM `goods_other` WHERE `id` = $good_id");
        $other = mysqli_fetch_assoc($other);
        $title = $other['title'];
        if(isset($new_expenses['other']["$title"])) {
            $new_expenses['other']["$title"]['quantity'] += $quantity;
        } else {
            $new_expenses['other']["$title"] = [
                "title" => $title,
                "quantity" => $quantity,
            ];
        }
        continue;
    }
}

$goods = [];

foreach ($new_expenses['goods'] as $good) {
    $product_id = $good['product_id'];
    $new_good = [
        "quantity" => $good['quantity'],
        "composition_quantity" => $good['composition_quantity'],
        "types" => [],
        "orders" => count(array_unique($good['orders'])),
        "dates" => []
    ];

    $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `id` = $product_id");
    $product = mysqli_fetch_assoc($product);
    $measure_id = $product['id_measure_unit'];

    $measure = mysqli_query($connect, "SELECT * FROM `measure_units` WHERE `id` = $measure_id");
    $measure = mysqli_fetch_assoc($measure);

    $new_good['product'] = $product['show_title'] ? $product['show_title'] : $product['title'];
    $new_good['measure'] = $measure['title'];
    foreach ($good['types'] as $type) {
        $new_good['types'][] = [
            "count" => $type['count'],
            "quantity" => $type['quantity'],
            "orders" => count(array_unique($type['orders'])),
        ];
    }
    $weeks = [];
    $months = [];
    $years = [];

    $count = count($good['dates']);
    $i = 1;
    $j = 1;
    $date_start = '';
    $date_end = '';
    $week = [
      "quantity" => 0,
      "orders" => 0,
      "types" => [],
    ];

    foreach ($good['dates'] as $date) {
        if($i == 1) {
            $date_start = $date['date'];
        }

        if(!isset($date['orders'])) {
            $new_date = [
                "date" => $date['date'],
                "quantity" => 0,
                "orders" => 0,
                "types" => []
            ];
            $date = [
                "date" => $date['date'],
                "quantity" => 0,
                "orders" => [],
                "types" => []
            ];
        } else {
            $new_date = [
                "date" => $date['date'],
                "quantity" => 0,
                "orders" => count(array_unique($date['orders'])),
                "types" => []
            ];
        }



        $week['orders'] += count(array_unique($date['orders']));
        $week['quantity'] += $date['quantity'];

        $month = explode("-", $date['date']);

        $year = $month[0];
        $month = $month[0] . "-" . $month[1];

        if(isset($months[$month])) {
            $months[$month]['quantity'] += $date['quantity'];
            $months[$month]['orders'] += count(array_unique($date['orders']));
        } else {
            $months[$month] = [
                "month" => $month,
                "quantity" => $date['quantity'],
                "orders" => count(array_unique($date['orders'])),
                "types" => []
            ];
        }

        if(isset($years[$year])) {
            $years[$year]['quantity'] += $date['quantity'];
            $years[$year]['orders'] += count(array_unique($date['orders']));
        } else {
            $years[$year] = [
                "year" => $year,
                "quantity" => $date['quantity'],
                "orders" => count(array_unique($date['orders'])),
                "types" => []
            ];
        }

        foreach ($date['types'] as $date_type) {
            $new_date['quantity'] += $date_type['quantity'] * $date_type['count'];
            $new_date['types'][] = [
                "count" => $date_type['count'],
                "quantity" => $date_type['quantity'],
                "orders" => count(array_unique($date_type['orders'])),
            ];

            if(isset($week['types'][$date_type['count']])) {
                $week['types'][$date_type['count']]['quantity'] += $date_type['quantity'];
                $week['types'][$date_type['count']]['orders'] += count(array_unique($date_type['orders']));
            } else {
                $week['types'][$date_type['count']] = [
                    "count" => $date_type['count'],
                    "quantity" => $date_type['quantity'],
                    "orders" => count(array_unique($date_type['orders'])),
                ];
            }
            if(isset($months[$month]['types'][$date_type['count']])) {
                $months[$month]['types'][$date_type['count']]['quantity'] += $date_type['quantity'];
                $months[$month]['types'][$date_type['count']]['orders'] += count(array_unique($date_type['orders']));
            } else {
                $months[$month]['types'][$date_type['count']] = [
                    "count" => $date_type['count'],
                    "quantity" => $date_type['quantity'],
                    "orders" => count(array_unique($date_type['orders'])),
                ];
            }

            if(isset($years[$year]['types'][$date_type['count']])) {
                $years[$year]['types'][$date_type['count']]['quantity'] += $date_type['quantity'];
                $years[$year]['types'][$date_type['count']]['orders'] += count(array_unique($date_type['orders']));
            } else {
                $years[$year]['types'][$date_type['count']] = [
                    "count" => $date_type['count'],
                    "quantity" => $date_type['quantity'],
                    "orders" => count(array_unique($date_type['orders'])),
                ];
            }
        }
        if($i == 7) {
            $date_end = $date['date'];

            $week_types = [];

            foreach ($week['types'] as $week_type) {
                $week_types[] = $week_type;
            }

            $weeks[] = [
                "date_start" => $date_start,
                "date_end" => $date_end,
                "quantity" => $week['quantity'],
                "orders" => $week['orders'],
                "types" => $week_types,
            ];
            $date_start = '';
            $date_end = '';

            $week['quantity'] = 0;
            $week['orders'] = 0;
            $week['types'] = [];

            $i = 0;
        }
        $new_good['dates'][] = $new_date;
        $i++;
        $j++;

        if($j == $count) {
            $date_end = $date['date'];

            $week_types = [];

            foreach ($week['types'] as $week_type) {
                $week_types[] = $week_type;
            }

            $weeks[] = [
                "date_start" => $date_start,
                "date_end" => $date_end,
                "quantity" => $week['quantity'],
                "orders" => $week['orders'],
                "types" => $week_types,
            ];
        }
    }
    $new_good['weeks'] = $weeks;
    $new_good['months'] = $months;
    $new_good['years'] = $years;

    $goods[] = $new_good;
}

$new_expenses['orders']['count'] = count(array_unique($new_expenses['orders']['count']));

$consumable_list = [];
$other_list = [];
$order_list = [];

foreach ($new_expenses['consumable'] as $consumable) {
    $consumable_list[] = $consumable;
}
foreach ($new_expenses['other'] as $other) {
    $other_list[] = $other;
}

foreach ($new_expenses['orders']['dates'] as $date) {
    $order_list[] = [
      "date" => $date['date'],
      "count" => isset($date['count']) ? count(array_unique($date['count'])) : 0
    ];
}
$new_expenses['orders']['dates'] = $order_list;
$new_expenses['goods'] = $goods;
$new_expenses['consumable'] = $consumable_list;
$new_expenses['other'] = $other_list;

$new_expenses['orders']['weeks'] = [];
$new_expenses['orders']['months'] = [];
$new_expenses['orders']['years'] = [];

$week = [
    "count"=> 0
];
$i = 1;
$j = 1;
$date_start = '';
$date_end = '';
$count = count($new_expenses['orders']['dates']);
foreach ($new_expenses['orders']['dates'] as $expense_order) {
    if($i == 1) {
        $date_start = $expense_order['date'];
    }
    $week['count'] += $expense_order['count'];

    $month = explode("-", $expense_order['date']);

    $year = $month[0];
    $month = $month[0] . "-" . $month[1];

    if(isset($new_expenses['orders']['months'][$month])) {
        $new_expenses['orders']['months'][$month]['count'] += $expense_order['count'];
    } else {
        $new_expenses['orders']['months'][$month] = [
            "count" => $expense_order['count'],
            "month" => $month,
        ];
    }

    if(isset($new_expenses['orders']['years'][$year])) {
        $new_expenses['orders']['years'][$year]['count'] += $expense_order['count'];
    } else {
        $new_expenses['orders']['years'][$year] = [
            "count" => $expense_order['count'],
            "year" => $year,
        ];
    }

    if($i == 7) {
        $date_end = $expense_order['date'];

        $new_expenses['orders']['weeks'][] = [
            "date_start" => $date_start,
            "date_end" => $date_end,
            "count" => $week['count'],
        ];

        $week['count'] = 0;
        $i = 0;
    }
    $i++;
    $j++;

    if($j == $count) {
        $date_end = $expense_order['date'];
    }
}
$new_expenses['date_start'] = $_POST['date_start'];
$new_expenses['date_end'] = $_POST['date_end'];

$req = [
  "messages" => ['Список графиков успешно получен'],
  "graphics" => $new_expenses
];
http_response_code(200);
echo json_encode($req);