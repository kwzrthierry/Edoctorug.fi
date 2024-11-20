<?php
require '../test 3/db_connection.php';

// Check if nationalIdNumber is provided in the POST data
if (isset($_POST['nationalIdNumber'])) {
    $nationalIdNumber = $_POST['nationalIdNumber'];

    // Fetch user details based on national ID number
    $sql = "SELECT * FROM users WHERE national_id_number = '$nationalIdNumber'";
    $result = $conn->query($sql);

    // Check if user found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Retrieve the national ID file path from the user record
        $nationalIdFile = $user['national_id_file'];

        // Return the file path as a JSON response
        echo json_encode([
            'status' => 'success',
            'fileUrl' => $nationalIdFile
        ]);
        exit();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found.'
        ]);
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'National ID number parameter missing.'
    ]);
    exit();
}
?>
