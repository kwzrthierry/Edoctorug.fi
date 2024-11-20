<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_name']) || !isset($_SESSION['national_id'])) {
    die("User is not logged in.");
}

$loggedInUser = $_SESSION['user_name'];
$userNationalId = $_SESSION['national_id']; // Fetch the national ID from session

require '../test 3/db_connection.php';

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Current page number

// Calculate offset
$offset = ($page - 1) * $limit;

// Get search and date range parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Prepare the SQL statement with date range and search filtering
$sql = "SELECT * FROM bills_records WHERE name LIKE ?";

// Append date range filter if dates are provided
if ($startDate && $endDate) {
    $sql .= " AND date BETWEEN ? AND ?";
}

$sql .= " LIMIT ? OFFSET ?";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$searchParam = "%$search%";
if ($startDate && $endDate) {
    $stmt->bind_param("ssii", $searchParam, $startDate, $endDate, $limit, $offset);
} else {
    $stmt->bind_param("sii", $searchParam, $limit, $offset);
}

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an empty array to store bills
$bills = [];

// Check if there are any bills records
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bills[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bills</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../styles.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: black;
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
            background-color: white !important;
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
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333 !important;
        }
        .pagination {
            justify-content: center;
        }
        .pagination .page-link {
            color: #007bff; /* Blue page link */
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff; /* Blue background for active page */
            border-color: #007bff;
        }
        .filter-section {
            margin-bottom: 20px;
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-section label {
            font-weight: bold;
        }
        .filter-section .btn {
            margin-right: 10px;
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
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="navbar-brand"><img src="../../assets/images/client-01.png" alt="Logo" class="logo"></div>
                <form id="logoutForm" action="../logout.php" method="post" class="ml-auto">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </nav>

            <div class="container-fluid">
                <h1 class="mt-4">Bills</h1>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" action="bills.php">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                            </div>
                            <div class="form-group col-md-4 align-self-end">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="bills.php" class="btn btn-secondary">Clear</a>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="search">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Bills Records Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Bill ID</th>
                                <th>Name</th>
                                <th>Bill Name</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bills) > 0): ?>
                                <?php foreach ($bills as $bill): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($bill['bill_id']); ?></td>
                                        <td><?php echo htmlspecialchars($bill['name']); ?></td>
                                        <td><?php echo htmlspecialchars($bill['bill_name']); ?></td>
                                        <td><?php echo htmlspecialchars($bill['date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No bills records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php
                // Get the total number of records for pagination
                $countSql = "SELECT COUNT(*) AS total FROM bills_records WHERE name LIKE ?";
                if ($startDate && $endDate) {
                    $countSql .= " AND date BETWEEN ? AND ?";
                }

                $countStmt = $conn->prepare($countSql);
                if ($startDate && $endDate) {
                    $countStmt->bind_param("sss", $searchParam, $startDate, $endDate);
                } else {
                    $countStmt->bind_param("s", $searchParam);
                }

                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $totalCount = $countResult->fetch_assoc()['total'];

                $totalPages = ceil($totalCount / $limit);
                ?>

                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $("#menu-toggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
