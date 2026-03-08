<?php
function find_other_product_title($connect, $title)
{
    $product = mysqli_query($connect, "SELECT * FROM `products_other` WHERE `title` = '$title'");
    if(mysqli_num_rows($product) > 0) {
        return mysqli_fetch_assoc($product);
    }
    return null;
}

function create_other_product($connect, $title)
{
    mysqli_query($connect, "INSERT INTO `products_other`(`title`) VALUES ('$title')");
    $last_id = mysqli_insert_id($connect);
    return [
        'messages' => ['Продукт с кривым названием успешно добавлен'],
        'product_other' => [
            'id' => $last_id,
            'title' => $title,
            'packing' => null
        ]
    ];
}