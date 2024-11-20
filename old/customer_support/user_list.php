<?php
require '../test 3/db_connection.php';

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
            background: linear-gradient(45deg, #007bff, #00c6ff) !important;
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
                    $userImage = isset($_SESSION['user_gender']) && $_SESSION['user_gender'] === 'female' ? '../female.jpg' : '../male.jpg';
                ?>
                <img src="<?php echo $userImage; ?>" alt="User Photo">
                <h5><?php echo $loggedInUser; ?></h5>
            </div>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'dashboard-admin.php') echo 'active'; ?>"><i class="fa fa-home"></i> Home</a>
                <a href="user_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'user_list.php') echo 'active'; ?>"><i class="fa fa-users"></i> User List</a>
                <a href="loan_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'loan_list.php') echo 'active'; ?>"><i class="fa fa-file"></i> Loan Application List</a>
                <a href="savings_list.php" class="list-group-item list-group-item-action <?php if(basename($_SERVER['PHP_SELF']) == 'savings_list.php') echo 'active'; ?>"><i class="fas fa-piggy-bank"></i> Savings List</a>
            </div>
        </div>
        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <img src="../../assets/images/client-01.png" alt="Logo" class="logo">
                <form action="../logout.php" method="post" class="ml-auto">
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['national_id_number']; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['phone']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm view-id-file" data-file-url="../<?php echo $user['national_id_file']; ?>">View Identification</button>
                                </td>

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
                        $total_records = $row_count['total'];
                        $total_pages = ceil($total_records / $limit);

                        // Display pagination
                        for ($i = 1; $i <= $total_pages; $i++) {
                            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="user_list.php?page=' . $i . '">' . $i . '</a></li>';
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.view-id-file').forEach(button => {
            button.addEventListener('click', function() {
                const fileUrl = this.getAttribute('data-file-url');
                if (fileUrl) {
                    window.open(fileUrl, '_blank');
                } else {
                    alert('No identification file available for this user.');
                }
            });
        });

        document.getElementById('searchUser').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userTable = document.getElementById('userTable');
            const rows = userTable.getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                const nationalIdNumber = row.cells[0].textContent.toLowerCase();
                const name = row.cells[1].textContent.toLowerCase();
                const phone = row.cells[2].textContent.toLowerCase();
                const email = row.cells[3].textContent.toLowerCase();

                if (nationalIdNumber.includes(searchTerm) ||
                    name.includes(searchTerm) ||
                    phone.includes(searchTerm) ||
                    email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

    </script>
</body>
</html>
