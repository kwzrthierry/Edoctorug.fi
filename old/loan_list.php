<?php
require 'test 3/db_connection.php';

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number

// Calculate offset
$offset = ($page - 1) * $limit;

// Fetch users data from the database with limit and offset
$sql = "SELECT * FROM loans_application LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Initialize an empty array to store users
$loans = [];

// Check if there are any loan applications
if ($result->num_rows > 0) {
    // Loop through each row in the result set and store it in the $loans array
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
}
session_start();
$loggedInUser = $_SESSION['user_name'];
// Get distinct statuses for filtering
$statusSql = "SELECT DISTINCT status FROM loans_application";
$statusResult = $conn->query($statusSql);
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
    <link rel="stylesheet" type="text/css" href="styles.css">
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
                <a href="leads_request.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'leads_request.php') echo 'active'; ?>"><i class="fas fa-file"></i> Lead Requests</a>
            </div>
        </div>
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <img src="../assets/images/client-01.png" alt="Logo" class="logo">
                <form action="logout.php" method="post" class="ml-auto">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </nav>
            <!-- Content -->
            <div class="container-fluid">
                <h1 class="mt-4">Loan Application List</h1>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <!-- Filter by Status -->
                        <select class="form-control" id="filterStatus">
                            <option value="">Filter by Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <!-- Search Input -->
                        <input class="form-control" id="searchLoan" type="text" placeholder="Search...">
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
                                <td>
                                    <?php if ($loan['status'] == 'pending'): ?>
                                        <button class="btn btn-info btn-sm viewUserBtn" data-id="<?php echo $loan['id']; ?>">User Info</button>
                                        <button class="btn btn-success btn-sm approveBtn" data-id="<?php echo $loan['id']; ?>">Approve</button>
                                        <button class="btn btn-danger btn-sm denyBtn" data-id="<?php echo $loan['id']; ?>">Deny</button>
                                    <?php elseif ($loan['status'] == 'approved'): ?>
                                        <button class="btn btn-warning btn-sm feedbackBtn" data-id="<?php echo $loan['id']; ?>">Feedback</button>
                                    <?php elseif ($loan['status'] == 'denied'): ?>
                                        <button class="btn btn-warning btn-sm reasonBtn" data-id="<?php echo $loan['id']; ?>">Reason</button>
                                    <?php endif; ?>
                                </td>
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
                        $conn->close();
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
        <!-- User Info Modal -->
    <div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog" aria-labelledby="userInfoModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userInfoModalLabel">User Info</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <!-- User info will be displayed here -->
            <div id="userNationalIdFile">
                <!-- User's national ID file will be displayed here -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="viewNationalIdFileBtn">View National ID File</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Deny Reason Modal -->
    <div class="modal fade" id="denyReasonModal" tabindex="-1" role="dialog" aria-labelledby="denyReasonModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="denyReasonModalLabel">Reason for Denial</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea id="denyReasonText" class="form-control" placeholder="Enter reason for denial" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="submitDenyReasonBtn">Submit</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="feedbackModalLabel">Feedback for Approval</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <textarea id="feedbackText" class="form-control" placeholder="Enter feedback" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="submitFeedbackBtn">Submit</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reason Modal -->
    <div class="modal fade" id="reasonModal" tabindex="-1" role="dialog" aria-labelledby="reasonModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reasonModalLabel">Reason</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" id="reasonText">
            <!-- Reason text will be displayed here -->
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2024 Savings & Loans. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        $(document).ready(function() {
            // Search function for loan table
            $("#searchLoan").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#loanTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Function to filter table rows based on input value and status
            function filterTable() {
                var input, filter, table, tr, tdName, tdStatus, i, statusFilter;
                input = $("#searchLoan").val().toUpperCase();
                table = $("#loanTable");
                tr = table.find("tr");
                statusFilter = $("#filterStatus").val().toUpperCase();

                tr.each(function() {
                    tdName = $(this).find("td").eq(1); // Index 1 for 'Name'
                    tdStatus = $(this).find("td").eq(7); // Index 7 for 'Status'
                    if (tdName.length && tdStatus.length) {
                        txtValueName = tdName.text();
                        txtValueStatus = tdStatus.text();
                        $(this).toggle(
                            txtValueName.toUpperCase().indexOf(input) > -1 &&
                            (statusFilter === "" || txtValueStatus.toUpperCase().indexOf(statusFilter) > -1)
                        );
                    }
                });
            }

            // Event listeners for input and select elements
            $("#searchLoan").on("keyup", filterTable);
            $("#filterStatus").on("change", filterTable);

            // Handle click on "User Info" button
            $(document).on('click', '.viewUserBtn', function() {
                var loanId = $(this).data('id');
                $.ajax({
                    url: 'get_user_info.php',
                    type: 'POST',
                    data: { loanId: loanId },
                    success: function(response) {
                        $('#userInfoModal .modal-body').html(response);
                        $('#userInfoModal').modal('show');
                    },
                    error: function() {
                        alert('An error occurred while fetching user info.');
                    }
                });
            });

            // Handle click on "View National ID File" button
            $(document).on('click', '#viewNationalIdFileBtn', function() {
                var nationalIdFileLocation = $('#userNationalIdFile').data('location');
                window.open(nationalIdFileLocation, '_blank');
            });

            // Handle click on "Approve" button
            $(document).on('click', '.approveBtn', function() {
                var loanId = $(this).data('id');
                $('#submitFeedbackBtn').data('id', loanId);
                $('#feedbackModal').modal('show');
            });

            // Handle submit of feedback
            $(document).on('click', '#submitFeedbackBtn', function() {
                var loanId = $(this).data('id');
                var feedback = $('#feedbackText').val();
                if (feedback.trim() === '') {
                    alert('Please enter feedback.');
                    return;
                }
                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: { loanId: loanId, status: 'approved', reason: feedback },
                    success: function() {
                        $('#' + loanId + '_status').text('approved').removeClass('status-pending').addClass('status-approved');
                        $('#loan' + loanId + ' .approveBtn, #loan' + loanId + ' .denyBtn, #loan' + loanId + ' .viewUserBtn').remove();
                        $('#loan' + loanId + ' .actions').append("<button class='btn btn-warning btn-sm feedbackBtn' data-id='" + loanId + "'>Feedback</button>");
                        $('#feedbackModal').modal('hide');
                        $('#feedbackText').val('');
                    },
                    error: function() {
                        alert('An error occurred while updating status.');
                    }
                });
            });

            // Handle click on "Deny" button
            $(document).on('click', '.denyBtn', function() {
                var loanId = $(this).data('id');
                $('#submitDenyReasonBtn').data('id', loanId);
                $('#denyReasonModal').modal('show');
            });

            // Handle submit of deny reason
            $(document).on('click', '#submitDenyReasonBtn', function() {
                var loanId = $(this).data('id');
                var reason = $('#denyReasonText').val();
                if (reason.trim() === '') {
                    alert('Please enter a reason for denial.');
                    return;
                }
                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: { loanId: loanId, status: 'denied', reason: reason },
                    success: function() {
                        $('#' + loanId + '_status').text('denied').removeClass('status-pending').addClass('status-denied');
                        $('#loan' + loanId + ' .approveBtn, #loan' + loanId + ' .denyBtn, #loan' + loanId + ' .viewUserBtn').remove();
                        $('#loan' + loanId + ' .actions').append("<button class='btn btn-warning btn-sm reasonBtn' data-id='" + loanId + "'>Reason</button>");
                        $('#denyReasonModal').modal('hide');
                        $('#denyReasonText').val('');
                    },
                    error: function() {
                        alert('An error occurred while updating status.');
                    }
                });
            });

            // Handle click on "Reason" button
            $(document).on('click', '.reasonBtn', function() {
                var loanId = $(this).data('id');
                $.ajax({
                    url: 'get_reason.php',
                    type: 'POST',
                    data: { loanId: loanId },
                    success: function(response) {
                        $('#reasonText').html(response);
                        $('#reasonModal').modal('show');
                    },
                    error: function() {
                        alert('An error occurred while fetching the reason.');
                    }
                });
            });

            // Handle click on "Feedback" button
            $(document).on('click', '.feedbackBtn', function() {
                var loanId = $(this).data('id');
                $.ajax({
                    url: 'get_feedback.php',
                    type: 'POST',
                    data: { loanId: loanId },
                    success: function(response) {
                        $('#reasonText').html(response);
                        $('#reasonModalLabel').text('Feedback');
                        $('#reasonModal').modal('show');
                    },
                    error: function() {
                        alert('An error occurred while fetching the feedback.');
                    }
                });
            });

            // Handle click on close buttons for modals
            $(document).on('click', '.modal .close', function() {
                $(this).closest('.modal').modal('hide');
            });
        });
    </script>

</body>
</html>
