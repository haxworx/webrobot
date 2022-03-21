<?php

require 'lib/Twig.php';

$validated = false;

function valid_address($address)
{
    if (preg_match('/^(http|https):\/\/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', strtolower($address))) {
        return true;
    }
    return false;
}

function valid_agent($agent)
{
    if (preg_match('/^[A-Za-z0-9\._\/]+\/\d+\.\d+$/', $agent)) {
        return true;
    }
    return false;
}

function valid_time($time)
{
    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
        return true;
    }
    return false;
}

function valid_frequency($frequency)
{
    if (($frequency === 'weekly' || ($frequency === 'daily'))) {
        return true;
    }
    return false;
}

function float_in_range($value, $min, $max)
{
    if (!is_float($value)) {
        return false;
    }
    if (($value >= floatval($min)) && ($value <= floatval($max))) {
        return true;
    }
    return false;
}

function int_in_range($value, $min, $max)
{
    if (!is_int($value)) {
        return false;
    }
    if (($value >= intval($min)) && ($value <= intval($max))) {
        return true;
    }
    return false;
}

$allowed = [ 'address', 'agent', 'time', 'frequency', 'delay', 'retry_max'];
$frequency_allowed = [ 'daily', 'weekly' ];
$weekdays = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
$daily = 0;
$weekly = 0;
$weekday = null;

foreach ($allowed as $param) {
    if (!isset($_POST[$param])) {
        return;
    }

    if (!in_array($param, $allowed)) {
        http_response_code(500);
        return;
    }
}

$address         = $_POST['address'];
$agent           = $_POST['agent'];
$start_time      = $_POST['time'];
$delay           = floatval($_POST['delay']);
$retry_max       = intval($_POST['retry_max']);
$frequency       = $_POST['frequency'];
if (!in_array($frequency, $frequency_allowed)) {
    http_response_code(500);
    return;
}

# Optional (checkbox) parameters.
if (isset($_POST['ignore_query'])) {
    $ignore_query = 1;
} else {
    $ignore_query = 0;
}

if (isset($_POST['import_sitemaps'])) {
    $import_sitemaps = 1;
} else {
    $import_sitemaps = 0;
}

$input_error = false;

if (!valid_time($start_time)) {
    $input_error = "Invalid time specified";
}

if (!valid_agent($agent)) {
    $input_error = "Invalid user-agent specified.";
}

if (!valid_address($address)) {
    $input_error = "Invalid address specified.";
}

if (!valid_frequency($frequency)) {
    $input_error = "Invalid frequency specified.";
}

if (!float_in_range($delay, 0.5, 5.0)) {
    $input_error = "Interval out of range.";
}

if (!int_in_range($retry_max, 0, 10)) {
    $input_error = "Retry max out of range.";
}

if ((!isset($_POST['content_types'])) || (!is_array($_POST['content_types'])) || (count($_POST['content_types']) == 0)) {
    $input_error = "No content type specfied.";
}

if ($input_error !== false) {
    try {
        $template = $twig->load('errors.html.twig');
    } catch (Exception $e) {
        error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
        http_response_code(500);
        return;
    }
    echo $template->render(['message' => $input_error]);
    return;
}

if ($frequency === "daily") {
    $daily = 1;
} elseif ($frequency === "weekly") {
    if (!isset($_POST['weekly']) || empty($_POST['weekly'])) {
        http_response_code(500);
        return;
    } else {
        if (!in_array($_POST['weekly'], $weekdays)) {
            http_response_code(500);
            return;
        }
        $weekly = 1;
        $weekday = $_POST['weekly'];
    }
}

$domain = parse_url($address, PHP_URL_HOST);
$scheme = parse_url($address, PHP_URL_SCHEME);

$validated = true;
