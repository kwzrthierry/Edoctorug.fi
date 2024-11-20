<?php
include 'token_generation.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $billerId = $input['biller'];
    $paymentCode = $input['paymentCode'];
    $customerId = $input['customerId'];
    $amount = $input['amount'];
    $terminalId = '3pbl0001';
    $accessToken = getAccessToken();
    $url = 'https://sandbox.interswitchng.com/quickteller/api/v1/transactions';

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'terminalId: ' . $terminalId
    ];

    $data = json_encode([
        'customerId' => $customerId,
        'paymentCode' => $paymentCode,
        'amount' => $amount * 100,
        'requestReference' => uniqid(),
        'terminalId' => $terminalId
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
}
?>
