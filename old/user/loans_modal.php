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

// Get date range parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Prepare the SQL statement with date range filtering
$sql = "SELECT * FROM loans_application WHERE national_id_number = ?";

// Append date range filter if dates are provided
if ($startDate && $endDate) {
    $sql .= " AND application_date BETWEEN ? AND ?";
}

$sql .= " LIMIT ? OFFSET ?";

// Prepare the SQL statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
if ($startDate && $endDate) {
    $stmt->bind_param("sssii", $userNationalId, $startDate, $endDate, $limit, $offset);
} else {
    $stmt->bind_param("sii", $userNationalId, $limit, $offset);
}

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an empty array to store loans
$loans = [];

// Check if there are any loan applications
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
}

// Get distinct statuses for filtering
$statusStmt = $conn->prepare("SELECT DISTINCT status FROM loans_application WHERE national_id_number = ?");

if (!$statusStmt) {
    die("Prepare failed: " . $conn->error);
}

$statusStmt->bind_param("s", $userNationalId);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$statuses = [];

if ($statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statuses[] = $row['status'];
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Applications</title>
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
            background-color: white !important; /* Make navbar transparent */
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
                <a href="save_money_modal.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'save_money_mosal.php') echo 'active'; ?>"><i class="fa fa-piggy-bank"></i> Save Money</a>
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
                <div class="navbar-brand" ><img src="../../assets/images/client-01.png" alt="Logo" class="logo"></div>
                <form action="../logout.php" method="post" class="ml-auto">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </nav>
            <!-- Content -->
            <div class="container-fluid">
                <h1 class="mt-4">Loan Application List</h1>
                <div class="filter-section">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <!-- Filter by Date Range -->
                            <label for="startDate">Start Date:</label>
                            <input class="form-control" id="startDate" type="date">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate">End Date:</label>
                            <input class="form-control" id="endDate" type="date">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" id="applyFilterBtn">Apply Filter</button>
                            <button class="btn btn-secondary" id="clearFilterBtn">Clear Filter</button>
                        </div>
                        <div class="col-md-3">
                            <!-- Search Input -->
                            <input class="form-control" id="searchLoan" type="text" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <!-- Table for Loan Applications -->
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Phone number</th>
                            <th>Email</th>
                            <th>NID Number</th>
                            <th>Loan Amount</th>
                            <th>Application Date</th>
                            <th>Status</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody id="loanTable">
                        <?php foreach ($loans as $loan): ?>
                            <tr id="loan<?php echo $loan['id']; ?>" class="<?php echo $statusClass; ?>">
                                <td><?php echo $loan['id']; ?></td>
                                <td><?php echo $loan['name']; ?></td>
                                <td><?php echo $loan['phone']; ?></td>
                                <td><?php echo $loan['email']; ?></td>
                                <td><?php echo $loan['national_id_number']; ?></td>
                                <td><?php echo $loan['loan_amount']; ?></td>
                                <td><?php echo $loan['application_date']; ?></td>
                                <td id="<?php echo $loan['id']; ?>_status"><?php echo $loan['status']; ?></td>
                                <td><?php echo htmlspecialchars($loan['reason']); ?></td>
                            </tr>


                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Count total number of records
                        $sql_count = "SELECT COUNT(*) AS total FROM loans_application";
                        $result_count = $conn->query($sql_count);
                        $row_count = $result_count->fetch_assoc();
                        $total_pages = ceil($row_count['total'] / $limit);

                        // Previous button
                        $prev_class = ($page == 1) ? 'disabled' : '';
                        echo '<li class="page-item ' . $prev_class . '"><a class="page-link" href="?page=' . ($page - 1) . '">Previous</a></li>';

                        // Numbered page links
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $active_class = ($page == $i) ? 'active' : '';
                            echo '<li class="page-item ' . $active_class . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                        }

                        // Next button
                        $next_class = ($page == $total_pages) ? 'disabled' : '';
                        echo '<li class="page-item ' . $next_class . '"><a class="page-link" href="?page=' . ($page + 1) . '">Next</a></li>';
                        // Close the database connection
                        // Close statements and connection
                        $stmt->close();
                        $statusStmt->close();
                        $conn->close();
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle the Apply Filter button click
            $('#applyFilterBtn').click(function() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();
                var searchTerm = $('#searchLoan').val();
                window.location.href = '?page=1&start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate) + '&search=' + encodeURIComponent(searchTerm);
            });

            // Filter table rows based on search input
            $('#searchLoan').on('keyup', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('#loanTable tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
                });
            });
            // Handle Clear Filter button click
            document.getElementById('clearFilterBtn').addEventListener('click', function() {
                // Clear the filter inputs
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('searchLoan').value = '';

                // Reload the page without any filter parameters
                // Create a URL object to manipulate query parameters
                const url = new URL(window.location.href);
                url.searchParams.delete('start_date');
                url.searchParams.delete('end_date');
                url.searchParams.delete('search');
                
                // Redirect to the URL without filter parameters
                window.location.href = url.toString();
            });

            // Event listeners for input and select elements
            document.getElementById("searchLoan").addEventListener("keyup", filterTable);
            document.getElementById("filterStatus").addEventListener("change", filterTable);

            // Add event listener for keyup event on search input
            document.getElementById('searchLoan').addEventListener('keyup', filterLoans);

        });

    </script>
</body>
</html>
