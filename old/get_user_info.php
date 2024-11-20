<?php
require 'test 3/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loanId'])) {
    $loanId = $_POST['loanId'];

    $sql = "SELECT * FROM loans_application WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loanId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $loan = $result->fetch_assoc();
        echo "<p>Name: {$loan['name']}</p>";
        echo "<p>Phone: {$loan['phone']}</p>";
        echo "<p>Email: {$loan['email']}</p>";
        echo "<p>National ID Number: {$loan['national_id_number']}</p>";
        echo "<p>Loan Amount: {$loan['loan_amount']}</p>";
        echo "<p>Application Date: {$loan['application_date']}</p>";
        echo "<div id='userNationalIdFile' data-location='{$loan['national_id_file']}'></div>";
    } else {
        echo "No user information found.";
    }

    $stmt->close();
    $conn->close();
}
?>
