<?php

function generate_token() {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = str_shuffle($characters);
    return substr($randomString, 0, 60);
}