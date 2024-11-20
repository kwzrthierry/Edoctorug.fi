<?php
// Set the response content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (make sure this is turned off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent accidental output before JSON response
ob_start();

// Database connection
require_once 'test 3/db_connection.php';

// Retrieve POST data
$nationalId = $_POST['signupNationalIdNumber'] ?? '';
$name = $_POST['signupName'] ?? '';
$email = $_POST['signupEmail'] ?? '';
$phone = $_POST['saveMoneyPhone'] ?? '';
$password = $_POST['signupPassword'] ?? '';
$wantsToGiveLoans = isset($_POST['wantsToGiveLoans']) ? 1 : 0;  // Check if the checkbox is checked

// Determine user type based on the checkbox
$usertype = $wantsToGiveLoans ? 'user' : 'loaner';  // Assign usertype based on the checkbox

// Handle file upload
$uploadDir = 'upload/';
$nationalIdFolder = $uploadDir . $nationalId . '_' . preg_replace('/\s+/', '_', $name) . '/';
$uploadedFilePath = '';

if (!empty($_FILES['nationalIdFile']['name'])) {
    $fileName = basename($_FILES['nationalIdFile']['name']);
    $filePath = $nationalIdFolder . $fileName;

    // Create directory if it does not exist
    if (!file_exists($nationalIdFolder)) {
        mkdir($nationalIdFolder, 0777, true);
    }

    // Move uploaded file to the designated folder
    if (move_uploaded_file($_FILES['nationalIdFile']['tmp_name'], $filePath)) {
        $uploadedFilePath = $filePath;
    } else {
        ob_end_clean(); // Clear output buffer to prevent invalid JSON
        echo json_encode(['status' => 'error', 'errors' => ['file' => 'Failed to upload file']]);
        exit();
    }
}

// Initialize an array to hold errors
$errors = [];

// Function to check if a value already exists in a column
function checkIfExists($conn, $column, $value) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE $column = ?");
    if ($stmt === false) {
        return false;
    }
    $stmt->bind_param('s', $value);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

// Validate and check for existing records
if (checkIfExists($conn, 'email', $email)) {
    $errors['email'] = 'Email is already registered';
}

if (checkIfExists($conn, 'national_id_number', $nationalId)) {
    $errors['nationalId'] = 'National ID number is already registered';
}

if (checkIfExists($conn, 'phone', $phone)) {
    $errors['phone'] = 'Phone number is already registered';
}

// If there are errors, return them as JSON
if (!empty($errors)) {
    ob_end_clean(); // Clear output buffer to prevent invalid JSON
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit();
}

// Insert new user into the database
$stmt = $conn->prepare('INSERT INTO users (national_id_number, name, email, phone, password, national_id_file, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)');
if ($stmt === false) {
    ob_end_clean(); // Clear output buffer to prevent invalid JSON
    echo json_encode(['status' => 'error', 'errors' => ['database' => 'Failed to prepare statement']]);
    exit();
}

// Use variables for all parameters:
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$stmt->bind_param('sssssss', $nationalId, $name, $email, $phone, $hashedPassword, $uploadedFilePath, $usertype);
if ($stmt->execute()) {
    ob_end_clean(); // Clear output buffer to prevent invalid JSON
    echo json_encode(['status' => 'success']);
} else {
    ob_end_clean(); // Clear output buffer to prevent invalid JSON
    echo json_encode(['status' => 'error', 'errors' => ['database' => 'Failed to execute statement']]);
}
$stmt->close();

// Close the database connection
$conn->close();

// End output buffering and flush the output
ob_end_flush();
?>
