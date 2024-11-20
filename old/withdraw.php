<?php
// Include the file for database connection
require 'test 3/db_connection.php';

// Yo! Payments API credentials
$yo_username = "90005702859";
$yo_password = "MsUl-Ei4O-BmJo-apHP-npHY-wsYT-c8nc-skny";
$yo_endpoint = "https://sandbox.yo.co.ug/services/yopaymentsdev/task.php";

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['loanName'];
    $phone = "6" . $_POST['loanPhone'];
    $email = $_POST['loanEmail'];
    $nationalIdNumber = $_POST['loanNationalIdNumber'];
    $loanAmount = $_POST['loanAmount'];

    // Handle file upload
    $targetDir = "uploads/";
    $nationalIdFile = $targetDir . basename($_FILES["loanNationalId"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($nationalIdFile, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($nationalIdFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 2MB)
    if ($_FILES["loanNationalId"]["size"] > 2000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" && $imageFileType != "pdf" ) {
        echo "Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        // If file upload is successful
        if (move_uploaded_file($_FILES["loanNationalId"]["tmp_name"], $nationalIdFile)) {
            // File upload success, proceed with database insertion
            $stmt = $conn->prepare("INSERT INTO loans_application (name, phone, email, national_id_number, national_id_file, loan_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssd", $name, $phone, $email, $nationalIdNumber, $nationalIdFile, $loanAmount);

            // Execute the database insertion query
            if ($stmt->execute()) {
                echo "Your loan application has been submitted successfully.";
                // After successfully saving the application, initiate a Yo! Payments transaction
                initiateYoPayment($name, $phone, $loanAmount, $yo_username, $yo_password, $yo_endpoint);
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Close the database connection
$conn->close();

// Function to initiate Yo! Payments transaction
function initiateYoPayment($name, $phone, $loanAmount, $yo_username, $yo_password, $yo_endpoint) {
    // Construct XML request for Yo! Payments API
    $xml_request = '<?xml version="1.0" encoding="UTF-8"?>' .
                   '<AutoCreate>' .
                   '<Request>' .
                   '<APIUsername>' . $yo_username . '</APIUsername>' .
                   '<APIPassword>' . $yo_password . '</APIPassword>' .
                   '<Method>acwithdrawfundstobank</Method>' .
                   '<Amount>' . $loanAmount . '</Amount>' .
                   '<CurrencyCode>UGX</CurrencyCode>' .
                   '<BankAccountName>' . $name . '</BankAccountName>' .
                   '<BankAccountNumber>' . $phone . '</BankAccountNumber>' .
                   '<PrivateTransactionReference>' . uniqid() . '</PrivateTransactionReference>' .
                   '</Request>' .
                   '</AutoCreate>';

    // Initialize cURL session
    $ch = curl_init($yo_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);

    // Execute the cURL session
    $response = curl_exec($ch);
    // Check for errors during cURL execution
    if ($response === false) {
        echo "Curl error: " . curl_error($ch);
    } else {
        // Display payment initiation response
        echo "Payment initiation response: " . $response;
    }

    // Close cURL session
    curl_close($ch);
}
?>
