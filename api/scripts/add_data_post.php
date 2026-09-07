<?php
require_once __DIR__ . '/../connect.php';
$orders = mysqli_query($connect, "SELECT `orders`.`id`, `orders`.`number`,`orders`.`id_order_status`, `clients_address`.`delivery` FROM `orders` JOIN `clients_address` ON `clients_address`.`id` = `orders`.`id_client_address` WHERE (`orders`.`id_order_status` = 3 OR `orders`.`id_order_status` = 6) AND `clients_address`.`delivery` = 'Почта России'");
if (mysqli_num_rows($orders) > 0) {
    $apiKey = 'C10l5_GoDqENedHsjmR92n1M4NSLyK80';
    $auth = 'ODkwMjgzMTY4Njg6dXJhbG1obXJzaG9w';
    while ($order = mysqli_fetch_array($orders)) {
        $id = $order['id'];
        $order_status = $order['id_order_status'];
        $number = $order['number'];
        $url = "https://otpravka-api.pochta.ru/1.0/backlog/search?query=$number";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: AccessToken $apiKey",
            "X-User-Authorization: Basic $auth",
            'Content-Type: application/json;charset=UTF-8'
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $info_order = $response[0];
        $request_id = $info_order['id'];
        $track = $info_order['barcode'];

        mysqli_query($connect, "UPDATE `orders` SET `request_id`='$request_id', `track` = '$track' WHERE `id` = $id");
        $date = date("Y-m-d");
        $time = date("H:i:s");
        if($order_status == 6) {
            mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=7, `track`='$track' WHERE `id` = $id");
            mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,7,'$date', '$time')");
        } else {
            mysqli_query($connect, "UPDATE `orders` SET `id_order_status`=1, `track`='$track' WHERE `id` = $id");
            mysqli_query($connect, "INSERT INTO `orders_process`(`id_order`, `id_order_status`, `date`, `time`) VALUES ($id,1,'$date', '$time')");
        }

        $ch = curl_init("https://otpravka-api.pochta.ru/1.0/user/shipment?sending-date=$date&use-online-balance=true");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            $request_id
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: AccessToken $apiKey",
            "X-User-Authorization: Basic $auth",
            'Content-Type: application/json;charset=UTF-8'
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $url = "https://otpravka-api.pochta.ru/1.0/forms/$request_id/f7pdf";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: AccessToken $apiKey",
            "X-User-Authorization: Basic $auth",
            'Content-Type: application/json;charset=UTF-8'
        ]);

        $response = curl_exec($ch);

        print_r($response);

        file_put_contents(__DIR__ . "/../../files/$id.pdf", $response);
        curl_close($ch);
    }
}
