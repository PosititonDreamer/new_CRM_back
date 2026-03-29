<?php 
    function sendMessage($getQuery) {
        $token = "8309707726:AAGwjEWESwPgWMmjaDVmpXMSZOxOVtyv_8g";
        
        $ch = curl_init("https://api.telegram.org/bot". $token ."/sendMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    function sendDocument($getQuery) {
        $token = "8309707726:AAGwjEWESwPgWMmjaDVmpXMSZOxOVtyv_8g";

        $ch = curl_init('https://api.telegram.org/bot'. $token .'/sendDocument');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $getQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    function deleteMessage($getQuery) {
        $token = "8309707726:AAGwjEWESwPgWMmjaDVmpXMSZOxOVtyv_8g";
        
        $ch = curl_init("https://api.telegram.org/bot". $token ."/deleteMessage?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    function checkFollow($getQuery) {
        $token = "8309707726:AAGwjEWESwPgWMmjaDVmpXMSZOxOVtyv_8g";
        
        $ch = curl_init("https://api.telegram.org/bot". $token ."/getChatMember?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }