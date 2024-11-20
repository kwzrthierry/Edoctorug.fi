<?php
// Start the session
session_start();

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['user_name'])) {
    header("Location: index.php");
    exit();
}

// Fetch the current account balance
function fetchBalance() {
    // Yo! Payments API credentials
    $yo_username = "90005702859";
    $yo_password = "MsUl-Ei4O-BmJo-apHP-npHY-wsYT-c8nc-skny";
    $yo_endpoint = "https://sandbox.yo.co.ug/services/yopaymentsdev/task.php";

    // Construct XML request for fetching account balance
    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
        <AutoCreate>
            <Request>
                <APIUsername>'. $yo_username. '</APIUsername>
                <APIPassword>'. $yo_password. '</APIPassword>
                <Method>acaccountbalance</Method>
                <NonBlocking>TRUE</NonBlocking>
            </Request>
        </AutoCreate>';

    // Initialize cURL session
    $ch = curl_init($yo_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

    // Execute cURL request
    $response = curl_exec($ch);
    curl_close($ch);

    // Parse XML response and return account balance
    $xml = simplexml_load_string($response);
    return $xml->Response->Balance->Amount;
}

// Fetch current account balance
$account_balance = fetchBalance();
?>