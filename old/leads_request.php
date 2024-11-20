<?php
require 'test 3/db_connection.php';
session_start();
$loggedInUser = $_SESSION['user_name']; // Replace with your actual session variable
// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number

// Calculate offset
$offset = ($page - 1) * $limit;

// Fetch leads requests with pending status
$sql_leads_requests = "
    SELECT 
        lr.id as request_id, 
        lr.user_id, 
        lr.total_savings, 
        lr.request_status, 
        lr.admin_comments, 
        lr.created_at as request_created_at, 
        lr.reviewed_at,
        u.name, 
        u.email, 
        u.national_id_number
    FROM lead_requests lr
    JOIN users u ON lr.user_id = u.user_id
    WHERE lr.request_status = 'pending'
    LIMIT $limit OFFSET $offset";
$result_leads_requests = $conn->query($sql_leads_requests);

// Initialize an empty array to store leads requests
$leads_requests = [];

// Check if there are any leads requests
if ($result_leads_requests->num_rows > 0) {
    while ($row = $result_leads_requests->fetch_assoc()) {
        // Store user_id from lead_requests table in session
        $_SESSION['user_id_lead'] = $row['user_id'];

        $leads_requests[] = $row;
    }
}

// Get distinct statuses for filtering
$statusSql = "SELECT DISTINCT status FROM loans_application";
$statusResult = $conn->query($statusSql);
$statuses = [];
if ($statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $statuses[] = $row['status'];
    }
}

// Fetch the total number of pending requests
$sql_count = "SELECT COUNT(*) AS total FROM lead_requests WHERE request_status = 'pending'";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_pages = ceil($row_count['total'] / $limit);

// Fetch all pending loan applications for dropdown
$loanAppSql = "SELECT * FROM loans_application WHERE status = 'pending'";
$loanAppResult = $conn->query($loanAppSql);
$loanApplications = [];
if ($loanAppResult->num_rows > 0) {
    while ($row = $loanAppResult->fetch_assoc()) {
        $loanApplications[] = $row;
    }
}

// Fetch total savings amount for each user
$savingsSql = "
    SELECT 
        u.user_id as user_id,
        SUM(s.amount) as total_savings
    FROM users u
    LEFT JOIN savings s ON u.national_id_number = s.id_number
    GROUP BY u.user_id";
$savingsResult = $conn->query($savingsSql);
$savings = [];
if ($savingsResult->num_rows > 0) {
    while ($row = $savingsResult->fetch_assoc()) {
        $savings[$row['user_id']] = $row['total_savings'];
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        /* Retain the existing CSS */
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
        /* Additional styling for dropdown and modals */
        .modal .form-control {
            border-radius: 0.25rem;
        }
        .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
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
                    $userImage = isset($_SESSION['user_gender']) && $_SESSION['user_gender'] === 'female' ? 'female.jpg' : 'male.jpg';
                ?>
                <img src="<?php echo $userImage; ?>" alt="User Photo">
                <h5><?php echo $loggedInUser; ?></h5>
            </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard-admin.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard-admin.php') echo 'active'; ?>"><i class="fa fa-home"></i> Home</a>
                <a href="user_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'user_list.php') echo 'active'; ?>"><i class="fa fa-users"></i> User List</a>
                <a href="loan_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'loan_list.php') echo 'active'; ?>"><i class="fa fa-file"></i> Loan Application List</a>
                <a href="savings_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'savings_list.php') echo 'active'; ?>"><i class="fas fa-piggy-bank"></i> Savings List</a>
                <a href="leads_request.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'leads_request.php') echo 'active'; ?>"><i class="fas fa-file-alt"></i> Leads Requests</a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <img src="../assets/images/client-01.png" alt="Logo" class="logo">
                <a href="logout.php" class="btn btn-danger ml-auto">Logout</a>
            </nav>
            <div class="container-fluid">
                <h1 class="mt-4">Leads Requests</h1>
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-file-alt"></i> Leads Requests List
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>User Email</th>
                                    <th>User National ID</th>
                                    <th>Total Savings</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Reviewed At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($leads_requests) > 0): ?>
                                    <?php foreach ($leads_requests as $request): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                            <td><?php echo htmlspecialchars($request['user_id']); ?></td>
                                            <td><?php echo htmlspecialchars($request['name']); ?></td>
                                            <td><?php echo htmlspecialchars($request['email']); ?></td>
                                            <td><?php echo htmlspecialchars($request['national_id_number']); ?></td>
                                            <td><?php echo number_format($savings[$request['user_id']] ?? 0, 2); ?></td>
                                            <td><?php echo htmlspecialchars($request['request_status']); ?></td>
                                            <td><?php echo htmlspecialchars($request['request_created_at']); ?></td>
                                            <td><?php echo htmlspecialchars($request['reviewed_at'] ?? 'N/A'); ?></td>
                                            <td>
                                                <button class="btn btn-success btn-sm approve-btn" data-id="<?php echo htmlspecialchars($request['request_id']); ?>" data-user-id="<?php echo htmlspecialchars($request['user_id']); ?>">Approve</button>
                                                <button class="btn btn-danger btn-sm deny-btn" data-id="<?php echo htmlspecialchars($request['request_id']); ?>">Deny</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10">No lead requests for now.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a></li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Approval/Deny Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="actionModalLabel">Add Comment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="actionForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comment">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <div class="form-group" id="loanApplicationGroup" style="display: none;">
                            <label for="loanApplication">Select Loan Application</label>
                            <select class="form-control" id="loanApplication" name="loan_application">
                                <?php foreach ($loanApplications as $app): ?>
                                    <option value="<?php echo $app['id']; ?>">
                                        <?php echo $app['name']; ?> / <?php echo $app['email']; ?> / <?php echo $app['national_id_number']; ?> / <?php echo number_format($app['loan_amount'], 2); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>
                        <input type="hidden" id="requestId" name="request_id">
                        <input type="hidden" id="actionType" name="action_type">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Done</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.approve-btn').on('click', function() {
                $('#actionModal').modal('show');
                $('#loanApplicationGroup').show();
                $('#actionType').val('approve');
                $('#requestId').val($(this).data('id'));
            });

            $('.deny-btn').on('click', function() {
                $('#actionModal').modal('show');
                $('#loanApplicationGroup').hide();
                $('#actionType').val('deny');
                $('#requestId').val($(this).data('id'));
            });

            $('#actionForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'process_request.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response); // Check what the server is returning
                        // Refresh the table or redirect as needed
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + xhr.responseText); // Display the error message
                    }
                });
            });


            $('#menu-toggle').click(function() {
                $('#sidebar-wrapper').toggleClass('toggled');
                $('#page-content-wrapper').toggleClass('toggled');
            });
        });
    </script>
</body>
</html>
