<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../orders/functions.php';
// todo:: CDEK
$orders = mysqli_query($connect, "SELECT `orders`.`id`,`orders`.`delivered`,`orders`.`keeped`, `orders`.`track`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND `clients_address`.`delivery` = 'CDEK' AND (`orders`.`delivered` = 0 OR `orders`.`keeped` = 0)");

if(mysqli_num_rows($orders) > 0){
    $array = array();
    $array['grant_type']    = 'client_credentials';
    $array['client_id']     = 'Y5MbFmrIprTAQ30GbDim92Yq4aBmoLxw';
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

    while($order = mysqli_fetch_assoc($orders)) {
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
        if(isset($html['entity']) && isset($html['entity']['statuses'])) {
            $last_status = $html['entity']['statuses'][0];

            if($last_status['code'] === 'DELIVERED') {
                mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
                continue;
            }

            if($last_status['code'] === 'ACCEPTED_AT_PICK_UP_POINT') {
                $last_date = explode('T', $html['entity']['keep_free_until'])[0];
                $start = new DateTime("$date");
                $end = new DateTime("$last_date");
                $interval = new DateInterval('P1D');
                $days = 0;
                for($i = $start; $i <= $end; $i->add($interval)){
                    $days++;
                }

                if($order['delivered'] == 0 && $days > 3) {
                    send_delivered_mail($connect, $order_id);
                    continue;
                }

                if($order['keeped'] == 0 && $days <= 3) {
                    send_keeped_mail($connect, $order_id);
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

$orders = mysqli_query($connect, "SELECT `orders`.`id`,`orders`.`track`,`orders`.`delivered`,`orders`.`keeped`, `orders`.`number`,`orders`.`request_id`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND (`clients_address`.`delivery` = '5post(Пятерочка)' OR `clients_address`.`delivery` = 'Яндекс Доставка') AND (`orders`.`delivered` = 0 OR `orders`.`keeped` = 0)");

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
                    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
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

                    if ($order['delivered'] == 0 && $days > 3) {
                        send_delivered_mail($connect, $order_id);
                        continue;
                    }

                    if ($order['keeped'] == 0 && $days <= 3) {
                        send_keeped_mail($connect, $order_id);
                    }
                }
            }
        } else {
            mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
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


$orders = mysqli_query($connect, "SELECT `orders`.`id`,`orders`.`delivered`,`orders`.`keeped`, `orders`.`track`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 4 AND `clients_address`.`delivery` = 'Почта России' AND (`orders`.`delivered` = 0 OR `orders`.`keeped` = 0)");

if (mysqli_num_rows($orders) > 0) {
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
        if(count($result['operations']) > 0) {
            $next = true;
            foreach ($result['operations'] as $operation) {
                $name = $operation['OperationParameters']['OperType']['Name'];
                if ($name == 'Возврат' || $name == 'Вручение') {
                    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
                    file_put_contents(__DIR__ . '/../error-api.txt', "confirm-$track");
                    $next = false;
                }
            }
            if($next) {
                $lastOperation = $result['operations'][count($result['operations'])-1];
                $name = $lastOperation['OperationParameters']['OperType']['Name'];
                $description = $lastOperation['OperationParameters']['OperAttr']['Name'];
                if ($name == 'Возврат' || $name == 'Вручение') {
                    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
                    file_put_contents(__DIR__ . '/../error-api.txt', "confirm-$track\n", FILE_APPEND);
                } else if ($order['delivered'] == 0 && $name == 'Обработка' &&  ($description == 'Прибыло в место вручения' || $description == 'Прибыло в почтомат')) {
                    send_delivered_mail($connect, $order_id);
                    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1 WHERE `id` = $order_id");
                    file_put_contents(__DIR__ . '/../error-api.txt', "delivered-$track\n", FILE_APPEND);
                } else if ($order['keeped'] == 0 && $name == 'Обработка' && str_contains($description, 'Истекает срок хранения')) {
                    send_keeped_mail($connect, $order_id);
                    file_put_contents(__DIR__ . '/../error-api.txt', "keeped-$track\n", FILE_APPEND);
                    mysqli_query($connect, "UPDATE `orders` SET `delivered`= 1, `keeped`= 1 WHERE `id` = $order_id");
                }
            }
        }
    }
}


