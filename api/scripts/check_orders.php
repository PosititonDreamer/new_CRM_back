<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../orders/functions_mail.php';

$orders_add_mails = [];
$interval_2_hours = new DateInterval('PT2H');
$interval_1 = new DateInterval('P1D');
$interval_3 = new DateInterval('P3D');
$interval_7 = new DateInterval('P7D');
$interval_10 = new DateInterval('P10D');
$interval_21 = new DateInterval('P21D');
$interval_30 = new DateInterval('P30D');
$interval_180 = new DateInterval('P180D');
$interval_360 = new DateInterval('P360D');

$orders_delivered = mysqli_query($connect, "SELECT DISTINCT `orders_mail`.`id_order` FROM `orders_mail` JOIN `orders` ON `orders`.`id` = `orders_mail`.`id_order` WHERE `orders`.`id_order_status` = 4 AND `type` = 'order_received'");
$ids = [];

while ($orders_item = mysqli_fetch_assoc($orders_delivered)) {
    $ids[] = $orders_item['id_order'];
}

$add_where = "`orders`.id IN (" . join(', ', $ids) . ")";
$current_time = date("H");

// todo:: CDEK
$orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`track`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND `clients_address`.`delivery` = 'CDEK' AND $add_where");

if (mysqli_num_rows($orders) > 0) {
    $array = array();
    $array['grant_type'] = 'client_credentials';
    $array['client_id'] = 'Y5MbFmrIprTAQ30GbDim92Yq4aBmoLxw';
    $array['client_secret'] = 'FjmfQFBMHZTWLFfdbhYwcWTDQw8CYMGY';

    $ch = curl_init('https://api.cdek.ru/v2/oauth/token?parameters');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, '', '&'));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    curl_close($ch);
    $res = json_decode($html, true);

    $token = $res['access_token'];
    $date = date("Y-m-d");

    while ($order = mysqli_fetch_assoc($orders)) {
        $order_id = $order['id'];
        $track = $order['track'];
        $ch = curl_init('https://api.cdek.ru/v2/orders/?cdek_number=' . $track);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        curl_close($ch);
        $html = json_decode($html, true);
        if (isset($html['entity']) && isset($html['entity']['statuses'])) {
            $last_status = $html['entity']['statuses'][0];

            if ($last_status['code'] === 'DELIVERED') {
                // todo: Добавить здесь обработку писем
                send_mail($connect, $order_id, 'order_received');
                $last_date_status = $last_status['date_time'];
                $start = new DateTime("$last_date_status");
                $start = $start->add($interval_2_hours);
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_2_hours',
                    'date' => $start->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_3_days',
                    'date' => $start->add($interval_3)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_7_days',
                    'date' => $start->add($interval_7)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_10_days',
                    'date' => $start->add($interval_10)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_21_days',
                    'date' => $start->add($interval_21)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_30_days',
                    'date' => $start->add($interval_30)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_180_days',
                    'date' => $start->add($interval_180)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                $start = new DateTime("$last_date_status");
                $orders_add_mails[] = [
                    'order_id' => $order_id,
                    'type_mail' => 'order_received_after_360_days',
                    'date' => $start->add($interval_360)->format('Y-m-d'),
                    'time' => $start->format('H:i'),
                ];
                continue;
            }

            if ($last_status['code'] === 'ACCEPTED_AT_PICK_UP_POINT') {
                $last_date = explode('T', $html['entity']['keep_free_until'])[0];
                $start = new DateTime("$date");
                $end = new DateTime("$last_date");
                $interval = new DateInterval('P1D');
                $days = 0;
                for ($i = $start; $i <= $end; $i->add($interval)) {
                    $days++;
                }


                if ($days > 3) {
                    $add_info = [
                        'date' => $end->format('d.m.Y'),
                        'days' => $days,
                    ];
                    send_mail($connect, $order_id, 'order_delivered', $add_info);
                    $start = new DateTime("$date");
                    $interval = new DateInterval('P2D');
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'no_payed_order_delivered_after_2_days',
                        'date' => $start->add($interval)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    continue;
                }

                if ($days <= 3) {
                    $add_info = [
                      'date' => $end->format('d.m.Y'),
                      'days' => $days,
                    ];
                    send_mail($connect, $order_id, 'order_keep', $add_info);
                }

                if ($days <= 1) {
                    $add_info = [
                        'date' => $end->format('d.m.Y'),
                        'days' => $days,
                    ];
                    send_mail($connect, $order_id, 'order_last_day', $add_info);
                }
            }
        }
    }
}

// todo: Яндекс
$orders_not_number = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`request_id`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND (`clients_address`.`delivery` = '5post(Пятерочка)' OR `clients_address`.`delivery` = 'Яндекс Доставка') AND `orders`.`number` = '-1' AND `orders`.`request_id` IS NULL");
$clientId = '2b848e9f8b134e92b117f4460431a6d5';
$apiKey = 'y0__xDPp92hCBix9BwgvervohS_SlBH1ZdeSWY_u-mmMJPChvDbTw';

if (mysqli_num_rows($orders_not_number) > 0) {
    $ch = curl_init('https://b2b-authproxy.taxi.yandex.net/api/b2b/platform/requests/info');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'client_id' => $clientId,
        'from' => date('Y-m-d\TH:i:sP', strtotime('-2 day')),
        'to' => date('Y-m-d\TH:i:sP'),
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $orders = json_decode($response, true);
    $orders = $orders['requests'];

    foreach ($orders as $order) {
        $request_id = $order['request_id'];
        $track = "LO-" . $order['courier_order_id'];

        mysqli_query($connect, "UPDATE `orders` SET `request_id`='$request_id' WHERE `track` = '$track'");
    }
}

$orders = mysqli_query($connect, "SELECT `orders`.`id`,`orders`.`track`, `orders`.`number`,`orders`.`request_id`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND (`clients_address`.`delivery` = '5post(Пятерочка)' OR `clients_address`.`delivery` = 'Яндекс Доставка') AND $add_where");

if (mysqli_num_rows($orders) > 0) {
    $date = date("Y-m-d");

    while ($order = mysqli_fetch_assoc($orders)) {
        $order_id = $order['id'];
        $track = $order['track'];
        if (strpos($order['track'], "LO-") === 0) {
            $number = $order['number'];
            $request = "request_id=" . urlencode($order['request_id']);

            $url = "https://b2b-authproxy.taxi.yandex.net/api/b2b/platform/request/info?$request";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $order_info = json_decode($response, true);

            if (!isset($order_info['code'])) {
                $status = $order_info['state']['status'];

                if ($status == 'DELIVERY_DELIVERED') {
                    // todo: Добавить здесь обработку писем
                    $last_date_status = $order_info['state']['timestamp'];
                    $start = new DateTime("$last_date_status");
                    $start = $start->add($interval_2_hours);
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_2_hours',
                        'date' => $start->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_3_days',
                        'date' => $start->add($interval_3)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_7_days',
                        'date' => $start->add($interval_7)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_10_days',
                        'date' => $start->add($interval_10)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_21_days',
                        'date' => $start->add($interval_21)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_30_days',
                        'date' => $start->add($interval_30)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_180_days',
                        'date' => $start->add($interval_180)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    $start = new DateTime("$last_date_status");
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'order_received_after_360_days',
                        'date' => $start->add($interval_360)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                    send_mail($connect, $order_id, 'order_received');
                    continue;
                }

                if ($status == 'DELIVERY_ARRIVED_PICKUP_POINT') {
                    $url = "https://b2b-authproxy.taxi.yandex.net/api/b2b/platform/request/actual_info?$request";

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Authorization: Bearer ' . $apiKey,
                        'Content-Type: application/json'
                    ]);

                    $response = curl_exec($ch);
                    curl_close($ch);
                    $date_order_info = json_decode($response, true);

                    $last_date = explode('T', $date_order_info['destination_storage_expiration_date'])[0];
                    $start = new DateTime("$date");
                    $end = new DateTime("$last_date");
                    $interval = new DateInterval('P1D');
                    $days = 0;
                    for ($i = $start; $i <= $end; $i->add($interval)) {
                        $days++;
                    }

                    if ($days > 3) {
                        $add_info = [
                            'date' => $end->format('d.m.Y'),
                            'days' => $days,
                        ];
                        send_mail($connect, $order_id, 'order_delivered', $add_info);
                        $start = new DateTime("$date");
                        $interval = new DateInterval('P2D');
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'no_payed_order_delivered_after_2_days',
                            'date' => $start->add($interval)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        continue;
                    }

                    if ($days <= 3) {
                        $add_info = [
                            'date' => $end->format('d.m.Y'),
                            'days' => $days,
                        ];
                        send_mail($connect, $order_id, 'order_keep', $add_info);
                    }
                    if ($days <= 1) {
                        $add_info = [
                            'date' => $end->format('d.m.Y'),
                            'days' => $days,
                        ];
                        send_mail($connect, $order_id, 'order_last_day', $add_info);
                    }
                }
            }
        } else {
            mysqli_query($connect, "DELETE FROM `orders_mail` WHERE `id_order` = $order_id ");
        }
    }
}

// todo: Почта России

function objectToArray($data)
{
    if (is_object($data)) {
        $data = get_object_vars($data);
    }

    if (is_array($data)) {
        return array_map('objectToArray', $data);
    }

    return $data;
}


$orders = mysqli_query($connect, "SELECT `orders`.`id`,`orders`.`track`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND `clients_address`.`delivery` = 'Почта России' AND $add_where");

if (mysqli_num_rows($orders) > 0 && ($current_time == '00' || $current_time == '12')) {
    $login = 'SGOaAaoFEHHfOg';
    $password = 'gVtoXBkLA96t';
    while ($order = mysqli_fetch_assoc($orders)) {
        $order_id = $order['id'];
        $track = $order['track'];
        try {
            $client = new SoapClient('https://tracking.russianpost.ru/rtm34?wsdl', [
                'login' => $login,
                'password' => $password,
                'exceptions' => true,
                'soap_version' => SOAP_1_2  // спецификация требует SOAP 1.2
            ]);

            $response = $client->getOperationHistory([
                'OperationHistoryRequest' => [
                    'Barcode' => $track,
                    'MessageType' => 0,
                    'Language' => 'RUS'
                ],
                'AuthorizationHeader' => [
                    'login' => $login,
                    'password' => $password
                ]
            ]);

            $orderData = ['track' => $track, 'operations' => []];

            if (!empty($response->OperationHistoryData->historyRecord)) {
                $orderData['operations'] = $response->OperationHistoryData->historyRecord;
            }

        } catch (Exception $e) {
            $orderData = ['error' => $e->getMessage(), 'track' => $track];
        }

        $operations = $orderData['operations'] ?? [];

        $cleanOperations = [];
        foreach ($operations as $op) {
            $cleanOperations[] = objectToArray($op);
        }

        $result = [
            'track' => $orderData['track'],
            'operations' => $cleanOperations
        ];
        if (count($result['operations']) > 0) {
            $next = true;
            foreach ($result['operations'] as $operation) {
                if (isset($operation['OperationParameters']) && isset($operation['OperationParameters']['OperType']) && isset($operation['OperationParameters']['OperType']['Name'])) {
                    $name = $operation['OperationParameters']['OperType']['Name'];
                    if ($name == 'Возврат' || $name == 'Вручение') {
                        file_put_contents(__DIR__ . '/../error-api.txt', "confirm-$track\n", FILE_APPEND);
                        file_put_contents(__DIR__ . '/../data_post-api.txt', print_r($operation['OperationParameters']['OperType'], true), FILE_APPEND);
                        $next = false;
                        if ($name == 'Вручение') {
                            send_mail($connect, $order_id, 'order_received');
                            $last_date_status = $operation['OperationParameters']['OperDate'];
                            $start = new DateTime("$last_date_status");
                            $start = $start->add($interval_2_hours);
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_2_hours',
                                'date' => $start->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_3_days',
                                'date' => $start->add($interval_3)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_7_days',
                                'date' => $start->add($interval_7)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_10_days',
                                'date' => $start->add($interval_10)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_21_days',
                                'date' => $start->add($interval_21)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_30_days',
                                'date' => $start->add($interval_30)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_180_days',
                                'date' => $start->add($interval_180)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                            $start = new DateTime("$last_date_status");
                            $orders_add_mails[] = [
                                'order_id' => $order_id,
                                'type_mail' => 'order_received_after_360_days',
                                'date' => $start->add($interval_360)->format('Y-m-d'),
                                'time' => $start->format('H:i'),
                            ];
                        } else {
                            send_mail($connect, $order_id, 'order_back');
                        }
                    }

                } else {
                    $next = false;
                }
            }
            if ($next) {
                $lastOperation = $result['operations'][count($result['operations']) - 1];
                $name = $lastOperation['OperationParameters']['OperType']['Name'];
                $description = $lastOperation['OperationParameters']['OperAttr']['Name'];

                if ($name == 'Возврат' || $name == 'Вручение') {
                    file_put_contents(__DIR__ . '/../error-api.txt', "confirm-$track\n", FILE_APPEND);
                    file_put_contents(__DIR__ . '/../data_post-api.txt', print_r($operation['OperationParameters']['OperType'], true), FILE_APPEND);

                    if ($name == 'Вручение') {
                        send_mail($connect, $order_id, 'order_received');
                        $last_date_status = $lastOperation['OperationParameters']['OperDate'];
                        $start = new DateTime("$last_date_status");
                        $start = $start->add($interval_2_hours);
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_2_hours',
                            'date' => $start->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_3_days',
                            'date' => $start->add($interval_3)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_7_days',
                            'date' => $start->add($interval_7)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_10_days',
                            'date' => $start->add($interval_10)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_21_days',
                            'date' => $start->add($interval_21)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_30_days',
                            'date' => $start->add($interval_30)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_180_days',
                            'date' => $start->add($interval_180)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                        $start = new DateTime("$last_date_status");
                        $orders_add_mails[] = [
                            'order_id' => $order_id,
                            'type_mail' => 'order_received_after_360_days',
                            'date' => $start->add($interval_360)->format('Y-m-d'),
                            'time' => $start->format('H:i'),
                        ];
                    } else {
                        send_mail($connect, $order_id, 'order_back');
                    }
                } else if ($name == 'Обработка' && ($description == 'Прибыло в место вручения' || $description == 'Прибыло в почтомат')) {
                    $last_date_status = $lastOperation['OperationParameters']['OperDate'];
                    send_mail($connect, $order_id, 'order_delivered');
                    file_put_contents(__DIR__ . '/../error-api.txt', "delivered-$track\n", FILE_APPEND);
                    $start = new DateTime("$last_date_status");
                    $interval = new DateInterval('P2D');
                    $orders_add_mails[] = [
                        'order_id' => $order_id,
                        'type_mail' => 'no_payed_order_delivered_after_2_days',
                        'date' => $start->add($interval)->format('Y-m-d'),
                        'time' => $start->format('H:i'),
                    ];
                } else if ($name == 'Обработка' && str_contains($description, 'Истекает срок хранения')) {
                    $last_date_status = $lastOperation['OperationParameters']['OperDate'];
                    $start = new DateTime("$last_date_status");
                    $add_info = [
                        'date' => $start->add($interval_1)->format('d.m.Y'),
                        'days' => 1,
                    ];
                    send_mail($connect, $order_id, 'order_keep', $add_info);
                    send_mail($connect, $order_id, 'order_last_day', $add_info);
                }
            }
        }
    }
}

foreach ($orders_add_mails as $mail) {
    $type = $mail['type_mail'];
    $order_id = $mail['order_id'];
    $date = $mail['date'];
    $time = $mail['time'];
    mysqli_query($connect, "UPDATE `orders_mail` SET `date`='$date', `time`='$time:00' WHERE `type` = '$type' AND `id_order` = $order_id");
}

