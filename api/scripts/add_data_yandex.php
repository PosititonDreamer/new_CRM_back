<?php
require_once __DIR__ . '/../connect.php';

$orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`,`orders`.`id_order_status`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`orders`.`id_order_status` = 3 OR `orders`.`id_order_status` = 6) AND (`clients_address`.`delivery` = '5post(Пятерочка)' OR `clients_address`.`delivery` = 'Яндекс Доставка')");

$apiKey = 'y0__xDPp92hCBix9BwgvervohS_SlBH1ZdeSWY_u-mmMJPChvDbTw';
$clientId = '2b848e9f8b134e92b117f4460431a6d5';

if(mysqli_num_rows($orders) > 0) {
    while($order = mysqli_fetch_assoc($orders)) {
        $order_id = $order['id'];
        $number = $order['number'];
        $order_status = $order['id_order_status'];

        $url = 'https://b2b-authproxy.taxi.yandex.net/api/b2b/platform/request/info?request_code=' . urlencode($number);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $order_info = json_decode($response, true);

        if(isset($order_info['code']) && $order_info['code'] == 'customer_order_not_found') {
            continue;
        } else {
            $request_id = $order_info['request_id'];
            $track = "LO-" . $order_info['courier_order_id'];

            $ch = curl_init('https://b2b-authproxy.taxi.yandex.net/api/b2b/platform/request/generate-labels');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'client_id' => $clientId,
                'request_ids' => [$request_id], // ← МАССИВ, а не строка
                'generate_type' => 'one',
                'label_format' => 'pdf', // ← Добавьте этот параметр
                'language' => 'ru'
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json' // ← application/json, а НЕ application/pdf
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            file_put_contents(__DIR__ . "/../../files/$order_id.pdf", $response);


            $date = date("Y-m-d");
            $time = date("H:i:s");
            if($order_status == 6) {
                mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=7, `track`='$track' WHERE `id` = $order_id");
                mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,7,'$date', '$time')");
            } else {
                mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=1, `track`='$track' WHERE `id` = $order_id");
                mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,1,'$date', '$time')");
            }
        }
    }
}
