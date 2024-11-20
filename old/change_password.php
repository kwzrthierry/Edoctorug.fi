<?php
// Assuming database connection and necessary includes are done here
require "test 3/db_connection.php";

$error = '';
$success = '';

// Get the email from the URL
$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $newPassword = $_POST["newPassword"];
    $confirmPassword = $_POST["confirmPassword"];

    // Validate passwords
    if ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the user's password
        $sql_update = "UPDATE users SET password = ? WHERE email = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $hashedPassword, $email);
            if ($stmt_update->execute()) {
                // Password updated successfully, trigger countdown
                $success = 'Password updated successfully. You will be redirected to the login page in 3 seconds.';
            } else {
                // Handle SQL update error
                $error = 'An error occurred while updating your password. Please try again.';
            }
        } else {
            // Handle SQL statement preparation error
            $error = 'An error occurred while preparing the update query. Please try again.';
        }
    }
    // Close statement and connection
    $stmt_update->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .form-group label {
            font-weight: 600;
        }
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .success {
            color: #4CAF50;
            margin-top: 10px;
        }
        .error {
            color: #F44336;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <p>Redirecting in <span id="countdown">3</span> seconds...</p>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <h3 class="mb-4">Change Your Password</h3>
            <form action="change_password.php?email=<?php echo urlencode($email); ?>" method="POST">
                <input type="hidden" name="action" value="change_password">
                <div class="form-group password-container">
                    <label for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                </div>
                <div class="form-group password-container">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Password</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const successMessage = document.querySelector('.success');
            if (successMessage) {
                let countdownElement = document.getElementById('countdown');
                let countdownValue = 3;
                const interval = setInterval(() => {
                    countdownValue--;
                    countdownElement.textContent = countdownValue;
                    if (countdownValue === 0) {
                        clearInterval(interval);
                        window.location.href = 'index.php';
                    }
                }, 1000);
            }
        });
    </script>
</body>
</html>
