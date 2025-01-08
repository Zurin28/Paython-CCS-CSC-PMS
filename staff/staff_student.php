<?php 
session_start();
require_once '../classes/organization.class.php';
require_once '../classes/student.class.php';
require_once '../classes/staff.class.php';
require_once '../classes/academicperiod.class.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set the error log file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    die("Unauthorized access.");
}

// Instantiate classes
$student = new Student();
$staff = new Staff();
$academicPeriod = new AcademicPeriod();

// Get the current academic period
$currentPeriod = $academicPeriod->getCurrentPeriod();
$currentSchoolYear = $currentPeriod['school_year'];
$currentSemester = $currentPeriod['semester'];

// Get organizations for the logged-in staff member
$staffOrganizations = $staff->getStaffOrganizations($_SESSION['StudentID']);
$organizationIDs = array_column($staffOrganizations, 'OrganizationID');

// Log the organizations and organization IDs
error_log("Staff Organizations: " . print_r($staffOrganizations, true));
error_log("Organization IDs: " . implode(',', $organizationIDs));

// Get student details for the current academic period and organizations
$studentDetails = $student->getStudentFeeDetails($currentSchoolYear, $currentSemester, $organizationIDs);

// Debug the results
error_log("Student Details Count: " . count($studentDetails));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - PayThon</title>
    <link rel="stylesheet" href="../css/staffbar.css">
    <link rel="stylesheet" href="../css/staff_table.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <?php include 'staffbar.php'; ?>

    <!-- Remove any extra home-section wrappers -->
    <div class="content-wrapper">
        <div class="table-container">
            <div class="table-header">
                <div class="filter-section">
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="Paid">Paid</option>
                            <option value="Not Paid">Not Paid</option>
                            <option value="Pending">Pending</option>
                        </select>
                        <select id="courseFilter" class="filter-select">
                            <option value="all">All Courses</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Associate in Computer Technology">Associate in Computer Technology</option>
                            <option value="Application Development">Application Development</option>
                        </select>
                    </div>
                    
                    <!-- Search Filter -->
                    <div class="search-filter">
                        <i class='bx bx-search'></i>
                        <input type="text" id="searchBar" class="search-bar" 
                            placeholder="Search student name or ID...">
                        <button class="search-btn" onclick="filterTable()">
                            <i class='bx bx-search'></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Section</th>
                            <th>Fee Name</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($studentDetails)): ?>
                            <?php foreach ($studentDetails as $detail): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($detail['StudentID']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['Course']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['Year']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['Section']); ?></td>
                                    <td><?php echo htmlspecialchars($detail['FeeName']); ?></td>
                                    <td>₱<?php echo number_format($detail['Amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($detail['Status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".sidebarBtn");
        sidebarBtn.onclick = function() {
            sidebar.classList.toggle("active");
        }

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const courseFilter = document.getElementById('courseFilter').value;
            const searchBar = document.getElementById('searchBar').value.toLowerCase();
            const table = document.querySelector('.custom-table tbody');
            const rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                if (cells.length === 0) continue; // Skip empty rows

                const studentId = cells[0].textContent.toLowerCase();
                const name = cells[1].textContent.toLowerCase();
                const course = cells[2].textContent; // Course column
                const status = cells[7].textContent; // Status column

                const matchesStatus = (statusFilter === 'all' || status === statusFilter);
                const matchesCourse = (courseFilter === 'all' || course === courseFilter);
                const matchesSearch = (studentId.includes(searchBar) || name.includes(searchBar));

                if (matchesStatus && matchesCourse && matchesSearch) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Add event listeners
        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('courseFilter').addEventListener('change', filterTable);
        document.getElementById('searchBar').addEventListener('input', filterTable);

        // Optional: Add function to reset filters
        function resetFilters() {
            document.getElementById('statusFilter').value = 'all';
            document.getElementById('courseFilter').value = 'all';
            document.getElementById('searchBar').value = '';
            filterTable();
        }
    </script>
</body>
</html>