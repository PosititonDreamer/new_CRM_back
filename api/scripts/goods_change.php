<?php
require_once __DIR__ . "/../connect.php";

$few = 14;
$few_very = 7;
$months = 3;
$other_few = 40;
$other_few_very = 20;

$date = new DateTime();
$interval = new DateInterval("P" . $months . "M");
date_sub($date, $interval);
$old_date = $date->format('Y-m-d');
$date = date('Y-m-d');

$list = mysqli_query($connect, "SELECT * FROM `expenses` WHERE `date` >= '$old_date' AND `date` <= '$date' AND `id_expense_type` = 1");

$expenses = [
    'goods' => [],
    'consumable' => [],
    'other' => [],
    'weight'=> []
];

while($item = mysqli_fetch_assoc($list)){
    $type = 'goods';
    if($item['id_expense_good_type'] == 2) {
        $type = 'consumable';
    } elseif($item['id_expense_good_type'] == 3) {
        $type = 'other';
    }
    if(isset($expenses[$type][$item['id_good']])) {
        $expenses[$type][$item['id_good']]['quantity'] += $item['quantity'];
    } else {
        $expenses[$type][$item['id_good']] = [
            "good_id" => $item['id_good'],
            "quantity" => +$item['quantity'],
        ];
    }
}

$weight = [];

foreach ($expenses['goods'] as $item) {
    $good_id = $item['good_id'];
    $quantity = $item['quantity'];
    $good = mysqli_query($connect, "SELECT * FROM `goods` WHERE `id` = $good_id");
    $good = mysqli_fetch_assoc($good);
    if($good['weight'] == 1) {
        if(isset($weight[$good['id_product']])) {
            $weight[$good['id_product']]['quantity'] += $good['quantity'] * $quantity;
        } else {
            $weight[$good['id_product']] = [
              'good_id' => $good['id_product'],
              'quantity' => $good['quantity'] * $quantity,
            ];
        }
    } else {
        $good_few = round($quantity / ($months * 30) * $few);
        $good_few_very = round($quantity / ($months * 30) * $few_very);
        mysqli_query($connect, "UPDATE `goods` SET `few`=$good_few,`few_very`=$good_few_very WHERE `id` = $good_id");
    }
}

foreach ($weight as $item) {
    $product_id = $item['good_id'];
    $quantity = $item['quantity'];
    $good = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE  `id_product` = $product_id AND `id_warehouse` = 1");
    $good = mysqli_fetch_assoc($good);
    if($good['composite'] == 1) {
        $good_id = $good['id'];
        $composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE  `id_good_weight` = $good_id");
        $composite = mysqli_fetch_assoc($composite);
        $composite_id = $composite['id'];
        $composite_list = mysqli_query($connect, "SELECT * FROM `goods_weight_composite_proportion` WHERE  `id_good_weight_composite` = $composite_id");
        while ($composite = mysqli_fetch_assoc($composite_list)) {
            $weight_id = $composite['id_good_weight'];

            if(isset($expenses['weight'][$weight_id])) {
                $expenses['weight'][$good['id']]['quantity'] += $quantity / 100 * $composite['proportion'];
            } else {
                $expenses['weight'][$weight_id] = [
                    'good_id' => $weight_id,
                    'quantity' => $quantity,
                ];
            }
        }
    } else {
        if(isset($expenses['weight'][$good['id']])) {
            $expenses['weight'][$good['id']]['quantity'] += $quantity;
        } else {
            $expenses['weight'][$good['id']] = [
                'good_id' => $good['id'],
                'quantity' => $quantity,
            ];
        }
    }
}

foreach ($expenses['weight'] as $item) {
    $good_id = $item['good_id'];
    $quantity = $item['quantity'];
    $good_few = round($quantity / ($months * 30) * $few);
    $good_few_very = round($quantity / ($months * 30) * $few_very);
    mysqli_query($connect, "UPDATE `goods_weight` SET `few`=$good_few,`few_very`=$good_few_very WHERE `id` = $good_id");
}

foreach ($expenses['consumable'] as $item) {
    $good_id = $item['good_id'];
    $quantity = $item['quantity'];
    $good_few = round($quantity / ($months * 30) * $other_few);
    $good_few_very = round($quantity / ($months * 30) * $other_few_very);
    mysqli_query($connect, "UPDATE `goods_consumable` SET `few`=$good_few,`few_very`=$good_few_very WHERE `id` = $good_id");
}

foreach ($expenses['other'] as $item) {
    $good_id = $item['good_id'];
    $quantity = $item['quantity'];
    $good_few = round($quantity / ($months * 30) * $other_few);
    $good_few_very = round($quantity / ($months * 30) * $other_few_very);
    mysqli_query($connect, "UPDATE `goods_other` SET `few`=$good_few,`few_very`=$good_few_very WHERE `id` = $good_id");
}
