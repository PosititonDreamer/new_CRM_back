<?php 
    date_default_timezone_set('Asia/Yekaterinburg');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Credentials: true');
    $connect = mysqli_connect('localhost', 'root', '', 'crm_system');
