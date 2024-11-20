<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['national_id'])) {
    header("Location: ../login_modal.php");
    exit();
}

// Database connection setup
require '../test 3/db_connection.php';  // Adjust this based on your actual database connection file path
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

require '../penalty.php';

// Escape user input for safety
$national_id = mysqli_real_escape_string($conn, $_SESSION['national_id']);

deduct_loan($national_id);

// Fetch total savings for the current user
$savings_query = "SELECT SUM(amount) AS total_savings FROM savings WHERE id_number = '$national_id'";
$savings_result = mysqli_query($conn, $savings_query);
$total_savings = $savings_result ? mysqli_fetch_assoc($savings_result)['total_savings'] : 0;

// Fetch total loan applications for the current user
$loans_query = "SELECT COUNT(*) AS total_loans FROM loans_application WHERE national_id_number = '$national_id'";
$loans_result = mysqli_query($conn, $loans_query);
$total_loans = $loans_result ? mysqli_fetch_assoc($loans_result)['total_loans'] : 0;

// Fetch loan details for the current user
$loan_details_query = "SELECT loan_id, loanee_name, due_date, approval_date, loan_amount, approved_by FROM due_loans WHERE user_id = '$national_id'";
$loan_details_result = mysqli_query($conn, $loan_details_query);
$loan_details = $loan_details_result ? mysqli_fetch_all($loan_details_result, MYSQLI_ASSOC) : [];

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: black;
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            animation: fadeIn 1.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            animation: slideIn 0.7s;
        }
        @keyframes slideIn {
            from { margin-left: -100%; }
            to { margin-left: 0; }
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
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background-color: rgba(255, 255, 255, 0.2);
            border-bottom: none;
            color: #000;
            display: flex;
            align-items: center;
        }
        .card-header i {
            margin-right: 10px;
        }
        .card-body {
            padding: 1.25rem;
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
        .footer {
            position: relative;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 14px;
            color: #555;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px 0;
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
        .stat-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            background: linear-gradient(to right, #00c6ff, #007bff);
            color: white;
            margin-bottom: 20px;
        }
        .stat-card:hover {
            transform: scale(1.05);
        }
        .stat-card .card-body {
            padding: 20px;
        }
        .stat-card .stat-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card .stat-desc {
            font-size: 0.9rem;
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
                    <h5><?php echo $_SESSION['user_name']; ?></h5>
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
            <div class="container-fluid">
                <div class="row mt-4">
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-title">Total Savings</div>
                                <div class="stat-value"><?php echo number_format($total_savings, 2); ?> UGX</div>
                                <div class="stat-desc">Total savings made by you.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-title">Total Loan Applications</div>
                                <div class="stat-value"><?php echo $total_loans; ?></div>
                                <div class="stat-desc">Total loan applications made by you.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-title">Total Bill Payments</div>
                                <div class="stat-value">0</div> <!-- Placeholder value, replace with actual data if available -->
                                <div class="stat-desc">Total bill payments made by you.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <h3>Loan Details</h3>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Loanee Name</th>
                                        <th>Due Date</th>
                                        <th>Approval Date</th>
                                        <th>Approved By</th>
                                        <th>Amount due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($loan_details)) : ?>
                                        <?php foreach ($loan_details as $loan) : ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['loanee_name']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['approval_date']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['approved_by']); ?></td>
                                                <td><?php echo htmlspecialchars($loan['loan_amount']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No loan details available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                &copy; <?php echo date("Y"); ?> Loan Management System. All rights reserved.
            </footer>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>
</body>
</html>
