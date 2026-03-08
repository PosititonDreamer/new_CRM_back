<?php
$connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_crm_system');
$old_connect = mysqli_connect('localhost', 'u2996058_default', 'NztQarQSu85H1T6d', 'u2996058_default');

$products = mysqli_query($old_connect, "SELECT * FROM products");

while ($product = mysqli_fetch_assoc($products)) {
    $sort = 100;
    $last = mysqli_query($connect, "SELECT `sort` FROM `products` ORDER BY `products`.`sort` DESC LIMIT 1");
    if (mysqli_num_rows($last) > 0) {
        $last = mysqli_fetch_assoc($last);
        $sort = floor($last["sort"] / 100) * 100 + 100;
    }
    print_r($product);
    $title = $product['title'];
    mysqli_query($connect, "INSERT INTO `products`(`id_measure_unit`, `title`, `show_title`, `sort`, `hidden`) VALUES (0,'$title','',$sort,0)");
}