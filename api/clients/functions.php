<?php
function generate_request($full_name, $phone, $email) {
    $full_name_array = explode(" ", str_replace('.', '', $full_name));
    $request = [];
    foreach ($full_name_array as $full_name) {
        $request[] = "`full_name` LIKE '%$full_name%' AND (`phone`= '$phone' OR `email`= '$email')";
    }
    $request = "(" . join(") OR (", $request) . ")";
    return "SELECT * FROM `clients` WHERE (($request) OR (`phone` = '$phone' AND `email` = '$email')) AND `phone` IS NOT NULL AND `email` != ''";
}