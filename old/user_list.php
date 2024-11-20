<?php
require 'test 3/db_connection.php';

// Pagination variables
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Current page number

// Calculate offset
$offset = ($page - 1) * $limit;

// Fetch users data from the database with limit and offset
$sql = "SELECT * FROM users WHERE user_type='user' LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Initialize an empty array to store users
$users = [];

// Check if there are any users
if ($result->num_rows > 0) {
    // Loop through each row in the result set and store it in the $users array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
session_start();
$loggedInUser = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User list</title>
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
            padding: 10px;
            margin-left: 250px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: white !important; /* Make navbar transparent */
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333 !important;
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
        .pagination {
            justify-content: center;
            position: relative;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
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
                <h1 class="mt-4">User List</h1>
                <input class="form-control mb-4" id="searchUser" type="text" placeholder="Search..">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>National ID Number</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['national_id_number']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['phone']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <nav aria-label="Page navigation example">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Count total number of records
                        $sql_count = "SELECT COUNT(*) AS total FROM users WHERE user_type='user'";
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
    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p>&copy; 2024 Savings & Loans. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        $(document).ready(function() {
            // Search function for loan table
            $("#searchUser").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#userTable tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>








