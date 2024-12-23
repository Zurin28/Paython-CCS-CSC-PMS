<?php 
session_start();
require_once '../classes/organization.class.php';
require_once '../classes/student.class.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Instantiate the Student class
$student = new Student();
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
                    <!-- Filter Group -->
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
                        <?php
                        $studentDetails = $student->getStudentFeeDetails();
                     
                        foreach ($studentDetails as $detail) {
                            echo "<tr>
                                    <td>{$detail['StudentID']}</td>
                                    <td>{$detail['Name']}</td>
                                    <td>{$detail['Course']}</td>
                                    <td>{$detail['Year']}</td>
                                    <td>{$detail['Section']}</td>
                                    <td>{$detail['FeeName']}</td>
                                    <td>₱" . number_format($detail['Amount'], 2) . "</td>
                                    <td>{$detail['Status']}</td>
                                </tr>";
                        }
                        ?>
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
           // Status update functionality
           function updateStatus(checkbox, studentId) {
            if(checkbox.checked) {
                const row = checkbox.closest('tr');
                row.querySelector('td:nth-child(8)').textContent = 'Paid';
                checkbox.parentElement.innerHTML = '<span class="paid-status">Paid</span>';
                alert(`Payment status updated for Student ID: ${studentId}`);
            }
        }

        document.getElementById('organizationSelect').addEventListener('change', function() {
            const orgId = this.value;
            if (orgId) {
                window.location.href = 'staff_student.php?org=' + orgId;
            } else {
                window.location.href = 'staff_student.php';
            }
        });

        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const courseFilter = document.getElementById('courseFilter').value;
            const searchBar = document.getElementById('searchBar').value.toLowerCase();
            const table = document.querySelector('.custom-table tbody');
            const rows = table.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const studentId = cells[0].textContent.toLowerCase();
                const name = cells[1].textContent.toLowerCase();
                const course = cells[2].textContent;
                const status = cells[7].textContent;

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

        document.getElementById('statusFilter').addEventListener('change', filterTable);
        document.getElementById('courseFilter').addEventListener('change', filterTable);
        document.getElementById('searchBar').addEventListener('input', filterTable);
    </script>
</body>
</html>

