<?php
include 'token_generation.php';

function getTransactionStatus($requestReference) {
    $accessToken = getAccessToken();
    $url = 'https://sandbox.interswitchng.com/billpayments/api/v1/transactions/' . $requestReference;

    $headers = [
        'Authorization: Bearer ' . $accessToken
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestReference = $_POST['requestReference'];

    $status = getTransactionStatus($requestReference);
    echo json_encode($status);
}
?>
