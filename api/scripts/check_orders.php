<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../orders/functions.php';

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
