<?php
error_reporting(0);
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

$storage = $_SERVER['DOCUMENT_ROOT'] . '/storage/chat_' . date('dmY', time());

if (!$_SESSION['user'])
{
    $_SESSION['user'] = random_letter() . rand(10000, 99999);
}

function random_char ()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomChar = $characters[rand(0, strlen($characters) - 1)];
    return $randomChar;
}
function random_letter ()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomChar = $characters[rand(0, strlen($characters) - 1)];
    return $randomChar;
}
function sanitizeInput ($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function antiXSS ($input)
{
    $sanitized = strip_tags($input);
    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');

    return $sanitized;
}