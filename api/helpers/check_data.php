<?php
function check_data($params, $data)
{
    $messages = [];

    foreach ($params as $param) {
        if(isset($data[$param]) && is_array(json_decode($data[$param]))) {
            $array = json_decode($data[$param], true);
            if(count($array) == 0) {
                $messages[] = $param . " обязательно для заполнения";
            }
            continue;
        }

        if(isset($data[$param]) && is_numeric($data[$param])) {
            continue;
        }

        if (!isset($data[$param]) || empty(trim($data[$param]))) {
            $messages[] = $param . " обязательно для заполненеия\n";
        }
    }

    return count($messages) > 0 ? $messages : null;
}