<?php 
require_once '../classes/Fee.class.php';
require_once '../classes/organization.class.php';
require_once '../classes/academicperiod.class.php';
require_once '../classes/student.class.php';


ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['StudentID'])) {
    die("Unauthorized access. Please log in.");
}

$loggedInStudentID = $_SESSION['StudentID'];

$fee = new Fee();
$fees = $fee->getFeeStatus($loggedInStudentID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayThon - Payment Section</title>
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../header.css">
    <link rel="stylesheet" href="../student_payment.css">
    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            background-color: #e8f5e9;
            margin: 10% auto;
            padding: 40px;
            border: 1px solid #388e3c;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .close {
            color: #388e3c;
            float: right;
            font-size: 28px;
            font-weight: bold;
            margin-top: -10px;
        }
        .close:hover,
        .close:focus {
            color: #1b5e20;
            text-decoration: none;
            cursor: pointer;
        }
        .action-button {
            background-color: #4caf50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .action-button:hover {
            background-color: #45a049;
        }
        .action-button.pending {
            background-color: #ffeb3b;
            color: black;
        }
        .modal p {
            font-size: 18px;
            color: #2e7d32;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include '../header.staff.php'; ?>

<main class="main">
    <div class="title-section">
        <h1 class="title">Student Payments</h1>
        <div class="filter-container">
            <select class="filter-dropdown" id="statusFilter">
                <option value="all">Status</option>
                <option value="Paid">Paid</option>
                <option value="Not Paid">Not Paid</option>
                <option value="Pending">Pending</option>
            </select>
        </div>
    </div>
    
    <table class="payment-table">
        <thead>
            <tr>
                <th>Organization</th>
                <th>Fee</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Due</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $displayedFees = [];
            foreach ($fees as $fee): 
                if (!in_array($fee['FeeID'], $displayedFees)):
                    $displayedFees[] = $fee['FeeID'];
            ?>
                <tr class="payment-row" data-fee-id="<?= htmlspecialchars($fee['FeeID']) ?>" data-status="<?= htmlspecialchars($fee['paymentStatus']) ?>">
                    <td><?= htmlspecialchars($fee['organization']) ?></td>
                    <td><?= htmlspecialchars($fee['FeeName']) ?></td>
                    <td><?= htmlspecialchars($fee['paymentStatus']) ?></td>
                    <td><?= htmlspecialchars($fee['Amount']) ?></td>
                    <td><?= htmlspecialchars($fee['DueDate']) ?></td>
                    <td>
                        <?php if ($fee['paymentStatus'] === 'Not Paid'): ?>
                            <button class="action-button pay-now">Pay Now</button>
                        <?php elseif ($fee['paymentStatus'] === 'Pending'): ?>
                            <button class="action-button pending" disabled>Pending</button>
                        <?php else: ?>
                            <button class="action-button paid" disabled>Paid</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php 
                endif;
            endforeach; 
            ?>
        </tbody>
    </table>
</main>

<!-- Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Have you already reached out to the organization personnel and are ready to pay?</p>
        <form id="paymentForm" method="post" action="staff.process.payment.php">
            <input type="hidden" name="feeID" id="feeID">
            <button type="submit" class="action-button pay-confirm">Pay</button>
        </form>
    </div>
</div>

<script>
    // Get modal elements
    var modal = document.getElementById("paymentModal");
    var span = document.getElementsByClassName("close")[0];
    var feeIDInput = document.getElementById("feeID");

    // Open modal when "Pay Now" button is clicked
    document.querySelectorAll('.pay-now').forEach(function(button) {
        button.addEventListener('click', function() {
            var feeID = this.closest('tr').getAttribute('data-fee-id');
            feeIDInput.value = feeID;
            modal.style.display = "block";
        });
    });

    // Close modal when "x" is clicked
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Close modal when clicking outside of the modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Filter table rows based on status
    document.getElementById('statusFilter').addEventListener('change', function() {
        var selectedStatus = this.value;
        var rows = document.querySelectorAll('.payment-row');

        rows.forEach(function(row) {
            if (selectedStatus === 'all' || row.getAttribute('data-status') === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>

<!-- Success Modal -->
<div id="successModal" class="success-modal">
    <div class="success-modal-content">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Done!</h1>
        <p class="success-message">Your Payment Has Been Processed Successfully</p>
    </div>
</div>

<script src="studentside.js"></script>
</body>
</html>
