<?php
if ($messages) {
    $req = [
        "messages" => $messages
    ];
    http_response_code(400);
    echo json_encode($req);
    die();
}