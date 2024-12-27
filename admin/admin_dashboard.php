<?php
session_start();
require_once '../classes/academicperiod.class.php';
require_once '../classes/student.class.php';
require_once '../classes/organization.class.php';
require_once '../classes/paymentrequest.class.php';

// Get current academic period
$academicPeriod = new AcademicPeriod();
$currentPeriod = $academicPeriod->getCurrentPeriod();

// Initialize counters
$totalStudents = 0;
$totalOrganizations = 0;
$pendingRequests = 0;

if ($currentPeriod) {
    $student = new Student();
    $organization = new Organization();
    $paymentRequest = new PaymentRequest();
    
    $totalStudents = $student->getTotalStudents($currentPeriod['school_year'], $currentPeriod['semester']);
    $totalOrganizations = $organization->getTotalOrganizations($currentPeriod['school_year'], $currentPeriod['semester']);
    $pendingRequests = $paymentRequest->getPendingPaymentRequestCount($currentPeriod['school_year'], $currentPeriod['semester']);
}

// Add other required classes/connections
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PayThon</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <section class="home-section">
        <div class="home-content">
            <!-- Bento Grid Stats -->
            <div class="bento-grid">
                <div class="bento-card students">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Students</h3>
                            <p class="number"><?php echo number_format($totalStudents); ?></p>
                   
    
                        </div>
                    </div>
                </div>

                <div class="bento-card organizations">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="card-info">
                            <h3>Organizations</h3>
                            <p class="number"><?php echo number_format($totalOrganizations); ?></p>
                         
                        </div>
                    </div>
                </div>

                <div class="bento-card pending">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-info">
                            <h3>Pending Requests</h3>
                            <p class="number"><?php echo number_format($pendingRequests); ?></p>
                          
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="recent-activity">
                <div class="activity-header">
                    <h2>Recent Activity</h2>
                    <a href="activity_logs.php" class="view-more">
                        View All
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-details">
                            <p class="activity-text">
                                <span class="staff-name">Jose Miguel Esperat</span> approved payment request from <span class="highlight">CS Organization</span>
                            </p>
                            <p class="activity-time">2 hours ago</p>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon">
                        <i class='bx bx-money'></i>
                        </div>
                        <div class="activity-details">
                            <p class="activity-text">
                                <span class="staff-name">Joel Josh Que</span> added Fee for <span class="highlight">Venom Organization</span>
                            </p>
                            <p class="activity-time">5 hours ago</p>
                        </div>
                    </div>

                    <!-- Add more activity items as needed -->
                </div>
            </div>
        </div>
    </section>
    <script src="/js/interactive.js"></script>
</body>
</html>