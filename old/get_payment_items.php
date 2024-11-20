<?php
session_start();
include 'token_generation.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $billerId = $_POST['billerId'];
    $_SESSION['billerId'] = $billerId; // Store the billerId in the session

    error_log("Debug: Received billerId: $billerId"); // Debug statement

    $accessToken = getAccessToken();
    
    if (!$accessToken) {
        error_log("Debug: Failed to obtain access token"); // Debug statement
        echo json_encode(['error' => 'Failed to obtain access token.']);
        exit;
    }
    
    error_log("Debug: Obtained access token"); // Debug statement

    $url = 'https://qa.interswitchng.com/quicktellerservice/api/v5/services/options?serviceid=' . $billerId;

    error_log("Debug: Request URL: $url"); // Debug statement

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'TerminalID: 3pbl0001'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        error_log("Debug: Curl error: $curlError"); // Debug statement
        echo json_encode(['error' => 'Curl error: ' . $curlError]);
        curl_close($ch);
        exit;
    }
    
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("Debug: HTTP status: $http_status"); // Debug statement

    if ($http_status >= 400) {
        error_log("Debug: HTTP error $http_status: Failed to retrieve data"); // Debug statement
        echo json_encode(['error' => 'HTTP error ' . $http_status . ': Failed to retrieve data.']);
        exit;
    }

    error_log("Debug: Response: $response"); // Debug statement
    echo $response;
}
?>
