<?php
require_once __DIR__ . "/../../../connect.php";
require_once __DIR__ . "/../../../helpers/check_data.php";
$messages = check_data(['id', 'product', 'warehouse', 'balance', 'few', 'few_very', 'composite'], $_POST);

require_once __DIR__ . "/../../../helpers/check_messages.php";

$id = $_POST['id'];
$product = $_POST['product'];
$warehouse = $_POST['warehouse'];
$balance = $_POST['balance'];
$few = $_POST['few'];
$few_very = $_POST['few_very'];
$composite = $_POST['composite'] == 'true' ? 1 : 0;

if ($composite) {
    $messages = check_data(['composite_list'], $_POST);
    require_once __DIR__ . "/../../../helpers/check_messages.php";
}

$current_balance = 0;

$composite_list = $_POST['composite_list'];
$check = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
if (mysqli_num_rows($check) > 0) {
    $check = mysqli_fetch_assoc($check);
    if ($check['id'] == $id) {
        $current_balance = $check['balance'];
        mysqli_query($connect, "UPDATE `goods_weight` SET `id_product`=$product,`balance`=$balance,`few`=$few,`few_very`=$few_very,`composite`=$composite WHERE `id` = '$id'");
        $req = [
            "messages" => ["Весовой товар успешно изменен"],
            "good_weight" => [
                "id" => $id,
                "product" => $product,
                "warehouse" => $warehouse,
                "balance" => $balance,
                "few" => $few,
                "few_very" => $few_very,
                "composite" => $composite,
            ]
        ];
        mysqli_query($connect, "UPDATE `goods` SET `weight` = 1 WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
        if ($composite) {
            $last_id_composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $id");
            if (mysqli_num_rows($last_id_composite) > 0) {
                $last_id_composite = mysqli_fetch_assoc($last_id_composite)['id'];
            } else {
                mysqli_query($connect, "INSERT INTO `goods_weight_composite`(`id_good_weight`) VALUES ($id)");
                $last_id_composite = mysqli_insert_id($connect);
            }
            mysqli_query($connect, "DELETE FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $last_id_composite");
            $req['good_weight']['composite_list'] = [];
            $composite_goods = json_decode($composite_list, true);
            foreach ($composite_goods as $composite_good) {
                $weight_composite = $composite_good['weight_composite'];
                $proportion = $composite_good['proportion'];
                mysqli_query($connect, "INSERT INTO `goods_weight_composite_proportion`(`id_good_weight_composite`, `id_good_weight`, `proportion`) VALUES ($last_id_composite ,$weight_composite, $proportion)");
                $last_id_proportion = mysqli_insert_id($connect);
                $req['good_weight']['composite_list'][] = [
                    "id" => $last_id_proportion,
                    "composite_id" => $last_id_composite,
                    "weight" => $weight_composite,
                    "proportion" => $proportion
                ];
            }
        } else {
            $last_id_composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $id");
            if (mysqli_num_rows($last_id_composite) > 0) {
                $last_id_composite = mysqli_fetch_assoc($last_id_composite)['id'];
                mysqli_query($connect, "DELETE FROM `goods_weight_composite` WHERE `id` = $last_id_composite");
                mysqli_query($connect, "DELETE FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $last_id_composite");
            }
        }
        http_response_code(200);
        echo json_encode($req);
    } else {
        $req = [
            "messages" => ['Такой весовой товар уже есть']
        ];
        http_response_code(400);
        echo json_encode($req);
    }
} else {
    $check = mysqli_query($connect, "SELECT * FROM `goods_weight` WHERE `id` = '$id'");
    $check = mysqli_fetch_assoc($check);
    $current_balance = $check['balance'];
    mysqli_query($connect, "UPDATE `goods_weight` SET `id_product`=$product,`balance`=$balance,`few`=$few,`few_very`=$few_very,`composite`=$composite WHERE `id` = '$id'");
    $req = [
        "messages" => ["Весовой товар успешно изменен"],
        "good_weight" => [
            "id" => $id,
            "product" => $product,
            "warehouse" => $warehouse,
            "balance" => $balance,
            "few" => $few,
            "few_very" => $few_very,
            "composite" => $composite,
        ]
    ];
    if ($check['product'] == $product) {
        mysqli_query($connect, "UPDATE `goods` SET `weight`= 1 WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
    } else {
        $check_product = $check['product'];
        mysqli_query($connect, "UPDATE `goods` SET `weight`= 0 WHERE `id_product` = $check_product AND `id_warehouse` = $warehous");
        mysqli_query($connect, "UPDATE `goods` SET `weight`= 1 WHERE `id_product` = $product AND `id_warehouse` = $warehouse");
    }
    if ($composite) {
        $last_id_composite = mysqli_query($connect, "SELECT * FROM `goods_weight_composite` WHERE `id_good_weight` = $id");
        if (mysqli_num_rows($last_id_composite) > 0) {
            $last_id_composite = mysqli_fetch_assoc($last_id_composite)['id'];
        } else {
            mysqli_query($connect, "INSERT INTO `goods_weight_composite`(`id_good_weight`) VALUES ($last_id)");
            $last_id_composite = mysqli_insert_id($connect);
        }
        mysqli_query($connect, "DELETE FROM `goods_weight_composite_proportion` WHERE `id_good_weight_composite` = $last_id_composite");
        $req['good_weight']['composite_list'] = [];
        $composite_goods = json_decode($composite_list, true);
        foreach ($composite_goods as $composite_good) {
            $weight_composite = $composite_good['weight_composite'];
            $proportion = $composite_good['proportion'];
            mysqli_query($connect, "INSERT INTO `goods_weight_composite_proportion`(`id_good_weight_composite`, `id_good_weight`, `proportion`) VALUES ($last_id_composite ,$weight_composite, $proportion)");
            $last_id_proportion = mysqli_insert_id($connect);
            $req['good_weight']['composite_list'][] = [
                "id" => $last_id_proportion,
                "composite_id" => $last_id_composite,
                "weight" => $weight_composite,
                "proportion" => $proportion
            ];
        }
    }
    http_response_code(200);
    echo json_encode($req);
}

if($current_balance !== $balance && $composite == 0) {
    if($current_balance < $balance) {
        $type = 'green';
        $count = $balance - $current_balance;
    } else {
        $type = 'red';
        $count = $current_balance - $balance;
    }
    $date = date("Y-m-d");
    $check_magazine = mysqli_query($connect, "SELECT * FROM `magazines` WHERE `id_warehouse` = $warehouse AND `type` = 'hand' AND `date` = '$date'");
    if (mysqli_num_rows($check_magazine) > 0) {
        $check_magazine = mysqli_fetch_assoc($check_magazine);
        $last_id = $check_magazine['id'];
        $check_magazine_good = mysqli_query($connect, "SELECT * FROM `magazines_good` WHERE `id_magazine` = $last_id AND `id_good` = $id AND `type` = 'weight'");
        if (mysqli_num_rows($check_magazine_good) > 0) {
            $check_magazine_good = mysqli_fetch_assoc($check_magazine_good);
            $magazine_good_id = $check_magazine_good['id'];
            $magazine_balance = $check_magazine_good['balance'];
            if($count > $magazine_balance) {
                $count = $count - $magazine_balance;
            } else {
                $count = $magazine_balance - $count;
            }
            if($count == 0) {
                mysqli_query($connect, "DELETE FROM `magazines_good` WHERE `id` = $magazine_good_id");
            } else {
                mysqli_query($connect, "UPDATE `magazines_good` SET `balance`= $count, `type_view` = '$type' WHERE `id` = $magazine_good_id");
            }

        } else {
            mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'weight',$count,'$type')");
        }
    } else {
        mysqli_query($connect, "INSERT INTO `magazines`(`date`, `type`, `id_warehouse`) VALUES ('$date','hand','$warehouse')");
        $last_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `magazines_good`(`id_magazine`, `id_good`, `type`, `balance`, `type_view`) VALUES ($last_id,$id,'weight',$count,'$type')");
    }
}