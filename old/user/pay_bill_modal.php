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
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Bill</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: black;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            overflow-x: hidden;
        }
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: .4em;
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

        .pay-bill-container {
            max-width: 50%;
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
                <a href="save_money_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'save_money_mosal.php') echo 'active'; ?>"><i class="fa fa-piggy-bank"></i> Save Money</a>
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
                <!-- Pay bill form container -->
                <div class="pay-bill-container">
                    <h3>Pay Bill</h3>
                    <!-- Pay bill form -->
                    <form id="payBillForm">
                        <div class="row">
                            <!-- Form group for biller -->
                            <div class="form-group col-md-6">
                                <label for="biller">Biller</label>
                                <select class="form-control" id="biller" name="biller" required></select>
                            </div>
                            <!-- Form group for payment item -->
                            <div class="form-group col-md-6">
                                <label for="paymentItem">Payment Item</label>
                                <select class="form-control" id="paymentItem" name="paymentItem" required></select>
                            </div>
                        </div>
                        <div class="row">
                            <!-- Form group for customer ID -->
                            <div class="form-group col-md-6">
                                <label for="customerId">Customer ID</label>
                                <input type="text" class="form-control" id="customerId" name="customerId" required>
                            </div>
                            <!-- Form group for amount -->
                            <div class="form-group col-md-6">
                                <label for="amount">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                        <!-- Button to submit the pay bill form -->
                        <div class="d-flex justify-content-center mt-3">
                            <button type="submit" class="btn btn-primary pulse">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            fetchBillers();

            // Handle biller selection change
            $('#biller').on('change', function() {
                var billerId = $(this).val();
                fetchPaymentItems(billerId);
            });

            // Handle bill payment form submission
            $('#payBillForm').on('submit', function(event) {
                event.preventDefault();
                validateCustomer();
            });

            function showLoading() {
                $('#loadingOverlay').show();
            }

            function hideLoading() {
                $('#loadingOverlay').hide();
            }

            function fetchBillers() {
                showLoading();
                $.ajax({
                    url: '../get_billers.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        if (response.error) {
                            alert(response.error);
                        } else {
                            var billers = response.BillerList.Category.flatMap(category => category.Billers);
                            populateDropdown('#biller', billers, 'Name', 'Id');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        console.error('Failed to fetch billers:', error);
                        alert('Failed to fetch billers. Please try again later.');
                    }
                });
            }

            function validateCustomer() {
                showLoading();
                const customerId = $('#customerId').val();
                const paymentCode = $('#paymentItem').val();
                
                $.ajax({
                    url: '../validate_customer.php',
                    method: 'POST',
                    data: { customerId: customerId, paymentCode: paymentCode },
                    success: function(response) {
                        hideLoading();
                        const validation = JSON.parse(response);
                        if (validation.status === 'success') {
                            makePayment();
                        } else {
                            alert('Customer validation failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        console.error('Error validating customer:', error);
                    }
                });
            }

            function makePayment() {
                showLoading();
                const paymentData = {
                    biller: $('#biller').val(),
                    amount: $('#amount').val(),
                    customerId: $('#customerId').val(),
                    paymentCode: $('#paymentItem').val()
                };

                $.ajax({
                    url: '../pay_bill.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(paymentData),
                    success: function(response) {
                        hideLoading();
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Payment successful');
                        } else {
                            alert('Payment failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        console.error('Error making payment:', error);
                    }
                });
            }

            function populateDropdown(selector, items, textKey, valueKey) {
                var dropdown = $(selector);
                dropdown.empty();
                dropdown.append('<option selected="true" disabled>Choose...</option>');
                dropdown.prop('selectedIndex', 0);

                items.forEach(function(item) {
                    dropdown.append($('<option></option>').attr('value', item[valueKey]).text(item[textKey]));
                });
            }

            function fetchPaymentItems(billerId) {
                showLoading();
                $.ajax({
                    url: '../get_payment_items.php',
                    method: 'POST',
                    dataType: 'json',
                    data: { billerId: billerId },
                    success: function(response) {
                        hideLoading();
                        if (response.error) {
                            alert(response.error);
                        } else {
                            populateDropdown('#paymentItem', response.PaymentItems, 'Name', 'Id');
                        }
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        console.error('Failed to fetch payment items:', error);
                        alert('Failed to fetch payment items. Please try again later.');
                    }
                });
            }
        });
    </script>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display:none;">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

</body>
</html>
