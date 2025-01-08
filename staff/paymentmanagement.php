<?php
session_start();
require_once '../classes/paymentrequest.class.php';
require_once '../classes/staff.class.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

// Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if staff is logged in
if (!isset($_SESSION['StaffID'])) {
    error_log('Error: Staff not logged in');
    echo 'Error: Staff not logged in';
    exit;
}

// Initialize classes
$paymentRequest = new PaymentRequest();
$staff = new Staff();

// Get staff organizations
$staffOrganizations = $staff->getStaffOrganizations($_SESSION['StudentID']);
error_log("Staff organizations: " . print_r($staffOrganizations, true));

// Extract organization IDs
$organizationIDs = array_column($staffOrganizations, 'OrganizationID');
error_log("Organization IDs: " . implode(',', $organizationIDs));

// Get the selected organization from GET parameter
$selectedOrg = isset($_GET['org']) ? $_GET['org'] : null;
error_log("Selected organization: " . $selectedOrg);

// Get all payment requests for the current period and organizations
$paymentRequests = $paymentRequest->getAllPaymentRequestsForCurrentPeriod($organizationIDs);

// Capture and log the var_dump output
ob_start();
var_dump($paymentRequests);
$varDumpOutput = ob_get_clean();
error_log("Payment requests: " . $varDumpOutput);

// Log the feeID values for debugging
foreach ($paymentRequests as $request) {
    error_log("feeID: " . ($request['FeeID'] ?? 'NULL'));
}

// Log the number of payment requests fetched
error_log("Number of payment requests fetched: " . count($paymentRequests));
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
                                    <option value="<?php echo htmlspecialchars($org['OrganizationID'] ?? ''); ?>"
                                            <?php echo ($selectedOrg == $org['OrganizationID']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($org['OrgName'] ?? ''); ?>
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
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Date Paid</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="paymentRequestsTable">
                        <?php if (!empty($paymentRequests)): ?>
                            <?php foreach ($paymentRequests as $request): ?>
                                <tr data-student-id="<?php echo htmlspecialchars($request['StudentID'] ?? ''); ?>" data-fee-id="<?php echo htmlspecialchars($request['FeeID'] ?? ''); ?>">
                                    <td><?= htmlspecialchars($request['StudentID'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($request['Name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($request['FeeName'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($request['Amount'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($request['PaymentType'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($request['DatePaid'] ?? '') ?></td>
                                    <td class='action-buttons'>
                                        <?php if ($request['Status'] === 'Paid'): ?>
                                            <button class="btn accepted" disabled>Accepted</button>
                                        <?php elseif ($request['Status'] === 'Not Paid'): ?>
                                            <button class="btn rejected" disabled>Rejected</button>
                                        <?php else: ?>
                                            <button onclick="acceptPayment('<?= htmlspecialchars($request['StudentID'] ?? '') ?>', '<?= htmlspecialchars($request['FeeID'] ?? '') ?>')" class="btn accept">Accept</button>
                                            <button onclick="rejectPayment('<?= htmlspecialchars($request['StudentID'] ?? '') ?>', '<?= htmlspecialchars($request['FeeID'] ?? '') ?>')" class="btn reject">Reject</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No payment requests found.</td>
                            </tr>
                        <?php endif; ?>
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
            const datePaid = cells[5].textContent.trim(); // Ensure this matches the Date Paid column index

            const matchesSearch = studentId.includes(searchValue) || name.includes(searchValue);
            const matchesDate = !specificDate || datePaid === specificDate;
            rows[i].style.display = matchesSearch && matchesDate ? '' : 'none';
        }
    }

    function updatePaymentStatus(studentID, feeID, status) {
        console.log(`Updating payment status: studentID=${studentID}, feeID=${feeID}, status=${status}`);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                console.log(`Response status: ${xhr.status}`);
                if (xhr.status === 200) {
                    console.log(`Response: ${xhr.responseText}`);
                    if (xhr.responseText === 'Success') {
                        alert('Payment status updated successfully.');
                        const row = document.querySelector(`tr[data-student-id="${studentID}"][data-fee-id="${feeID}"]`);
                        if (status === 'Paid') {
                            row.classList.remove('rejected');
                            row.classList.add('accepted');
                            row.querySelector('.accept').disabled = true;
                            row.querySelector('.reject').disabled = true;
                        } else if (status === 'Not Paid') {
                            row.classList.remove('accepted');
                            row.classList.add('rejected');
                            row.querySelector('.accept').disabled = true;
                            row.querySelector('.reject').disabled = true;
                        }
                    } else {
                        alert('Error updating payment status: ' + xhr.responseText);
                    }
                } else {
                    console.error(`Error: Failed to update payment status. HTTP status: ${xhr.status}`);
                }
            }
        };
        xhr.send(`studentID=${studentID}&feeID=${feeID}&status=${status}`);
    }

    function acceptPayment(studentID, feeID) {
        updatePaymentStatus(studentID, feeID, 'Paid');
    }

    function rejectPayment(studentID, feeID) {
        updatePaymentStatus(studentID, feeID, 'Not Paid');
    }
    </script>
</body>
</html>
