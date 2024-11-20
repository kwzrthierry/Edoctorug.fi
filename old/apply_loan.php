<?php
// Include the file for database connection
require 'test 3/db_connection.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['loanName'];
    $phone = "6" . $_POST['loanPhone'];
    $email = $_POST['loanEmail'];
    $nationalIdNumber = $_POST['loanNationalIdNumber'];
    $loanAmount = $_POST['loanAmount'];

    // Handle file upload
    $targetDir = "upload/";
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
        && $imageFileType != "gif" && $imageFileType != "pdf") {
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
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO loans_application (name, phone, email, national_id_number, national_id_file, loan_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

            // Bind parameters
            $stmt->bind_param("sssssd", $name, $phone, $email, $nationalIdNumber, $nationalIdFile, $loanAmount);

            // Execute the database insertion query
            if ($stmt->execute()) {
                echo "success";
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
?>
