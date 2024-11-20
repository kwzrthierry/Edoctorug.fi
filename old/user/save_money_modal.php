<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_name'])) {
    header("Location: ../login_modal.php");
    exit();
}

// Database connection setup
require '../test 3/db_connection.php';  // Adjust this based on your actual database connection file path
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Escape user input for safety
$user_name = mysqli_real_escape_string($conn, $_SESSION['national_id']);

// Fetch user data
$sql = "SELECT * FROM users WHERE national_id_number = '$user_name'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $user = mysqli_fetch_assoc($result);
    if (!$user) {
        die("User not found.");
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Money</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: black;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        #sidebar-wrapper {
            width: 250px;
            background-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            z-index: 1000;
            color: black;
            transition: all 0.3s;
            position: fixed;
            height: 100%;
            backdrop-filter: blur(10px);
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        #sidebar-wrapper .sidebar-heading {
            padding: 0.875rem 1.25rem;
            font-size: 1.2rem;
        }

        #sidebar-wrapper .list-group-item {
            border-color: transparent;
            transition: all 0.3s;
            background: none;
        }

        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(45deg, #007bff, #00c6ff);
            color: white;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: black;
            color: white;
        }

        #page-content-wrapper {
            flex: 1;
            transition: margin-left 0.3s;
            background-color: #f8f9fa;
            padding: 20px;
            margin-left: 250px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: white;
        }

        .navbar .welcome-text {
            color: #333;
            font-size: 1.5rem;
            font-weight: bold;
            margin-left: 20px;
        }

        .save-money-container {
            max-width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        .btn-primary {
            border-radius: 30px;
            background: linear-gradient(to right, #007bff, #0056b3);
            border: none;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #0056b3, #007bff);
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

        @media (max-width: 768px) {
            #wrapper {
                flex-direction: column;
            }
            #page-content-wrapper {
                margin-left: 0;
                margin-top: 70px;
            }
            #sidebar-wrapper {
                height: auto;
                width: 100%;
                position: relative;
            }
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
        }
        .user-info img {
            width: 100px;
            height: 100px;
            border-radius: 10%;
        }
        .user-info h5 {
            margin-top: 10px;
            color: black;
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

        .form-row {
            margin-bottom: 1rem;
        }

        /* Centered Modal */
        .modal-dialog {
            margin: 1.75rem auto;
        }
        .logo {
            margin-bottom: 20px;
            height: 60px;
            display: block;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div class="bg-light border-right" id="sidebar-wrapper">
            <div class="sidebar-heading">
                <div class="user-info">
                    <?php
                        // Check if the user's gender is stored in the session
                        $userImage = isset($_SESSION['user_gender']) && $_SESSION['user_gender'] === 'female' ? '../female.jpg' : '../male.jpg';
                    ?>
                    <img src="<?php echo $userImage; ?>" alt="User Photo">
                    <h5><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>"><i class="fa fa-home"></i> Home</a>
                <a href="apply_loan_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'apply_loan_modal.php') echo 'active'; ?>"><i class="fa fa-money-bill-alt"></i> Apply Loan</a>
                <a href="pay_bill_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'pay_bill_modal.php') echo 'active'; ?>"><i class="fa fa-credit-card"></i> Pay Bill</a>
                <a href="save_money_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'save_money_modal.php') echo 'active'; ?>"><i class="fa fa-piggy-bank"></i> Save Money</a>
                <a href="loans_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'loans_modal.php') echo 'active'; ?>"><i class="fas fa-file"></i> Loans</a>
                <a href="savings.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'savings.php') echo 'active'; ?>"><i class="fas fa-piggy-bank"></i> Savings</a>
                <a href="bills.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'bills.php') echo 'active'; ?>"><i class="fas fa-file-invoice-dollar"></i> Bills</a>
                <a href="pay_loan.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'pay_loan.php') echo 'active'; ?>"><i class="fa fa-credit-card"></i> Pay Loan</a>
            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="navbar-brand"><img src="../../assets/images/client-01.png" alt="Logo" class="logo"></div>
                <form id="logoutForm" action="../logout.php" method="post" class="ml-auto">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </nav>
            <div class="container-fluid d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <!-- Save money form container -->
                <div class="save-money-container">
                    <h3>Save Money</h3>
                    <!-- Save money form -->
                    <form id="saveMoneyForm" action="save_money_modal.php" method="POST">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="saveMoneyName">Full Name as on National ID</label>
                                    <input type="text" class="form-control" id="saveMoneyName" name="saveMoneyName" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="saveMoneyEmail">Email</label>
                                    <input type="email" class="form-control" id="saveMoneyEmail" name="saveMoneyEmail" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="saveMoneyAmount">Amount to Save</label>
                                    <input type="number" class="form-control" id="saveMoneyAmount" name="saveMoneyAmount" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="saveMoneyPhone">Mobile Phone Number</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">+256</span>
                                        </div>
                                        <input type="text" class="form-control phone-input" id="saveMoneyPhone" name="saveMoneyPhone" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                                        <!-- Feedback for invalid phone number -->
                                        <div class="invalid-feedback">Please enter a valid phone number.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            <button type="submit" class="btn btn-primary pulse">Save Money</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Handle save money form submission
            $('#saveMoneyForm').submit(function(e) {
                e.preventDefault();

                var formData = new FormData(this);

                $.ajax({
                    url: 'save_money.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.trim() === 'success') {
                            // Show the success modal if the response is 'success'
                            $('#saveMoneySuccessModal').modal('show');
                        } else {
                            // Handle other responses or errors
                            alert(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while saving money.');
                    }
                });
            });
        });
    </script>

    <!-- Modal for Success Message -->
    <div class="modal fade" id="saveMoneySuccessModal" tabindex="-1" role="dialog" aria-labelledby="saveMoneySuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saveMoneySuccessModalLabel">Money Saved Successfully</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Your money has been saved successfully.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to show success modal if session variable is set -->
    <?php
    if (isset($_SESSION['save_money_success']) && $_SESSION['save_money_success']) {
        echo '<script type="text/javascript">
                $(document).ready(function(){
                    $("#saveMoneySuccessModal").modal("show");
                });
              </script>';
        unset($_SESSION['save_money_success']); // Unset the session variable after displaying the modal
        echo 'success'; // Echo 'success' to AJAX success function
    }
    ?>

</body>
</html>