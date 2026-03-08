<?php
function find_product_title($connect, $title)
{
    $product = mysqli_query($connect, "SELECT * FROM `products` WHERE `title` = '$title'");
    if (mysqli_num_rows($product) > 0) {
        return mysqli_fetch_assoc($product);
    }
    return null;
}