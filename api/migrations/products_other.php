<?php
$connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_crm_system');
$old_connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_default');

$products = mysqli_query($old_connect, "SELECT * FROM products_others");

while ($product = mysqli_fetch_assoc($products)) {
    $id = $product['product_id'];
    $count = $product['count'];
    $title = $product['title'];

    $product_title = mysqli_query($old_connect, "SELECT * FROM products WHERE `id` = '$id'");
    $product_title = mysqli_fetch_assoc($product_title);
    $product_title = $product_title['title'];

    $new_product = mysqli_query($connect, "SELECT * FROM products WHERE title = '$product_title'");
    $new_product = mysqli_fetch_assoc($new_product);
    $new_product_id = $new_product['id'];
    $new_packing = mysqli_query($connect, "SELECT * FROM products_packing WHERE `id_product` = '$new_product_id' AND `packing` = '$count'");


    if(mysqli_num_rows($new_packing) > 0){
        $new_packing = mysqli_fetch_assoc($new_packing);
        $new_packing_id = $new_packing['id'];
        mysqli_query($connect, "INSERT INTO `products_other`(`id_packing`, `title`) VALUES ($new_packing_id,'$title')");
    } else {
        mysqli_query($connect, "INSERT INTO `products_packing`(`id_product`, `packing`) VALUES ($new_product_id,$count)");
        $new_packing_id = mysqli_insert_id($connect);
        mysqli_query($connect, "INSERT INTO `products_other`(`id_packing`, `title`) VALUES ($new_packing_id,'$title')");
    }
}