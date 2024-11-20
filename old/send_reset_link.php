<?php
// Assuming database connection and necessary includes are done here
require "test 3/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["forgotEmail"];

    // Validate email (basic validation)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Handle invalid email scenario
        header("Location: forgot_password.php?error=invalidemail");
        exit();
    }

    // Check if the email exists in the users table and get user type
    $sql_check = "SELECT user_type FROM users WHERE email = ?";
    if ($stmt_check = $conn->prepare($sql_check)) {
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            // No user with that email exists, redirect to signup page
            header("Location: signup.php?error=emailnotfound");
            exit();
        }

        // Check user type
        if (in_array($user['user_type'], ['admin', 'support'])) {
            // User is admin or support, show modal
            echo '<script>
                    alert("For customer support or admin, please contact the website administrator or IT department at TK.tech.rw@gmail.com.");
                    window.location.href = "forgot_password.php";
                  </script>';
            exit();
        }
    } else {
        // Handle SQL statement preparation error
        header("Location: forgot_password.php?error=sqlerror");
        exit();
    }

    // Generate a 5-character token (letters and numbers)
    $token = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);

    // Prepare SQL statement to delete any existing token
    $sql_delete = "DELETE FROM forgot_password_tokens WHERE email = ?";
    if ($stmt_delete = $conn->prepare($sql_delete)) {
        $stmt_delete->bind_param("s", $email);
        $stmt_delete->execute();
        $stmt_delete->close();
    } else {
        // Handle SQL statement preparation error
        header("Location: forgot_password.php?error=sqlerror");
        exit();
    }

    // Prepare SQL statement to insert new token
    $sql = "INSERT INTO forgot_password_tokens (email, token, expiry_time, status)
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE), 'not_verified')";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ss", $email, $token);

        // Execute statement
        if ($stmt->execute()) {
            // Mailjet configuration
            $apiKey = '011d2876304c6d5ce2a96d8ad5a5956c';
            $apiSecret = '4647168034066ebe852841169105580c';

            $data = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => 'tk.tech.rw@gmail.com',
                            'Name' => 'Saving & Loans'
                        ],
                        'To' => [
                            [
                                'Email' => $email
                            ]
                        ],
                        'Subject' => 'Password Reset Token',
                        'TextPart' => "Your password reset token is: $token",
                        'HTMLPart' => "<html>
                                        <body style='font-family: \"Poppins\", sans-serif; color: #333;'>
                                            <div style='max-width: 600px; margin: auto; padding: 20px; background-color: #f4f4f4; border-radius: 8px; text-align: center;'>
                                                <h2 style='color: #007bff;'>Password Reset Token</h2>
                                                <p style='font-size: 1.2rem;'>Your password reset token is: <strong style='font-size: 2rem; color: #007bff;'>$token</strong></p>
                                                <p style='font-size: 1rem;'>Thank you for using our service. If you did not request this, please ignore this email.</p>
                                                <p style='font-size: 0.9rem;'>Best regards,<br>Savings & Loans</p>
                                            </div>
                                        </body>
                                    </html>"
                    ]
                ]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3.1/send");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$apiSecret");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                error_log("cURL Error: " . curl_error($ch)); // Log the error
                // Redirect to error page with a specific error code
                header("Location: forgot_password.php?error=emailsenderror");
                curl_close($ch);
                exit();
            }
            curl_close($ch);

            // Redirect to token input page
            header("Location: enter_token.php");
            exit();
        } else {
            // Handle database insertion/updating error
            header("Location: forgot_password.php?error=sqlerror");
            exit();
        }
    } else {
        // Handle SQL statement preparation error
        header("Location: forgot_password.php?error=sqlerror");
        exit();
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Handle invalid request method (should not happen if form is correctly configured)
    header("Location: forgot_password.php");
    exit();
}
?>
