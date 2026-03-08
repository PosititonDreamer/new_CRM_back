<?php
function find_user($connect, $token) {
    $user = mysqli_query($connect, "SELECT * FROM `workers` WHERE token = '$token'");
    if (mysqli_num_rows($user) > 0) {
        $user = mysqli_fetch_assoc($user);
        $rule_id = $user["id_worker_rule"];
        $user_rule = mysqli_query($connect, "SELECT * FROM `workers_rule` WHERE id = $rule_id");
        $user_rule = mysqli_fetch_assoc($user_rule);
        return [
            "id" => $user["id"],
            'name' => $user['name'],
            'description' => $user['description'],
            'salary' => $user['salary'],
            'token' => $user['token'],
            'rule' => $user_rule['title']
        ];
    }
    return null;
}