<?php
require_once __DIR__ . "/../connect.php";
$date = date("Y-m-d");
$check = mysqli_query($connect, "SELECT * FROM magazines WHERE `date` = '$date' AND `type` = 'everyday'");
if (mysqli_num_rows($check) == 0) {
    file_put_contents(__DIR__ . "/magazines/info.txt", 'Данные сохранены ' . date('Y-m-d'), FILE_APPEND);
    require_once __DIR__ . "/create.php";
}