<?php
require_once __DIR__ . '/../connect.php';

$orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE `orders`.`id_order_status` = 3 AND `clients_address`.`delivery` = 'CDEK'");

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
    $time = date("H:i:s");

    while($order = mysqli_fetch_assoc($orders)) {
        $order_id = $order['id'];
        $delivery_id = $order['number'];
        $ch = curl_init('https://api.cdek.ru/v2/orders/?im_number=' . $delivery_id);
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
        if(isset($html['entity']) && isset($html['entity']['cdek_number'])) {
            $track = $html['entity']['cdek_number'];
            mysqli_query($connect, "UPDATE `orders` SET `track`='$track', `id_order_status` = 1 WHERE `id` = $order_id");
            mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($order_id,1,'$date', '$time')");
            // todo: добавить функцию отправки в тг обработанная инфа о заказе

            // todo: добавить функцию отправки в тг количетсво заказов

//            $uuid = $html['entity']['uuid'];
//
//            $postData = [
//                "orders" => [
//                    ["order_uuid" => $uuid]
//                ],
//                "format" => "A6",
//            ];
//
//            $ch = curl_init('https://api.cdek.ru/v2/print/barcodes');
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
//            curl_setopt($ch, CURLOPT_HTTPHEADER, [
//                'Content-Type: application/json',
//                'Authorization: Bearer ' . $token
//            ]);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//            $response = curl_exec($ch);
//
//            $uuid = json_decode($response, true)['entity']['uuid'];
//            sleep(1);
//
//            $ch = curl_init("https://api.cdek.ru/v2/print/barcodes/$uuid.pdf");
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Authorization: Bearer ' . $token
//            ));
//            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_HEADER, false);
//
//            $response = curl_exec($ch);
//            curl_close($ch);
//            if(!empty($response)) {
//                file_put_contents(__DIR__ . "/../../files/$order_id.pdf", $response);
//            }
        }
    }
}
