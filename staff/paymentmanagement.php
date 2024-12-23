<?php
require_once '../classes/paymentrequest.class.php';
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

$paymentRequest = new PaymentRequest();
$paymentRequests = $paymentRequest->getPaymentRequestsForCurrentPeriod();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipts</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/staffbar.css">
    <link rel="stylesheet" href="../css/receive_receipt.css">
    
</head>
<body>
    <div class="container">
        <?php include 'staffbar.php'; ?>
        <div class="content-wrapper">
            <div class="table-container">
                <!-- Updated filter section -->
               
                <div class="filter-section">
                    
                    <div class="search-filter">
                        <i class='bx bx-search'></i>
                        <input type="text" class="search-bar" placeholder="Search receipt or student name...">
                        <button class="search-btn">
                            <i class='bx bx-search'></i>
                            Search
                        </button>
                    </div>
                    <button class="filter-date-btn">
                        <i class='bx bx-calendar'></i>
                        Filter by Date
                    </button>
                </div>

                <table>
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
                            <tr>
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

                <style>
                    .btn.accepted {
                        background-color: white;
                        color: green;
                        border: 2px solid green;
                        cursor: default;
                    }
                    .btn.rejected {
                        background-color: white;
                        color: red;
                        border: 2px solid red;
                        cursor: default;
                    }
                </style>
            </div>
        </div>
    </div>


    <script>
      // Sidebar toggle functionality
let sidebar = document.querySelector(".sidebar");
let sidebarBtn = document.querySelector(".bx-menu");

sidebarBtn.addEventListener("click", () => {
    sidebar.classList.toggle("active");
    if (sidebar.classList.contains("active")) {
        document.querySelector(".content-wrapper").style.marginLeft = "60px";
        document.querySelector("nav").style.width = "calc(100% - 60px)";
    } else {
        document.querySelector(".content-wrapper").style.marginLeft = "240px";
        document.querySelector("nav").style.width = "calc(100% - 240px)";
    }
});

// Modal functionality
function acceptPayment(studentID, feeName) {
    console.log('Accepting payment for:', studentID, feeName); // Debugging line
    updatePaymentStatus(studentID, feeName, 'Paid');
}

function rejectPayment(studentID, feeName) {
    console.log('Rejecting payment for:', studentID, feeName); // Debugging line
    updatePaymentStatus(studentID, feeName, 'Not Paid');
}

function updatePaymentStatus(studentID, feeName, status) {
    fetch('update_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'studentID': studentID,
            'feeName': feeName,
            'status': status
        })
    })
    .then(response => response.text())
    .then(data => {
        console.log('Response:', data); // Log the response
        if (data === "Success") {
            alert(`Payment status updated to ${status} for Student ID: ${studentID} and Fee: ${feeName}`);
            location.reload(); // Reload the page to reflect changes
        } else {
            alert(data); // Show detailed error message
        }
    })
    .catch(error => console.error('Error:', error));
}

document.getElementById('organizationSelect').addEventListener('change', filterTable);

function filterTable() {
    const searchInput = document.querySelector('.search-bar').value.toLowerCase();
    const filterDate = document.getElementById('filter-date').value;
    const selectedOrg = document.getElementById('organizationSelect').value;
    const table = document.querySelector('table tbody');
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const studentID = row.cells[0].textContent.toLowerCase();
        const name = row.cells[1].textContent.toLowerCase();
        const feeName = row.cells[2].textContent.toLowerCase();
        const datePaid = row.cells[4].textContent;
        const orgID = row.getAttribute('data-org-id');

        const matchesSearch = studentID.includes(searchInput) || name.includes(searchInput) || feeName.includes(searchInput);
        const matchesDate = !filterDate || datePaid === filterDate;
        const matchesOrg = !selectedOrg || orgID === selectedOrg;

        if (matchesSearch && matchesDate && matchesOrg) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
    </script>
</body>
</html>

