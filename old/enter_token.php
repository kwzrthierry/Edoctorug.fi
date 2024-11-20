<?php
// Assuming database connection and necessary includes are done here
require "test 3/db_connection.php";

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the token from the form
    $token = '';
    for ($i = 0; $i < 5; $i++) {
        $token .= $_POST["digit$i"];
    }

    // Validate the token length
    if (strlen($token) !== 5 || !preg_match('/^[0-9A-Za-z]+$/', $token)) {
        $error = 'The token you entered is incorrect.';
    } else {
        // Check if the token exists and is valid
        $sql = "SELECT email FROM forgot_password_tokens WHERE token = ? AND expiry_time > NOW()";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                // Invalid or expired token
                $error = 'The token you entered is incorrect.';
            } else {
                // Token is valid, retrieve the email
                $row = $result->fetch_assoc();
                $email = $row['email'];

                // Update the token status to 'verified'
                $sql_update = "UPDATE forgot_password_tokens SET status = 'verified' WHERE token = ?";
                if ($stmt_update = $conn->prepare($sql_update)) {
                    $stmt_update->bind_param("s", $token);
                    $stmt_update->execute();
                }

                // Redirect to the change password page with email as a query parameter
                header("Location: change_password.php?email=" . urlencode($email));
                exit();
            }
        } else {
            // Handle SQL statement preparation error
            $error = 'An error occurred while verifying the token.';
        }

        // Close statement and connection
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Token</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            text-align: center;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 90%;
            max-width: 400px;
        }
        h1 {
            color: black;
            margin-bottom: 20px;
            padding: 10px;
        }
        input {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            text-align: center;
            margin: 0 5px;
            border: 2px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s, transform 0.2s;
        }
        input.correct {
            border-color: #4CAF50;
        }
        input.incorrect {
            border-color: #F44336;
        }
        button {
            font-size: 1.2rem;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:disabled {
            background-color: #d6d6d6;
            cursor: not-allowed;
        }
        .error {
            color: #F44336;
            margin-top: 10px;
            font-size: 1rem;
        }
        .countdown {
            font-size: 1rem;
            margin-top: 10px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Enter Your Token</h1>
        <form id="tokenForm" action="enter_token.php" method="POST">
            <div id="tokenFields">
                <!-- JavaScript will generate input fields here -->
            </div>
            <button type="submit" id="submitButton" disabled>Submit</button>
            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="countdown" id="countdown"></div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tokenFields = document.getElementById('tokenFields');
            const numFields = 5; // Updated to 5 characters
            for (let i = 0; i < numFields; i++) {
                const input = document.createElement('input');
                input.type = 'text';
                input.maxLength = 1;
                input.name = `digit${i}`;
                input.dataset.index = i;
                input.addEventListener('input', validateInput);
                input.addEventListener('paste', handlePaste);
                tokenFields.appendChild(input);
            }

            function validateInput(e) {
                const input = e.target;
                const value = input.value;
                if (/^[0-9A-Za-z]$/.test(value)) {
                    input.classList.add('correct');
                    input.classList.remove('incorrect');
                    // Move to next input
                    const nextInput = input.nextElementSibling;
                    if (nextInput) nextInput.focus();
                } else {
                    input.classList.add('incorrect');
                    input.classList.remove('correct');
                }
                checkFormCompletion();
            }

            function checkFormCompletion() {
                const inputs = document.querySelectorAll('input');
                const allCorrect = Array.from(inputs).every(input => input.classList.contains('correct'));
                document.getElementById('submitButton').disabled = !allCorrect;
            }

            function handlePaste(e) {
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const inputs = document.querySelectorAll('input');
                if (pasteData.length === numFields) {
                    inputs.forEach((input, index) => {
                        input.value = pasteData[index];
                        validateInput({ target: input });
                    });
                    checkFormCompletion();
                }
            }

            function startCountdown() {
                let countdownTime = 120; // 2 minutes in seconds
                const countdownElem = document.getElementById('countdown');
                const interval = setInterval(() => {
                    const minutes = Math.floor(countdownTime / 60);
                    const seconds = countdownTime % 60;
                    countdownElem.textContent = `Resend link available in ${minutes}:${seconds.toString().padStart(2, '0')}`;
                    countdownTime--;
                    if (countdownTime < 0) {
                        clearInterval(interval);
                        countdownElem.textContent = '';
                    }
                }, 1000);
            }

            startCountdown();
        });
    </script>
</body>
</html>
