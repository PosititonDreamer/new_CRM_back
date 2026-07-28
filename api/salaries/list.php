<?php
require_once __DIR__ . "/../connect.php";
require_once __DIR__ . "/../helpers/check_data.php";
$messages = check_data(['worker', 'date_start', 'date_end'], $_POST);

require_once __DIR__ . "/../helpers/check_messages.php";

$worker_id = $_POST['worker'];

$worker = mysqli_query($connect, "SELECT * FROM `workers` WHERE `id` = $worker_id");
$worker = mysqli_fetch_assoc($worker);

$rule = $worker['id_worker_rule'];
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];

if ($rule == 2) {
    $penalties = mysqli_query($connect, "SELECT * FROM `salaries_penalty` WHERE `id_worker` = $worker_id AND `date` >= '$date_start' AND `date` <= '$date_end'");
    $salaries = mysqli_query($connect, "SELECT * FROM `salaries_assembler` WHERE `id_worker` = $worker_id AND `date` >= '$date_start' AND `date` <= '$date_end'");
    $salaries_supply = mysqli_query($connect, "SELECT * FROM `salaries_assembler_supply` WHERE `id_worker` = $worker_id AND `date` >= '$date_start' AND `date` <= '$date_end'");

    $new_penalties = [];
    $new_salaries = [];
    $new_salaries_supply = [];

    while ($penalty = mysqli_fetch_assoc($penalties)) {
        $new_penalties[] = [
            'id' => $penalty['id'],
            "description" => $penalty['description'],
            "date" => $penalty['date'],
            "sum" => $penalty['sum'],
            "ready" => $penalty['ready'] == 1,
        ];
    }

    $salaries_length = mysqli_query($connect, "SELECT * FROM `salaries_assembler` WHERE `id_worker` = $worker_id AND `ready` = 0");
    $salaries_length = mysqli_num_rows($salaries_length);
    $salaries_supply_length = mysqli_query($connect, "SELECT * FROM `salaries_assembler_supply` WHERE `id_worker` = $worker_id AND `ready` = 0");
    $salaries_supply_length = mysqli_num_rows($salaries_supply_length);
    $penalties_length = mysqli_query($connect, "SELECT * FROM `salaries_penalty` WHERE `id_worker` = $worker_id AND `ready` = 0");
    $penalties_length = mysqli_num_rows($penalties_length);

    while ($salary = mysqli_fetch_assoc($salaries)) {
        $order_id = $salary['id_order'];
        $order = mysqli_query($connect, "SELECT * FROM `orders` WHERE `id` = $order_id");

        if (mysqli_num_rows($order) == 0) {
            continue;
        }
        $order = mysqli_fetch_assoc($order);
        $client_id = $order['id_client'];

        $client = mysqli_query($connect, "SELECT * FROM `clients` WHERE `id` = $client_id");
        $client = mysqli_fetch_assoc($client);

        $goods = mysqli_query($connect, "SELECT orders_good.`id_good`, orders_good.`quantity`, goods.price FROM `orders_good` JOIN goods ON goods.id = orders_good.id_good WHERE orders_good.id_order = $order_id AND orders_good.id_order_good_type = 1");

        $price = 0;

        while ($good = mysqli_fetch_assoc($goods)) {
            $price += $good['price'] * $good['quantity'];
        }

        $new_salaries[] = [
            "id" => $salary['id'],
            "track" => $order['track'],
            "full_name" => $client['full_name'],
            "date" => $order['date'],
            "worker" => $salary['id_worker'],
            "send" => $salary['send'] == 1,
            "ready" => $salary['ready'] == 1,
            "price" => $price,
        ];
    }

    while ($salary = mysqli_fetch_assoc($salaries_supply)) {
        $supply_id = $salary['id_supply'];
        $supply_list = mysqli_query($connect, "SELECT * FROM `supplies_list` WHERE `id_supply` = $supply_id");
        $goods_list = [];
        $price = 0;

        while ($supply = mysqli_fetch_assoc($supply_list)) {
            $supply_warehouse_connection = $supply['id_supply_warehouse_connection'];
            $quantity = $supply['quantity'];

            $connection_good = mysqli_query($connect, "SELECT * FROM `supplies_warehouse_connection` WHERE `ID` = $supply_warehouse_connection");
            $connection_good = mysqli_fetch_assoc($connection_good);
            if ($connection_good['good_type'] == 'good') {
                $good_id = $connection_good['id_good_give'];
                $good = mysqli_query($connect, "SELECT `goods`.`quantity`,`goods`.`price`, `products`.`title`, `products`.`show_title`, `measure_units`.`title` AS `measure` FROM `goods` JOIN `products` ON `products`.`id` = `goods`.`id_product` JOIN `measure_units` ON `measure_units`.`id` = `products`.`id_measure_unit` WHERE `goods`.id = $good_id");
                $good = mysqli_fetch_assoc($good);
                $price += $quantity * $good['price'];

                $goods_list[] = [
                    "price" => $quantity * $good['price'],
                    "title" => ($good['show_title'] ?? $good['title']) . ", " . $good['quantity'] . " " . $good['measure'] . " - " . $quantity . " шт.",
                ];
            }
        }

        $new_salaries_supply[] = [
            "id" => $salary['id'],
            "date" => $salary['date'],
            "ready" => $salary['ready'] == 1,
            "price" => $price,
            "list" => $goods_list,
        ];
    }

    $req = [
        "messages" => ['Список зарплат сборщика успешно получен'],
        "salaries" => $new_salaries,
        "salaries_supply" => $new_salaries_supply,
        "penalties" => $new_penalties,
        "salaries_length" => $salaries_length,
        "penalties_length" => $penalties_length,
        "salaries_supply_length" => $salaries_supply_length,
        "rule" => $rule,
        "worker" => $worker_id,
        "date_start" => $date_start,
        "date_end" => $date_end
    ];
    http_response_code(200);
    echo json_encode($req);
    die();
}

if ($rule == 3) {
    $salaries = mysqli_query($connect, "SELECT * FROM `salaries_operator` WHERE `id_worker` = $worker_id AND `date_start` >= '$date_start' AND `date_start` <= '$date_end' ORDER BY `salaries_operator`.`date_end` DESC");
    $penalties = mysqli_query($connect, "SELECT * FROM `salaries_penalty` WHERE `id_worker` = $worker_id AND `date` >= '$date_start' AND `date` <= '$date_end'");
    $new_salaries = [];
    $new_penalties = [];

    $penalties_length = mysqli_query($connect, "SELECT * FROM `salaries_penalty` WHERE `id_worker` = $worker_id AND `ready` = 0");
    $penalties_length = mysqli_num_rows($penalties_length);

    while ($salary = mysqli_fetch_assoc($salaries)) {
        $new_salaries[] = [
            'id' => $salary['id'],
            "date_start" => $salary['date_start'],
            "date_end" => $salary['date_end'],
            "description" => $salary['description'],
            "sum" => $salary['sum'],
        ];
    }

    while ($penalty = mysqli_fetch_assoc($penalties)) {
        $new_penalties[] = [
            'id' => $penalty['id'],
            "description" => $penalty['description'],
            "date" => $penalty['date'],
            "sum" => $penalty['sum'],
            "ready" => $penalty['ready'] == 1,
        ];
    }

    $req = [
        "messages" => ['Список зарплат оператора успешно получен'],
        "salaries" => $new_salaries,
        "penalties" => $new_penalties,
        "rule" => $rule,
        "worker" => $worker_id,
        "date_start" => $date_start,
        "date_end" => $date_end,
        "penalties_length" => $penalties_length,
    ];
    http_response_code(200);
    echo json_encode($req);
    die();
}