<?php
include 'token_generation.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    echo getBillers();
}

function getBillers() {
    $accessToken = getAccessToken();
    
    if (!$accessToken) {
        return json_encode(['error' => 'Failed to obtain access token.']);
    }
    
    $url = 'https://qa.interswitchng.com/quicktellerservice/api/v5/services?categoryId=1';

    $headers = [
        'Authorization: Bearer ' . $accessToken
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return json_encode(['error' => $error]);
    }
    
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status >= 400) {
        return json_encode(['error' => 'HTTP error ' . $http_status . ': Failed to retrieve data.']);
    }

    return $response;
}
?>