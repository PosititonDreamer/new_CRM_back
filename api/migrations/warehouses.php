<?php
$connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_crm_system');
$old_connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_default');

$old_warehouses = mysqli_query($old_connect, 'SELECT * FROM `warehouse`');

$warehouses = [];

while ($warehouse = mysqli_fetch_assoc($old_warehouses)) {
    $warehouses[] = [
        'id' => $warehouse['id'],
        'title' => $warehouse['name'],
        'description' => $warehouse['description'],
        'type' => $warehouse['rule'] == 'commodity' ? 1 : 2,
        'goods' => []
    ];
}

$old_accounting = mysqli_query($old_connect, 'SELECT * FROM `accounting`');

$accounting = [];
while ($account = mysqli_fetch_assoc($old_accounting)) {
    $id = $account['product_id'];
    $product = mysqli_query($old_connect, 'SELECT * FROM `products` WHERE `id` = ' . $id);
    $product = mysqli_fetch_assoc($product);

    $check_weight = $account['weight_id'] > 0;
    $new_account = [
        'product' => $product['title'],
        'warehouse' => $account['warehouse_id'],
        'article' => $account['article'],
        'quantity' => $account['count'],
        'balance' => $account['amount'],
        'weight' => $check_weight,
        'consumable_list' => []
    ];

    $consumable_list = mysqli_query($old_connect, 'SELECT * FROM `accounting_relationship` WHERE `account_id` = ' . $account['id']);
    if (mysqli_num_rows($consumable_list) > 0) {
        while ($consumable = mysqli_fetch_assoc($consumable_list)) {
            $consumable_item = mysqli_query($old_connect, 'SELECT * FROM `consumable_accounting` WHERE `id` = ' . $consumable['consumable_id']);
            $consumable_item = mysqli_fetch_assoc($consumable_item);
            $new_account['consumable_list'][] = [
                'title' => $consumable_item['title'],
                'balance' => $consumable_item['amount'],
            ];
        }
    }

    $accounting[] = $new_account;
}


$weight_list = mysqli_query($old_connect, "SELECT * FROM `weight_accounting` ORDER BY `weight_accounting`.`composite` ASC");
$weights = [];

while ($weight = mysqli_fetch_assoc($weight_list)) {
    $id = $weight['product_id'];
    $product = mysqli_query($old_connect, 'SELECT * FROM `products` WHERE `id` = ' . $id);
    $product = mysqli_fetch_assoc($product);
    $check_composite = !is_null($weight['composite']) && $weight['composite'] > 0;

    $new_account_weight = [
        'balance' => $weight['amount'],
        'composite' => $check_composite,
        'warehouse' => $weight['warehouse_id'],
        'product' => $product['title'],
    ];

    if ($check_composite) {
        $new_account_weight['composite_list'] = [];
        $composite = mysqli_query($old_connect, 'SELECT * FROM `accounting_composite` WHERE `accounting_id` = ' . $weight['id']);
        while ($compos = mysqli_fetch_assoc($composite)) {
            $composite_product = mysqli_query($old_connect, 'SELECT * FROM `weight_accounting` WHERE `id` = ' . $compos['product_id']);
            $composite_product = mysqli_fetch_assoc($composite_product);
            $product_title = mysqli_query($old_connect, 'SELECT * FROM `products` WHERE `id` = ' . $composite_product['product_id']);
            $product_title = mysqli_fetch_assoc($product_title)['title'];
            $new_account_weight['composite_list'][] = [
                'proportion' => $compos['procent'],
                'product' => $product_title,
            ];
        }
    }
    $weights[] = $new_account_weight;
}

$old_individual = mysqli_query($old_connect, "SELECT * FROM `accounting_individual` WHERE `type` = 'box' ORDER BY `accounting_individual`.`sort` ASC");
$individual = [];
while ($item = mysqli_fetch_assoc($old_individual)) {
    $individual[] = [
        'warehouse' => $item['warehouse_id'],
        'title' => $item['title'],
        'balance' => $item['amount'],
        'type' => 2,
    ];
}

for ($i = 0; $i < count($warehouses); $i++) {
    foreach ($accounting as $account) {
        if ($warehouses[$i]['id'] == $account['warehouse']) {
            $warehouses[$i]['goods'][] = $account;
        }
    }

    $old_individual = mysqli_query($old_connect, "SELECT * FROM `accounting_individual` WHERE `type` = 'magnet' AND `warehouse_id` = " . $warehouses[$i]['id']);
    if(mysqli_num_rows($old_individual) > 0){
        while($item = mysqli_fetch_assoc($old_individual)){
            $warehouses[$i]['other'][] = [
                'title' => $item['title'],
                'balance' => $item['amount'],
                'type' => 1,
            ];
        }
    } else {
        $warehouses[$i]['other'][] = [
            'title' => 'Фирменный магнит',
            'balance' => 0,
            'type' => 1,
        ];
    }

    foreach ($individual as $account) {
        if ($warehouses[$i]['id'] == $account['warehouse']) {
            $warehouses[$i]['other'][] = $account;
        }
    }

    foreach ($weights as $weight) {
        if ($warehouses[$i]['id'] == $weight['warehouse']) {
            $warehouses[$i]['weight'][] = $weight;
        }
    }
}


foreach ($warehouses as $warehouse) {
    $warehouse_title = $warehouse['title'];
    $warehouse_type = $warehouse['type'];
    $warehouse_description = $warehouse['description'];
    mysqli_query($connect, "INSERT INTO `warehouses`(`id_type`, `title`, `description`, `hidden`) VALUES ($warehouse_type,'$warehouse_title','$warehouse_description',0)");
    $warehouse_id = mysqli_insert_id($connect);

    foreach ($warehouse['goods'] as $good) {
        $good_product = $good['product'];
        $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` = '$good_product'");
        $product_id = mysqli_fetch_assoc($product)['id'];
        $article = $good['article'];
        $quantity = $good['quantity'];
        $balance = $good['balance'];
        $check_weight = $good['weight'] ? 1 : 0;
        mysqli_query($connect, "INSERT INTO `goods`(`id_product`, `id_warehouse`, `quantity`, `balance`, `article`, `few`, `few_very`, `weight`, `hidden`) VALUES ($product_id, $warehouse_id, $quantity, $balance, '$article', 0, 0, $check_weight, 0)");
        $good_id = mysqli_insert_id($connect);

        foreach ($good['consumable_list'] as $consumable) {
            $title = $consumable['title'];
            $balance = $consumable['balance'];
            $check = mysqli_query($connect, "SELECT * FROM `goods_consumable` WHERE `title` = '$title' AND `id_warehouse` = $warehouse_id");
            if(mysqli_num_rows($check) > 0){
                $consumable_id = mysqli_fetch_assoc($check)['id'];
                mysqli_query($connect, "INSERT INTO `goods_consumable_binding`(`id_good_consumable`, `id_good`) VALUES ($consumable_id,$good_id)");
            } else {
                mysqli_query($connect, "INSERT INTO `goods_consumable`(`id_warehouse`, `title`, `balance`, `few`, `few_very`, `sort`, `hidden`) VALUES ($warehouse_id,'$title',$balance,0,0,0,0)");
                $consumable_id = mysqli_insert_id($connect);
                mysqli_query($connect, "INSERT INTO `goods_consumable_binding`(`id_good_consumable`, `id_good`) VALUES ($consumable_id,$good_id)");
            }
        }
    }

    foreach ($warehouse['weight'] as $weight) {
        $weight_product = $weight['product'];
        $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` = '$weight_product'");
        $product_id = mysqli_fetch_assoc($product)['id'];
        $balance = $weight['balance'];
        $check_composite = $weight['composite'] ? 1 : 0;
        mysqli_query($connect, "INSERT INTO `goods_weight`(`id_product`, `id_warehouse`, `balance`, `few`, `few_very`, `composite`) VALUES ($product_id,$warehouse_id,$balance,0,0,$check_composite)");
        $weight_id = mysqli_insert_id($connect);

        if($check_composite == 1){
            mysqli_query($connect, "INSERT INTO `goods_weight_composite`(`id_good_weight`) VALUES ($weight_id)");
            $composite_id = mysqli_insert_id($connect);

            foreach ($weight['composite_list'] as $composite) {
                $composite_product = $composite['product'];
                $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` = '$composite_product'");
                $product_id = mysqli_fetch_assoc($product)['id'];
                $weight_id = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product_id AND `id_warehouse` = $warehouse_id");
                $weight_id = mysqli_fetch_assoc($weight_id)['id'];
                $proportion = $composite['proportion'];
                mysqli_query($connect, "INSERT INTO `goods_weight_composite_proportion`(`id_good_weight_composite`, `id_good_weight`, `proportion`) VALUES ($composite_id,$weight_id,$proportion)");
            }
        }
    }

    foreach ($warehouse['other'] as $other) {
        $title = $other['title'];
        $balance = $other['balance'];
        $type = $other['type'];
        mysqli_query($connect, "INSERT INTO `goods_other`(`id_warehouse`, `id_good_other_type`, `title`, `balance`, `few`, `few_very`, `sort`, `hidden`) VALUES ($warehouse_id,$type,'$title','$balance',0,0,0,0)");
    }
}
mysqli_query($connect, "UPDATE `goods_consumable` SET `sort`=`id` * 100");
mysqli_query($connect, "UPDATE `goods_other` SET `sort`=`id` * 100");