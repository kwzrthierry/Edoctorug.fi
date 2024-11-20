<?php
session_start();

// Including the database connection file
require '../test 3/db_connection.php';

// Yo! Payments API credentials
$yo_username = "90005702859";
$yo_password = "MsUl-Ei4O-BmJo-apHP-npHY-wsYT-c8nc-skny";
$yo_endpoint = "https://sandbox.yo.co.ug/services/yopaymentsdev/task.php";

// Checking if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $amount = $_POST['saveMoneyAmount'];

    // Fetch user information using national ID stored in session
    if (isset($_SESSION['national_id'])) {
        $national_id = $_SESSION['national_id'];

        // Query to fetch user data based on national ID
        $query = "SELECT name, phone, email FROM users WHERE national_id_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $national_id);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($name, $phone, $email);
            $stmt->fetch();

            // Prepend "256" to phone number
            $phoneN = "256" . $phone;

            // Creating XML request for depositing funds
            $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                <AutoCreate>
                    <Request>
                        <APIUsername>' . $yo_username . '</APIUsername>
                        <APIPassword>' . $yo_password . '</APIPassword>
                        <Method>acdepositfunds</Method>
                        <NonBlocking>TRUE</NonBlocking>
                        <Account>' . $phoneN . '</Account>
                        <Amount>' . $amount . '</Amount>
                        <Narrative>Saving Money</Narrative>
                        <ExternalReference>123456789</ExternalReference>
                    </Request>
                </AutoCreate>';

            // Initialize cURL session
            $ch = curl_init($yo_endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));

            // Execute cURL session
            $response = curl_exec($ch);
            curl_close($ch);

            // Parse XML response
            $xml = simplexml_load_string($response);
            $status = $xml->Response->Status;

            // Check the status of the response
            if ($status == "OK") {
                // Insert data into the database including the national ID
                $insert_query = "INSERT INTO savings (id_number, name, phone, email, amount) VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param("ssssd", $national_id, $name, $phone, $email, $amount);

                // Execute the prepared statement
                if ($insert_stmt->execute()) {
                    // Set session variable for success message
                    $_SESSION['save_money_success'] = true;
                    echo "success";
                } else {
                    echo "Money saved successfully, but failed to record in the database.";
                }

                // Close the prepared statement
                $insert_stmt->close();
            } else {
                // Error message if saving money failed
                echo "Error saving money: " . $xml->Response->StatusMessage;
            }
        } else {
            echo "User with national ID $national_id not found.";
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "National ID not found in session.";
    }
}
?>
