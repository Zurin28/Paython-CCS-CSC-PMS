<?php

$current_page = basename($_SERVER['PHP_SELF']);

// Define page titles
$page_titles = [
    'staff_dashboard.php' => 'Dashboard',
    'staff_student.php' => 'Student List',
    'paymentmanagement.php' => 'Payment Management',
    'staff_fees.php' => 'Fees',
    // Add more pages as needed
];

// Get the current page title
$current_title = isset($page_titles[$current_page]) ? $page_titles[$current_page] : 'Dashboard';

// Include the Organization class
require_once '../classes/organization.class.php';

// Instantiate the Organization class
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/staffbar.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Fixed Header Section -->
        <div class="sidebar-header">
            <div class="logo">
                <img src="../img/logoccs.png" alt="logo">
                <span class="logo_name">PayThon</span>
            </div>

            <div class="profile-section">
                <div class="profile-dropdown">
                    <div class="profile-info-trigger" style="background: none; padding: 0;">
                        <img src="../img/prof.jpg" alt="profile">
                        <div class="profile-info">
                            <span class="admin_name">NAME</span>
                            <span class="admin_role">Staff</span>
                        </div>
                        <i class='bx bx-chevron-down'></i>
                    </div>
                    
                    <div class="profile-dropdown-content">
                        <div class="profile-header">
                        <img src="../img/prof.jpg" alt="profile">
                            <div>
                                <span class="full-name">Jose Miguel</span>
                                <span class="email">JoseMiguel@example.com</span>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="../staff/staff_student.php">
                            <i class='bx bx-user-circle'></i>
                            Student Account
                        </a>
                       
                    </div>
                </div>
            </div>
        </div>

        <!-- Scrollable Menu Section -->
        <ul class="sidebar_list">
            <li>
                <a href="staff_dashboard.php" class="<?php echo ($current_page == 'staff/staff_dashboard.php') ? 'active' : ''; ?>">
                    <i class='bx bx-grid-alt'></i>
                    <span class="list_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="staff_student.php" class="<?php echo ($current_page == 'staff/staff_student.php') ? 'active' : ''; ?>">
                    <i class='bx bx-user-pin'></i>
                    <span class="list_name">Student List</span>
                </a>
            </li>
            <li>
                <a href="paymentmanagement.php" class="<?php echo ($current_page == 'staff/paymentmanagement.php') ? 'active' : ''; ?>">
                <i class='bx bxl-paypal'></i>
                    <span class="list_name">Payment Management</span>
                </a>
            </li>
            <li>
                <a href="staff_fees.php" class="<?php echo ($current_page == 'staff/staff_fees.php') ? 'active' : ''; ?>">
                <i class='bx bx-money-withdraw'></i>
                    <span class="list_name">Fees</span>
                </a>
            </li>
        </ul>

        <!-- Fixed Logout Button -->
        <li class="../account/login.php">
            <a href="../account/login.php">
                <i class='bx bx-log-out'></i>
                <span class="list_name">Log out</span>
            </a>
        </li>
    </div>

    <!-- Modify this section -->
    <nav class="home-section-nav">
        <div class="sidebar-button">
            <i class='bx bx-menu sidebarBtn'></i>
            <span class="dashboard" id="topbar-title"><?php echo $current_title; ?></span>
        </div>
        
       
   
    </nav>

    <script>
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior
                const name = this.getAttribute('data-name');
                document.getElementById('topbar-title').textContent = name;
                window.location.href = this.getAttribute('href'); // Navigate to the link
            });
        });
    </script>
</body>
</html> 