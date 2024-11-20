<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Money</title>
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
            padding: 20px;
        }

        .back-arrow {
            font-size: 24px;
            text-decoration: none;
            color: #007bff;
            margin-bottom: 20px;
            display: inline-block;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .back-arrow:hover {
            transform: translateX(-10px);
            color: #0056b3;
        }

        .save-money-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.5s ease-in-out;
        }
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animated {
            animation-duration: 0.5s;
            animation-fill-mode: both;
        }

        .pulse {
            animation-name: pulse;
            animation-duration: 1s;
            animation-iteration-count: infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale3d(1, 1, 1);
            }
            50% {
                transform: scale3d(1.05, 1.05, 1.05);
            }
        }

        .btn-primary {
            border-radius: 30px;
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #0056b3, #007bff);
        }
        .logo {
            margin-bottom: 20px;
            height: 60px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
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
        <i class="fas fa-arrow-left"></i>
    </button>

    <!-- Save money form container -->
    <div class="save-money-container">
        <img src="../assets/images/client-01.png" alt="Logo" class="logo">
        <h3>Save Money</h3>
        <!-- Save money form -->
        <form id="saveMoneyForm" enctype="multipart/form-data">
            <!-- Form group for national ID number -->
            <div class="form-group">
                <label for="loanNationalIdNumber">National ID Number</label>
                <input type="text" class="form-control" id="loanNationalIdNumber" name="loanNationalIdNumber" required>
            </div>
            <!-- Form group for full name -->
            <div class="form-group">
                <label for="saveMoneyName">Full Name as on National ID</label>
                <input type="text" class="form-control" id="saveMoneyName" name="saveMoneyName" required>
            </div>
            <!-- Form group for mobile phone number -->
            <div class="form-group">
                <label for="saveMoneyPhone">Mobile Phone Number</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">+256</span>
                    </div>
                    <input type="text" class="form-control phone-input" id="saveMoneyPhone" name="saveMoneyPhone" required>
                    <!-- Feedback for invalid phone number -->
                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>
            </div>
            <!-- Form group for email -->
            <div class="form-group">
                <label for="saveMoneyEmail">Email</label>
                <input type="email" class="form-control" id="saveMoneyEmail" name="saveMoneyEmail" required>
            </div>
            <!-- Form group for the amount to save -->
            <div class="form-group">
                <label for="saveMoneyAmount">Amount to Save</label>
                <input type="number" class="form-control" id="saveMoneyAmount" name="saveMoneyAmount" required>
            </div>
            <!-- Button to submit the save money form -->
            <button type="submit" class="btn btn-primary">Save Money</button>
        </form>
    </div>

    <!-- jQuery and Bootstrap JavaScript -->
     <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- Bootstrap JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- AOS JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom Script -->
    <script src="script.js"></script>


    <script>
        $(document).ready(function() {
            AOS.init();
            // Apply hover animation to submit button
            $('#saveMoneyForm button[type="submit"]').hover(
                function() {
                    $(this).addClass('animated pulse');
                },
                function() {
                    $(this).removeClass('animated pulse');
                }
            );
            
        });
        function goBack() {
            // Redirect to homepage
            window.location.href = '../services.html'; // Replace '/' with your homepage URL if different

            // Use replaceState to prevent returning to the current page
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        }

    </script>
</body>
</html>
