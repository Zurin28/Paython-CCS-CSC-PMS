<?php 
session_start();
require_once '../classes/account.class.php';
require_once '../classes/fee.class.php';
require_once '../classes/organization.class.php';
require_once '../classes/academicperiod.class.php';
require_once '../classes/student.class.php';
require_once '../classes/paymentrequest.class.php';
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);



// Get current academic period
$academicPeriod = new AcademicPeriod();
$currentPeriod = $academicPeriod->getCurrentPeriod();

if (!$currentPeriod) {
    error_log("No active academic period set.");
    echo "<p style='color: red;'>No active academic period set. Please contact the administrator.</p>";
    exit;
}

// Fetch students for the current academic period
$student = new Student();
$students = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['searchQuery'])) {
    $searchQuery = trim($_POST['searchQuery']);
    $students = $student->searchStudentsByPeriod($searchQuery, $currentPeriod['school_year'], $currentPeriod['semester']);
} else {
    $students = $student->getStudentsByPeriod($currentPeriod['school_year'], $currentPeriod['semester']);
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteStudentId'])) {
    $deleteStudentId = $_POST['deleteStudentId'];
    $account = new Account();
    $student = new Student();

    $studentDeleted = $student->deleteStudentByStudentId($deleteStudentId);
    $accountDeleted = $account->deleteAccountByStudentId($deleteStudentId);

    if ($accountDeleted && $studentDeleted) {
        echo "<p style='color: green;'>Student and account deleted successfully.</p>";
    } else {
        echo "<p style='color: red;'>Error deleting student or account.</p>";
    }

    // Refresh the student list after deletion
    $students = $student->getStudentsByPeriod($currentPeriod['school_year'], $currentPeriod['semester']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - PayThon</title>
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/studentlist.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <section class="home-section" style="padding-top: 80px;">
        <div class="home-content">
            <!-- Student Table Section -->
            <div class="table-container">
                <div class="table-header">
                
                    <!-- Search Form -->
                    <div class="search-filter">
                        <form method="POST">
                            <input 
                                type="text" 
                                name="searchQuery" 
                                id="searchBar" 
                                placeholder="Search..." 
                                class="search-bar"
                                value="<?= isset($_POST['searchQuery']) ? htmlspecialchars($_POST['searchQuery']) : '' ?>"
                            >
                            <button type="submit" class="search-button">Search</button>
                        </form>
                    </div>
                </div>

                <!-- Student Table -->
                <table id="studentTable" class="custom-table">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>StudentID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if (empty($students)) {
                        ?>
                        <tr>
                            <td colspan="8">
                                <p class="search">No Student Information for <?= htmlspecialchars($currentPeriod['school_year']) ?> - <?= htmlspecialchars($currentPeriod['semester']) ?> Semester</p>
                            </td>
                        </tr>
                        <?php 
                        } else {
                            $i = 1;
                            foreach ($students as $student) {
                        ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= htmlspecialchars($student['StudentID']) ?></td>
                            <td><?= htmlspecialchars($student['first_name']) . " " . htmlspecialchars($student['MI']) . " " . htmlspecialchars($student['last_name']) ?></td>
                            <td><?= htmlspecialchars($student['WmsuEmail']) ?></td>
                            <td><?= htmlspecialchars($student['Course']) ?></td>
                            <td><?= htmlspecialchars($student['Year']) ?></td>
                            <td><?= htmlspecialchars($student['Section']) ?></td>
                            <td class="table-actions">
                                <button class="table-btn btn-view view-status-btn" data-student-id="<?= $student['StudentID'] ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="deleteStudentId" value="<?= htmlspecialchars($student['StudentID']) ?>">
                                    <button type="submit" class="table-btn btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                                $i++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Fee Status Modal -->
    <div id="show-fees-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Student Fees Status</h3>
            <div id="loading-indicator" style="display: none;">Loading...</div>
            <table id="feesTable" class="custom-table">
                <thead>
                    <tr>
                        <th>NO.</th>
                        <th>Organization</th>
                        <th>Fee Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Fee rows will be dynamically added here -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/modal.js"></script>
    <script src="js/student.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.view-status-btn');
            const modal = document.getElementById('show-fees-modal');
            const closeModal = modal.querySelector('.close');
            const feesTableBody = document.getElementById('feesTable').querySelector('tbody');
            const loadingIndicator = document.getElementById('loading-indicator');

            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    fetchFeesStatus(studentId);
                });
            });

            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });

            function fetchFeesStatus(studentId) {
                loadingIndicator.style.display = 'block';
                feesTableBody.innerHTML = '';

                fetch(`fetch_fees_status.php?studentId=${studentId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        loadingIndicator.style.display = 'none';
                        if (data.error) {
                            console.error('Error:', data.error);
                            const row = document.createElement('tr');
                            row.innerHTML = `<td colspan="5">${data.error}</td>`;
                            feesTableBody.appendChild(row);
                        } else if (data.length > 0) {
                            data.forEach((fee, index) => {
                                const statusClass = fee.Status === 'Paid' ? 'status-paid' : fee.Status === 'Pending' ? 'status-pending' : 'status-not-paid';
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td>${fee.OrgName}</td>
                                    <td>${fee.FeeName}</td>
                                    <td>${fee.Amount}</td>
                                    <td class="${statusClass}">${fee.Status}</td>
                                `;
                                feesTableBody.appendChild(row);
                            });
                        } else {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td colspan="5">No fee information available for this student.</td>
                            `;
                            feesTableBody.appendChild(row);
                        }
                        modal.style.display = 'block';
                    })
                    .catch(error => {
                        loadingIndicator.style.display = 'none';
                        console.error('Error fetching fee status:', error);
                        error_log('Error fetching fee status: ' + error.message);
                    });
            }
        });

        function error_log(message) {
            fetch('log_error.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            });
        }
    </script>
</body>
</html>
