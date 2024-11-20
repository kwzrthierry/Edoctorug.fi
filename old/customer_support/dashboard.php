<?php
require '../test 3/db_connection.php';

// Fetch statistics
$approvedCount = $conn->query("SELECT COUNT(*) as count FROM loans_application WHERE status='approved'")->fetch_assoc()['count'];
$pendingCount = $conn->query("SELECT COUNT(*) as count FROM loans_application WHERE status='pending'")->fetch_assoc()['count'];
$deniedCount = $conn->query("SELECT COUNT(*) as count FROM loans_application WHERE status='denied'")->fetch_assoc()['count'];

// Fetch total savings and total approved loan amount
$totalSavingsResult = $conn->query("SELECT SUM(amount) as total FROM savings")->fetch_assoc();
$totalSavings = $totalSavingsResult['total'] ? $totalSavingsResult['total'] : 0;

$totalApprovedLoansResult = $conn->query("SELECT SUM(loan_amount) as total FROM loans_application WHERE status='approved'")->fetch_assoc();
$totalApprovedLoans = $totalApprovedLoansResult['total'] ? $totalApprovedLoansResult['total'] : 0;

// Fetch the logged in user's information (assuming user info is stored in a session)
session_start();
$loggedInUser = $_SESSION['user_name']; // Replace with your actual session variable


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: black;
        }
        .modal-lg {
            max-width: 90%; /* Increase this percentage as desired */
        }
        #page-content-wrapper.toggled {
            margin-left: 0;
        }
        #sidebar-wrapper.toggled {
            margin-left: -250px;
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
            color: white;
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
            color: black;
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
            background-color: white !important; /* Make navbar transparent */
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
            text-align: center;
            margin-bottom: 20px;
        }
        .modal-header {
            border-bottom: 0;
        }

        .modal-header .close {
            color: #fff;
            opacity: 1;
            font-size: 1.5rem;
        }

        .modal-content {
            border-radius: 15px;
        }

        .table {
            margin: 0;
            border-radius: 8px;
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
        #sidebarToggle {
            color: #fff;
            font-size: 24px;
            cursor: pointer;
        }
        #sidebarToggle:hover {
            color: #ccc;
        }
        .logo {
            margin-bottom: 20px;
            height: 60px;
            display: block;
/*            margin-left: auto;
            margin-right: auto;*/
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
                <h5><?php echo $loggedInUser; ?></h5>
            </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard.php') echo 'active'; ?>"><i class="fa fa-home"></i> Home</a>
                <a href="user_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'user_list.php') echo 'active'; ?>"><i class="fa fa-users"></i> User List</a>
                <a href="loan_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'loan_list.php') echo 'active'; ?>"><i class="far fa-file"></i> Loan Application List</a>
                <a href="savings_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'savings_list.php') echo 'active'; ?>"><i class="fas fa-piggy-bank"></i> Savings List</a>
            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <img src="../../assets/images/client-01.png" alt="Logo" class="logo">
                <form action="../logout.php" method="post" class="ml-auto">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </nav>
            <div class="container-fluid">
                <h1 class="mt-4">Dashboard</h1>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card text-white bg-success mb-3" data-toggle="modal" data-target="#approvedLoansModal" style="cursor: pointer;">
                            <div class="card-header"><i class="fa fa-check-circle"></i> Approved Loans</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $approvedCount; ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card text-white bg-warning mb-3" data-toggle="modal" data-target="#pendingLoansModal" style="cursor: pointer;">
                            <div class="card-header"><i class="fa fa-hourglass-half"></i> Pending Loans</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $pendingCount; ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card text-white bg-danger mb-3" data-toggle="modal" data-target="#deniedLoansModal" style="cursor: pointer;">
                            <div class="card-header"><i class="fa fa-times-circle"></i> Denied Loans</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $deniedCount; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header"><i class="fa fa-piggy-bank"></i> Total Savings</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo number_format($totalSavings, 2) . ' UGX'; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header"><i class="fa fa-coins"></i> Total Approved Loans</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo number_format($totalApprovedLoans, 2) . ' UGX'; ?></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Approved Loans Modal -->
<div class="modal fade" id="approvedLoansModal" tabindex="-1" role="dialog" aria-labelledby="approvedLoansModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvedLoansModalLabel">Approved Loans</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                $approvedLoans = $conn->query("SELECT * FROM loans_application WHERE status='approved'");
                if ($approvedLoans->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>National ID</th><th>Loan Amount</th><th>Application Date</th><th>Reason</th></tr></thead><tbody>';
                    while ($row = $approvedLoans->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['phone'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['national_id_number'] . '</td>';
                        echo '<td>' . number_format($row['loan_amount'], 2) . '</td>';
                        echo '<td>' . $row['application_date'] . '</td>';
                        echo '<td>' . $row['reason'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo "<p>No approved loans found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Repeat the same changes for Pending and Denied Loans Modals -->

<!-- Pending Loans Modal -->
<div class="modal fade" id="pendingLoansModal" tabindex="-1" role="dialog" aria-labelledby="pendingLoansModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="pendingLoansModalLabel">Pending Loans</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                $pendingLoans = $conn->query("SELECT * FROM loans_application WHERE status='pending'");
                if ($pendingLoans->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>National ID</th><th>Loan Amount</th><th>Application Date</th><th>Reason</th></tr></thead><tbody>';
                    while ($row = $pendingLoans->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['phone'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['national_id_number'] . '</td>';
                        echo '<td>' . number_format($row['loan_amount'], 2) . '</td>';
                        echo '<td>' . $row['application_date'] . '</td>';
                        echo '<td>' . $row['reason'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo "<p>No pending loans found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Denied Loans Modal -->
<div class="modal fade" id="deniedLoansModal" tabindex="-1" role="dialog" aria-labelledby="deniedLoansModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deniedLoansModalLabel">Denied Loans</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php
                $deniedLoans = $conn->query("SELECT * FROM loans_application WHERE status='denied'");
                if ($deniedLoans->num_rows > 0) {
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-bordered table-striped">';
                    echo '<thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>National ID</th><th>Loan Amount</th><th>Application Date</th><th>Reason</th></tr></thead><tbody>';
                    while ($row = $deniedLoans->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['id'] . '</td>';
                        echo '<td>' . $row['name'] . '</td>';
                        echo '<td>' . $row['phone'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['national_id_number'] . '</td>';
                        echo '<td>' . number_format($row['loan_amount'], 2) . '</td>';
                        echo '<td>' . $row['application_date'] . '</td>';
                        echo '<td>' . $row['reason'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo "<p>No denied loans found.</p>";
                }
                // Close the database connection
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2024 Edoctorug. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#sidebarToggle").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("toggled");
                if ($("#wrapper").hasClass("toggled")) {
                    $("#sidebarToggle i").removeClass("fa-times").addClass("fa-bars");
                } else {
                    $("#sidebarToggle i").removeClass("fa-bars").addClass("fa-times");
                }
            });
        });
    </script>
</body>
</html>
