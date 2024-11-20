<?php
// Including the database connection file
require 'test 3/db_connection.php';

// Yo! Payments API credentials
$yo_username = "90005702859";
$yo_password = "MsUl-Ei4O-BmJo-apHP-npHY-wsYT-c8nc-skny";
$yo_endpoint = "https://sandbox.yo.co.ug/services/yopaymentsdev/task.php";

// Checking if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieving form data
    $name = $_POST['saveMoneyName'];
    $phone = "256" . $_POST['saveMoneyPhone']; // Prepending "256" to phone number
    $email = $_POST['saveMoneyEmail'];
    $amount = $_POST['saveMoneyAmount'];
    $id = $_POST['loanNationalIdNumber'];

    // Creating XML request for depositing funds
    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
        <AutoCreate>
            <Request>
                <APIUsername>' . $yo_username . '</APIUsername>
                <APIPassword>' . $yo_password . '</APIPassword>
                <Method>acdepositfunds</Method>
                <NonBlocking>TRUE</NonBlocking>
                <Account>' . $phone . '</Account>
                <Amount>' . $amount . '</Amount>
                <Narrative>Saving Money</Narrative>
                <ExternalReference>123456789</ExternalReference>
            </Request>
        </AutoCreate>';

    // Initializing cURL session
    $ch = curl_init($yo_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

    // Executing cURL session
    $response = curl_exec($ch);
    curl_close($ch);

    // Parsing XML response
    $xml = simplexml_load_string($response);
    $status = $xml->Response->Status;

    // Checking the status of the response
    if ($status == "OK") {
        // Inserting data into the database
        $stmt = $conn->prepare("INSERT INTO savings (id_number, name, phone, email, amount) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd",$id, $name, $phone, $email, $amount);

        // Executing the prepared statement
        if ($stmt->execute()) {
            echo "Money saved successfully and recorded in the database.";
        } else {
            echo "Money saved successfully, but failed to record in the database.";
        }

        // Closing the prepared statement
        $stmt->close();
    } else {
        // Error message if saving money failed
        echo "Error saving money: " . $xml->Response->StatusMessage;
    }
}
?>
