<?php
function getAccessToken() {
    $clientId = 'IKIA72C65D005F93F30E573EFEAC04FA6DD9E4D344B1';
    $clientSecret = 'YZMqZezsltpSPNb4+49PGeP7lYkzKn1a5SaVSyzKOiI=';
    
    $authUrl = 'https://passport.k8.isw.la/passport/oauth/token';
    $authCredentials = base64_encode($clientId . ':' . $clientSecret);

    $headers = [
        'Authorization: Basic ' . $authCredentials,
        'Content-Type: application/x-www-form-urlencoded'
    ];

    $data = 'grant_type=client_credentials';

    $ch = curl_init($authUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return null;
    }

    curl_close($ch);

    $response = json_decode($response, true);
    if (isset($response['access_token'])) {
        return $response['access_token'];
    } else {
        echo 'Error fetching access token: ' . json_encode($response);
        return null;
    }
}
?>
