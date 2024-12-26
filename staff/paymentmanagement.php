<?php
session_start();
require_once '../classes/paymentrequest.class.php';
require_once '../classes/staff.class.php';
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

// Initialize classes
$paymentRequest = new PaymentRequest();
$staff = new Staff();

// Get staff organizations
$staffOrganizations = $staff->getStaffOrganizations($_SESSION['StudentID']);

// Get the selected organization from GET parameter
$selectedOrg = isset($_GET['org']) ? $_GET['org'] : null;

// Get payment requests with organization filter
$paymentRequests = $paymentRequest->getPaymentRequestsForCurrentPeriod($selectedOrg, $_SESSION['StudentID']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Management - PayThon</title>
    <link rel="stylesheet" href="../css/staffbar.css">
    <link rel="stylesheet" href="../css/receive_receipt.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .accepted {
            border: 2px solid green;
            color: green;
        }
        .rejected {
            border: 2px solid red;
            color: red;
        }
    </style>
</head>
<body>
    <?php include 'staffbar.php'; ?>
    
    <div class="content-wrapper">
        <div class="table-container">
            <div class="table-header">
                <div class="filter-section">
                    <!-- Organization Filter -->
                    <form method="GET" action="" class="org-filter-form" style="margin-right: 20px;">
                        <div class="org-dropdown">
                            <select name="org" class="org-select filter-select" onchange="this.form.submit()">
                                <option value="">All Organizations</option>
                                <?php foreach ($staffOrganizations as $org): ?>
                                    <option value="<?php echo htmlspecialchars($org['OrganizationID']); ?>"
                                            <?php echo ($selectedOrg == $org['OrganizationID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($org['OrgName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <!-- Specific Date Filter -->
                    <div class="date-filter">
                        <input type="date" id="specificDate" class="date-input">
                    </div>

                    <!-- Search Filter -->
                    <div class="search-filter">
                        <i class='bx bx-search'></i>
                        <input type="text" id="searchInput" class="search-bar" 
                               placeholder="Search student name or ID...">
                        <button class="search-btn">
                            <i class='bx bx-search'></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- Rest of your existing table structure -->
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Fee Name</th>
                            <th>Payment Type</th>
                            <th>Date Paid</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentRequests as $request): ?>
                            <tr data-student-id="<?php echo $studentID; ?>" data-fee-name="<?php echo $feeName; ?>">
                                <td><?= htmlspecialchars($request['StudentID']) ?></td>
                                <td><?= htmlspecialchars($request['Name']) ?></td>
                                <td><?= htmlspecialchars($request['FeeName']) ?></td>
                                <td><?= htmlspecialchars($request['PaymentType']) ?></td>
                                <td><?= htmlspecialchars($request['DatePaid']) ?></td>
                                <td class='action-buttons'>
                                    <?php if ($request['Status'] === 'Paid'): ?>
                                        <button class="btn accepted" disabled>Accepted</button>
                                    <?php elseif ($request['Status'] === 'Not Paid'): ?>
                                        <button class="btn rejected" disabled>Rejected</button>
                                    <?php else: ?>
                                        <button onclick="acceptPayment('<?= htmlspecialchars($request['StudentID']) ?>', '<?= addslashes(htmlspecialchars($request['FeeName'])) ?>')" class="btn accept">Accept</button>
                                        <button onclick="rejectPayment('<?= htmlspecialchars($request['StudentID']) ?>', '<?= addslashes(htmlspecialchars($request['FeeName'])) ?>')" class="btn reject">Reject</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Keep your existing JavaScript -->
    <script>
      document.getElementById('searchInput').addEventListener('input', filterTable);
      document.getElementById('specificDate').addEventListener('change', filterTable);
      
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const specificDate = document.getElementById('specificDate').value;
        const table = document.querySelector('.custom-table tbody');
        const rows = table.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            if (cells.length === 0) continue; // Skip empty rows

            const studentId = cells[0].textContent.toLowerCase();
            const name = cells[1].textContent.toLowerCase();
            const datePaid = cells[4].textContent.trim(); // Ensure this matches the Date Paid column index

            const matchesSearch = studentId.includes(searchValue) || name.includes(searchValue);
            const matchesDate = !specificDate || datePaid === specificDate;
            rows[i].style.display = matchesSearch && matchesDate ? '' : 'none';
        }
    }

    function updatePaymentStatus(studentID, feeName, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.responseText === 'Success') {
                    alert('Payment status updated successfully.');
                    const row = document.querySelector(`tr[data-student-id="${studentID}"][data-fee-name="${feeName}"]`);
                    if (status === 'Paid') {
                        row.classList.remove('rejected');
                        row.classList.add('accepted');
                    } else if (status === 'Not Paid') {
                        row.classList.remove('accepted');
                        row.classList.add('rejected');
                    }
                } else {
                    alert('Error updating payment status: ' + xhr.responseText);
                }
            }
        };
        xhr.send(`studentID=${studentID}&feeName=${feeName}&status=${status}`);
    }

    function acceptPayment(studentID, feeName) {
        updatePaymentStatus(studentID, feeName, 'Paid');
    }

    function rejectPayment(studentID, feeName) {
        updatePaymentStatus(studentID, feeName, 'Not Paid');
    }
    </script>
</body>
</html>
