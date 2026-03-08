<?php
require_once __DIR__ . "/connect.php";
require_once __DIR__ . "/workers/functions.php";
$URI = $_SERVER["REQUEST_URI"];
$METHOD = $_SERVER["REQUEST_METHOD"];
if($METHOD == 'OPTIONS'){
    http_response_code(200);
    die();
}

$URI = explode("?", $URI)[0];
if(strpos($URI, "/admin")){
    if(isset(getallheaders()["Authorization"])) {
        $auth = getallheaders()["Authorization"];
        $worker = find_user($connect, $auth);
        if(is_array($worker) && $worker['rule'] == 'Админ') {
            require_once __DIR__ . "/magazines/check.php";
            include str_replace("/api/", "", $URI);
        } else {
            $req = [
                "messages" => ["Пользователь не админ"]
            ];
            http_response_code(401);
            echo json_encode($req);
        }
    } else {
        $req = [
            "messages" => ["Пользователь не авторизован"]
        ];
        http_response_code(401);
        echo json_encode($req);
    }

} else {
    if(strpos($URI, "/blank.php") || strpos($URI, "/tilda_api.php") || strpos($URI, "api/workers") || strpos($URI, "api/migrations")){
        include str_replace("/api/", "", $URI);
    } else {
        if(isset(getallheaders()["Authorization"]) && !empty(getallheaders()["Authorization"])) {
            $auth = getallheaders()["Authorization"];
            $worker = find_user($connect, $auth);
            if(is_array($worker)) {
                require_once __DIR__ . "/magazines/check.php";
                include str_replace("/api/", "", $URI);
            }
        } else {
            $req = [
                "messages" => ["Пользователь не авторизован"]
            ];
            http_response_code(401);
            echo json_encode($req);
        }
    }
}