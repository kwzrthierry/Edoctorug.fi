<?php
session_start();
require '../test 3/db_connection.php';

// Set timeout duration (e.g., 1800 seconds = 30 minutes)
$timeout_duration = 1800;

// Check for inactivity timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Last request was more than the timeout duration ago
    session_unset();     // Unset $_SESSION variable for this page
    session_destroy();   // Destroy session data
    echo "<script>alert('Session expired due to inactivity. Please log in again.');</script>";
    header("Location: ../login_modal.php");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['national_id'])) {
    echo "<script>alert('You have to log in to access this page.');</script>";
    header("Location: ../login_modal.php");
    exit();
}

// Fetch user ID and national ID from the session
$user_NID = $_SESSION['national_id'];
$userID = $_SESSION['user_id'];

// Escape user input for safety
$user_NID = mysqli_real_escape_string($conn, $user_NID);

// Fetch user data
$sql = "SELECT * FROM users WHERE national_id_number = '$user_NID'";
$result = mysqli_query($conn, $sql);

if ($result) {
    $user = mysqli_fetch_assoc($result);
    if (!$user) {
        die("User not found.");
    }
} else {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch user's other details
$nameResult = $conn->query("SELECT name FROM users WHERE national_id_number = '$user_NID'");
$name = $nameResult ? ($nameRow = $nameResult->fetch_assoc()) ? $nameRow['name'] : null : null;

// Fetch total leads
$totalLeadsResult = $conn->query("SELECT COUNT(*) as count FROM leads WHERE user_id='$userID'");
$totalLeads = $totalLeadsResult ? $totalLeadsResult->fetch_assoc()['count'] : 0;

// Fetch total outstanding loans
$totalOutstandingLoansResult = $conn->query("SELECT SUM(due_amount) as total FROM leads WHERE user_id='$userID' AND status='approved'");
$outstandingLoans = $totalOutstandingLoansResult ? ($totalOutstandingLoansResult->fetch_assoc()['total'] ?: 0) : 0;

// Fetch total lead requests
$totalLeadRequestsResult = $conn->query("SELECT COUNT(*) as count FROM lead_requests WHERE user_id='$userID'");
$totalLeadRequests = $totalLeadRequestsResult ? $totalLeadRequestsResult->fetch_assoc()['count'] : 0;

// Fetch user's savings
$userSavingsResult = $conn->query("SELECT SUM(amount) as total FROM savings WHERE id_number='$user_NID'");
$totalSavings = $userSavingsResult ? ($userSavingsResult->fetch_assoc()['total'] ?: 0) : 0;

// Fetch lead requests
$leadRequestsResult = $conn->query("SELECT * FROM lead_requests WHERE user_id='$userID'");
$leadRequests = $leadRequestsResult ? $leadRequestsResult->fetch_all(MYSQLI_ASSOC) : [];

// Fetch leads and their recent messages
$leadsQuery = "
    SELECT l.*, m.message AS recent_message 
    FROM leads l 
    LEFT JOIN (
        SELECT lead_national_id, message
        FROM messages
        WHERE (lead_national_id, time_sent) IN (
            SELECT lead_national_id, MAX(time_sent)
            FROM messages
            GROUP BY lead_national_id
        )
    ) m ON l.lead_national_id = m.lead_national_id
    WHERE l.user_id = ?
";

// Prepare the statement
$stmt = $conn->prepare($leadsQuery);

// Check if prepare() failed
if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}

// Bind parameters
$stmt->bind_param('i', $userID);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Fetch all leads with recent messages
$leads = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loaner Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-body {
            text-align: center;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
            background: #f8f9fa;
        }

        .sidebar-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background-color: #13274F;
        }

        .sidebar-heading h5 {
            margin: 0;
            color: white;
        }

        #toggle-sidebar {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        #sidebar-wrapper {
            width: 250px;
            background: black;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            color: white;
            transition: all 0.3s ease;
            position: fixed;
            height: 100%;
            backdrop-filter: blur(8px);
            overflow: hidden;
        }

        #sidebar-wrapper.collapsed {
            width: 70px;
        }

        #sidebar-wrapper .sidebar-heading {
            text-align: center;
            padding: 20px;
        }

        #sidebar-wrapper .list-group-item {
            background: transparent;
            color: white;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none; /* Remove underline */
        }

        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            background: linear-gradient(135deg, #007bff, #ffc107);
            color: white;
        }

        #sidebar-wrapper .list-group-item .fa,
        #sidebar-wrapper .list-group-item.collapsed .fa {
            color: white;
            font-size: 18px;
        }

        #sidebar-wrapper.collapsed .list-group-item {
            text-align: center;
            padding: 15px 0;
        }

        #sidebar-wrapper.collapsed .list-group-item span {
            display: none;
        }

        #sidebar-wrapper.collapsed .sidebar-heading h5 {
            display: none;
        }

        #page-content-wrapper {
            flex: 1;
            transition: margin-left 0.3s ease;
            padding: 20px;
            margin-left: 250px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: #ffffff;
        }

        .navbar .welcome-text {
            color: #007bff;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .navbar .notification-icon {
            position: relative;
            display: inline-block;
        }

        .navbar .notification-icon .badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #ffc107;
            color: white;
            border-radius: 50%;
            padding: 0 6px;
            font-size: 12px;
        }

        .card {
            border-radius: 15px;
            box-shadow: none;
            transition: none;
            height: 100%;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: none;
            box-shadow: none;
        }

        .card-header {
            font-size: 1.1rem;
            font-weight: bold;
        }

        .card-body {
            padding: 20px;
        }

        .bg-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .bg-success {
            background: linear-gradient(135deg, #28a745, #218838);
        }

        .bg-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .bg-info {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
        }

        .bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .bg-dark {
            background: linear-gradient(135deg, #343a40, #23272b);
        }

        .text-dark {
            color: #343a40 !important;
        }

        .text-white {
            color: #ffffff !important;
        }

        

        .hidden-section {
            display: none;
        }



        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        

        /* Lead List */
        .list-leads {
            margin-top: 20px;
        }

        .list-lead-item {
            padding: 10px;
            margin-bottom: 10px;
            background: #1a1a1a; /* Darker black */
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .list-lead-item:hover {
            background: #333333; /* Slightly lighter black */
        }

        .lead-name {
            font-weight: bold;
            font-size: 1rem;
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
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">
                <button id="toggle-sidebar"><i class="fas fa-bars"></i></button>
                <h5>Admin Dashboard</h5>
            </div>
            <div class="list-group">
                <a href="#" class="list-group-item active" data-target="#overview-section">
                    <i class="fa fa-tachometer-alt"></i>
                    <span class="ml-2">Dashboard</span>
                </a>
                <a href="save_money.php" class="list-group-item" data-target="#save-money-section">
                    <i class="fas fa-piggy-bank"></i>
                    <span class="ml-2">Save Money</span>
                </a>
                <a href="#" class="list-group-item" data-target="#lead-requests-section">
                    <i class="fa fa-user-plus"></i>
                    <span class="ml-2">Lead Requests</span>
                </a>
                <a href="#" class="list-group-item" data-target="#leads-section">
                    <i class="fa fa-users"></i>
                    <span class="ml-2">Leads</span>
                </a>
                <a href="#" class="list-group-item" data-toggle="modal" data-target="#logoutModal">
                    <i class="fa fa-sign-out-alt"></i>
                    <span class="ml-2" >Logout</span>
                </a>
            </div>

        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <img src="../../assets/images/client-01.png" alt="Logo" class="logo">
                <div class="ml-auto">
                    <span class="welcome-text"><?php echo htmlspecialchars($name); ?></span>
                </div>
            </nav>
            <!-- Overview Section -->
            <div id="overview-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-header">Total Lead Requests</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $totalLeadRequests; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-header">Total Leads</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $totalLeads; ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-header">Outstanding Loans</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $outstandingLoans; ?> Amount Due</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-header">Total Savings</div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $totalSavings; ?></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-danger text-white">Leads Due in Less than 10 Days</div>
                            <div class="card-body">
                                <?php
                                // Fetch leads due in less than 10 days
                                $today = date('Y-m-d');
                                $dateLimit = date('Y-m-d', strtotime('+10 days'));
                                $dueLeadsResult = $conn->query("
                                    SELECT l.id, l.due_date, l.loan_amount, u.name
                                    FROM leads l
                                    JOIN users u ON l.user_id = u.national_id_number
                                    WHERE l.due_date BETWEEN '$today' AND '$dateLimit'
                                    AND l.status = 'approved'
                                    AND DATEDIFF(l.due_date, '$today') > 0
                                    ORDER BY l.due_date
                                ");
                                
                                if ($dueLeadsResult && $dueLeadsResult->num_rows > 0): ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Loanee Name</th>
                                                <th>Due Date</th>
                                                <th>Days Left</th>
                                                <th>Amount Due</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $dueLeadsResult->fetch_assoc()): 
                                                $dueDate = new DateTime($row['due_date']);
                                                $todayDate = new DateTime($today);
                                                $daysLeft = $dueDate->diff($todayDate)->days;
                                                $color = $daysLeft <= 3 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : 'text-success');
                                            ?>
                                                <tr class="<?php echo $color; ?>">
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                                    <td><?php echo $daysLeft; ?> days</td>
                                                    <td><?php echo htmlspecialchars($row['loan_amount']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No leads are due in less than 10 days.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-dark text-white">Leads Already Due</div>
                            <div class="card-body">
                                <?php
                                // Fetch leads already due
                                $dueToday = date('Y-m-d');
                                $dueTodayLeadsResult = $conn->query("
                                    SELECT l.id, l.due_date, l.loan_amount, u.name
                                    FROM leads l
                                    JOIN users u ON l.user_id = u.national_id_number
                                    WHERE l.due_date = '$dueToday'
                                    AND l.status = 'approved'
                                ");
                                
                                if ($dueTodayLeadsResult && $dueTodayLeadsResult->num_rows > 0): ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Loanee Name</th>
                                                <th>Amount Due</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $dueTodayLeadsResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['loan_amount']); ?></td>
                                                    <td>
                                                        <button class="btn btn-warning btn-sm">Escalate</button>
                                                        <button class="btn btn-info btn-sm">Talk to Them</button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>No leads are due today.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="escalation-form mt-3">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#confirmModal">
                        Submit Lead Request
                    </button>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmModalLabel">Confirm Lead Request</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to submit a lead request?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmSubmit">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="save-money-container hidden-section" id="save-money-section">
                <h3>Save Money</h3>
                <!-- Save money form -->
                <form id="saveMoneyForm" method="POST">
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
            <!-- Lead Requests Section -->
            <div id="lead-requests-section" class="hidden-section">
                <h2>Lead Requests</h2>
                <?php if (!empty($leadRequests)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Total Savings</th>
                                <th>Status</th>
                                <th>Admin Comments</th>
                                <th>Created At</th>
                                <th>Reviewed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leadRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['id']); ?></td>
                                    <td><?php echo htmlspecialchars($request['total_savings']); ?></td>
                                    <td><?php echo htmlspecialchars($request['request_status']); ?></td>
                                    <td><?php echo htmlspecialchars($request['admin_comments']) ?: 'Not yet looked at'; ?></td>
                                    <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                    <td><?php echo htmlspecialchars($request['reviewed_at']) ?: 'Not yet looked at'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You have no lead requests yet.</p>
                <?php endif; ?>
            </div>

            <!-- Leads Section -->
            <div id="leads-section" class="hidden-section">
                <h2>Leads</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Name</th>
                            <th>Loanee National ID</th>
                            <th>Phone</th>
                            <th>Loan Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leads-table-body">
                        <!-- Rows will be populated dynamically via JavaScript -->
                    </tbody>
                </table>
                <p id="no-leads-message" style="display: none;">You have received no leads yet.</p>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are you sure you want to logout?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelLogout">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmLogout">Logout</button>
          </div>
        </div>
      </div>
    </div>
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
    <!-- Insufficient Savings Modal -->
    <div class="modal fade" id="insufficientSavingsModal" tabindex="-1" aria-labelledby="insufficientSavingsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="insufficientSavingsModalLabel">Insufficient Savings</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    The savings are insufficient to cover the loan amount. Please review the savings balance.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- General Error Modal (Added) -->
    <div class="modal fade" id="generalErrorModal" tabindex="-1" aria-labelledby="generalErrorModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger" id="generalErrorModalLabel">Error</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Something went wrong. Please try again later.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>


    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggling
            const links = document.querySelectorAll('#sidebar-wrapper .list-group-item');
            const sections = document.querySelectorAll('#page-content-wrapper > div');
            const toggleSidebar = document.getElementById('toggle-sidebar');
            let isSidebarCollapsed = false;

            links.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.preventDefault();
                    const target = document.querySelector(this.getAttribute('data-target'));

                    sections.forEach(section => {
                        section.classList.toggle('hidden-section', section !== target);
                    });

                    links.forEach(link => link.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            toggleSidebar.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar-wrapper');
                const pageContent = document.getElementById('page-content-wrapper');

                if (isSidebarCollapsed) {
                    sidebar.classList.remove('collapsed');
                    pageContent.style.marginLeft = '250px';
                    isSidebarCollapsed = false;
                } else {
                    sidebar.classList.add('collapsed');
                    pageContent.style.marginLeft = '70px';
                    isSidebarCollapsed = true;
                }
            });

            
            // Logout handling
            document.getElementById('confirmLogout').addEventListener('click', function() {
                $('#logoutModal').modal('hide');
                window.location.href = '../logout.php'; // Adjust the path if necessary
            });

            document.getElementById('cancelLogout').addEventListener('click', function() {
                $('#logoutModal').modal('hide');
                window.location.href = 'dashboard.php'; // Adjust the path if necessary
            });

            // Confirm lead request
            document.getElementById('confirmSubmit').addEventListener('click', function () {
                $('#confirmModal').modal('hide');

                $.ajax({
                    type: 'POST',
                    url: 'submit_lead_request.php',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Lead request submitted successfully.');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while processing the request.');
                    }
                });
            });

            // Assuming $userID is available in PHP
            const userID = <?php echo json_encode($_SESSION['user_id']); ?>; // Pass userID from PHP to JavaScript


        });


        function handleLeadAction(action, buttonElement) {
            const leadRow = buttonElement.closest('tr');
            const leadId = leadRow.getAttribute('data-lead-id');

            if (leadId) {
                updateLead(action, leadId);
            } else {
                alert('Failed to retrieve Lead ID.');
            }
        }
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
                        location.reload();
                    } else {
                        alert(response);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while saving money.');
                }
            });
        });

        // Hide the modal and reload the page when the modal is closed
        $('#saveMoneySuccessModal').on('hidden.bs.modal', function () {
            location.reload();
        });

        function updateLead(action, leadId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'accept-reject-lead.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                document.getElementById('status-' + leadId).innerText = response.status;
                                document.getElementById('due-date-' + leadId).innerText = response.due_date;

                                const row = document.getElementById('lead-' + leadId);
                                row.querySelectorAll('button').forEach(button => {
                                    button.disabled = true;
                                    if (button.innerText === 'Accept' || button.innerText === 'Reject') {
                                        button.classList.remove('btn-success', 'btn-danger');
                                        button.classList.add('btn-secondary');
                                    }
                                });
                            } else {
                                if (response.message === 'Insufficient savings to cover the loan amount') {
                                    // Show the insufficient savings modal
                                    const insufficientSavingsModal = new bootstrap.Modal(document.getElementById('insufficientSavingsModal'));
                                    insufficientSavingsModal.show();
                                } else {
                                    // Show the general error modal
                                    const generalErrorModal = new bootstrap.Modal(document.getElementById('generalErrorModal'));
                                    generalErrorModal.show();

                                    // Log the error message to the console
                                    console.error('Error updating lead:', response.message);
                                }
                            }
                        } catch (e) {
                            // Show the general error modal for JSON parsing errors
                            const generalErrorModal = new bootstrap.Modal(document.getElementById('generalErrorModal'));
                            generalErrorModal.show();

                            // Log the error to the console
                            console.error('Error parsing response:', e);
                        }
                    } else {
                        // Show the general error modal for server errors
                        const generalErrorModal = new bootstrap.Modal(document.getElementById('generalErrorModal'));
                        generalErrorModal.show();

                        // Log the server error to the console
                        console.error('Server error:', xhr.statusText);
                    }
                }
            };

            xhr.send('action=' + encodeURIComponent(action) + '&lead_id=' + encodeURIComponent(leadId));
        }

        function contactLead(buttonElement) {
            const leadRow = buttonElement.closest('tr');
            const leadId = leadRow.getAttribute('data-lead-id');
            if (leadId) {
                // Make a request to the PHP backend to get the email
                fetch(`get_lead_email.php?lead_id=${leadId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.email) {
                            // Open the default email client with the email in "To" field
                            window.location.href = `mailto:${data.email}`;
                        } else {
                            alert(data.error || "Failed to get the email.");
                        }
                    })
                    .catch(error => console.error('Error:', error));
            } else {
                alert('Lead ID not found.');
            }
        }
        function fetchLeads() {
            fetch('fetch_leads.php') // Update this path as needed
                .then(response => response.json())
                .then(leads => {
                    const tableBody = document.getElementById('leads-table-body');
                    const noLeadsMessage = document.getElementById('no-leads-message');

                    tableBody.innerHTML = ''; // Clear existing rows

                    if (leads.length > 0) {
                        noLeadsMessage.style.display = 'none'; // Hide "no leads" message

                        leads.forEach(lead => {
                            const row = document.createElement('tr');
                            row.id = `lead-${lead.id}`;
                            row.className = lead.status === 'rejected' ? 'table-danger' : '';
                            row.dataset.leadId = lead.id;

                            row.innerHTML = `
                                <td>${lead.id}</td>
                                <td>${lead.lead_name}</td>
                                <td>${lead.lead_national_id}</td>
                                <td>${lead.lead_contact}</td>
                                <td>${lead.loan_amount}</td>
                                <td id="status-${lead.id}">${lead.status}</td>
                                <td id="due-date-${lead.id}">${lead.due_date}</td>
                                <td>
                                    ${lead.status === 'approved' || lead.status === 'rejected' ? 
                                    `<button class="btn btn-secondary" disabled>${lead.status.charAt(0).toUpperCase() + lead.status.slice(1)}</button>` : 
                                    `
                                    <button class="btn btn-success" onclick="handleLeadAction('accept', this)">Accept</button>
                                    <button class="btn btn-danger" onclick="handleLeadAction('reject', this)">Reject</button>
                                    `}
                                    <button class="btn btn-warning" onclick="contactLead(this)">Contact</button>
                                </td>
                            `;

                            tableBody.appendChild(row);
                        });
                    } else {
                        noLeadsMessage.style.display = 'block'; // Show "no leads" message
                    }
                });
        }

        // Fetch leads every 4 seconds
        setInterval(fetchLeads, 4000);

        // Initial fetch
        fetchLeads();

    </script>
    <?php
    if (isset($_SESSION['save_money_success']) && $_SESSION['save_money_success']) {
        echo '<script type="text/javascript">
                $(document).ready(function(){
                    $("#saveMoneySuccessModal").modal("show");
                });
              </script>';
        unset($_SESSION['save_money_success']); // Unset the session variable after displaying the modal
        echo 'success'; // Echo 'success' to AJAX success function
    };
    $conn->close();
    ?>
</body>
</html>
