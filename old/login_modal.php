<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            padding: 0; /* Remove padding to make it full height */
            margin: 0; /* Remove margin for full height */
            height: 100vh; /* Full viewport height */
            display: flex; /* Flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }

        .login-container {
            max-width: 400px;
            width: 100%; /* Responsive width */
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
            text-align: center; /* Center the text */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-group label {
            font-weight: 600;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #007bff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.25);
        }

        .input-group-text {
            border-radius: 5px 0 0 5px;
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.2rem rgba(38, 143, 255, 0.5);
        }

        .forgot-password {
            margin-top: 10px;
        }

        .signup-link {
            margin-top: 10px;
        }

        .logo {
            margin-bottom: 20px; /* Space below logo */
            height: 60px; /* Set logo height */
        }

        /* Styles for the Back button */
        .back-button {
            position: absolute; /* Position it at the top left */
            top: 20px; /* Adjust as needed */
            left: 20px; /* Adjust as needed */
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 50%; /* Circular button */
            width: 40px; /* Button width */
            height: 40px; /* Button height */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <!-- Back to Home link -->
    <button onclick="goBack()" class="back-button">
        <i class="fas fa-arrow-left"></i> <!-- Left arrow icon -->
    </button>

    <div class="login-container">
        <img src="../assets/images/client-01.png" alt="Logo" class="logo"> <!-- Add your logo here -->
        <h3 class="mb-4">Login</h3>
        <!-- Login form -->
        <form id="loginForm" method="POST" action="login.php">
            <div class="form-group">
                <label for="loginEmail">Email</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" class="form-control" id="loginEmail" name="loginEmail" required>
                </div>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-4">Login</button>
        </form>
        <!-- Forgot Password link -->
        <div class="forgot-password">
            <a href="forgot_password.php" class="btn btn-link">Forgot Password?</a>
        </div>
        <!-- Sign Up button/link -->
        <div class="text-center mt-3">
            <p>Don't have an account? <a href="signup_modal.php">Register here</a></p>
        </div>
    </div>
    
    <script>
        function goBack() {
            window.location.href = "../index.html"; // Redirect to index.html
        }
    </script>
    <!-- Full jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- jQuery and Bootstrap JavaScript -->
    <script src="script.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
