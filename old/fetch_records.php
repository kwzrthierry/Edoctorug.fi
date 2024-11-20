<?php
session_start();

// Database connection setup
require 'test 3/db_connection.php';  // Adjust this based on your actual database connection file path
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Escape user input for safety
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_name']);

// Fetch savings records
$savings_query = "SELECT amount, date FROM savings WHERE name = '$user_id'";
$savings_result = mysqli_query($conn, $savings_query);

// Fetch loan applications records
$loans_query = "SELECT loan_amount, application_date, status, reason FROM loans_application WHERE name = '$user_id'";
$loans_result = mysqli_query($conn, $loans_query);

// Prepare HTML for savings records
$savings_html = "<h3>Savings Records</h3>";
if (mysqli_num_rows($savings_result) > 0) {
    $savings_html .= "<table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>";
    while ($row = mysqli_fetch_assoc($savings_result)) {
        $savings_html .= "<tr>
                            <td>{$row['amount']}</td>
                            <td>{$row['date']}</td>
                          </tr>";
    }
    $savings_html .= "</tbody></table>";
} else {
    $savings_html .= "<p>No savings records found.</p>";
}

// Prepare HTML for loan applications records
$loans_html = "<h3>Loan Applications Records</h3>";
if (mysqli_num_rows($loans_result) > 0) {
    $loans_html .= "<table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>Loan Amount</th>
                                <th>Application Date</th>
                                <th>Status</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>";
    while ($row = mysqli_fetch_assoc($loans_result)) {
        $loans_html .= "<tr>
                            <td>{$row['loan_amount']}</td>
                            <td>{$row['application_date']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['reason']}</td>
                          </tr>";
    }
    $loans_html .= "</tbody></table>";
} else {
    $loans_html .= "<p>No loan applications records found.</p>";
}

// Combine savings and loans HTML
$records_html = $savings_html . $loans_html;

// Output the combined HTML
echo $records_html;

mysqli_close($conn);
?>
