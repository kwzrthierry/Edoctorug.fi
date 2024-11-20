<?php
include 'token_generation.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerId = $_POST['customerId'];
    $paymentCode = $_POST['paymentCode'];
    $accessToken = getAccessToken();
    $url = 'https://qa.interswitchng.com/quicktellerservice/api/v5/Transactions/validatecustomers';

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'TerminalID: 3pbl0001'
    ];

    $data = json_encode([
        'customerId' => $customerId,
        'paymentCode' => $paymentCode
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch);

    if ($response === false) {
        // Handle CURL error
        echo json_encode(['status' => 'error', 'message' => 'CURL Error: ' . curl_error($ch)]);
    } else {
        // Check HTTP status code
        if ($http_code >= 200 && $http_code < 300) {
            // Successful response
            echo $response;
        } else {
            // Error response
            echo json_encode(['status' => 'error', 'message' => 'HTTP Error: ' . $http_code]);
        }
    }
}
?>
