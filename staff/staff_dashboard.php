<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - PayThon</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../css/staffbar.css">
    <link rel="stylesheet" href="../css/staff_dashboard.css">
    <style>
        .status.pending {
            color: orange;
        }
        .status.paid {
            color: green;
        }
        .status.not-paid {
            color: red;
        }
    </style>
</head>
<body>
    <?php include 'staffbar.php'; ?>

    <div class="content-wrapper">
        <!-- Bento Grid Layout -->
        <div class="bento-grid">
            <!-- Statistics Cards -->
            <div class="bento-card payments">
                <i class='bx bx-money-withdraw'></i>
                <div class="card-info">
                    <h3>Total Payments</h3>
                    <p class="number">
                        <?php
                        // Fetch the total number of payment requests
                        require_once '../classes/paymentrequest.class.php';
                        $paymentRequest = new PaymentRequest();
                        echo $paymentRequest->getPaymentRequestCount();
                        ?>
                    </p>
                </div>
            </div>

            <div class="bento-card pending">
                <i class='bx bx-time'></i>
                <div class="card-info">
                    <h3>Pending Payment Requests</h3>
                    <p class="number">
                        <?php
                        // Fetch the number of pending payment requests
                        echo $paymentRequest->getPendingPaymentRequestCount();
                        ?>
                    </p>
                </div>
            </div>

            <div class="bento-card total">
                <i class='bx bx-wallet'></i>
                <div class="card-info">
                    <h3>Total Collection</h3>
                    <p class="number">
                        ₱<?php
                        // Fetch the total amount of all payment requests
                        echo number_format($paymentRequest->getTotalPaymentAmount(), 2);
                        ?>
                    </p>
                </div>
            </div>

            <!-- Recent Payments Table -->
            <div class="bento-card recent-payments">
                <div class="card-header">
                    <h3>Recent Payments</h3>
                    <a href="paymentmanagement.php" class="view-more-btn">
                        <span>View More</span>
                        <i class='bx bx-right-arrow-alt'></i>
                    </a>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Fee Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch recent payment requests
                            require_once '../classes/paymentrequest.class.php';
                            $paymentRequest = new PaymentRequest();
                            $recentPayments = $paymentRequest->getPaymentRequestsForCurrentPeriod();

                            ?>
                            <?php foreach (array_slice($recentPayments, 0, 6) as $payment): ?>
                                <tr>
                                    <td><?= htmlspecialchars($payment['Name']) ?></td>
                                    <td><?= htmlspecialchars($payment['FeeName']) ?></td>
                                    <td>₱<?= htmlspecialchars($payment['Amount']) ?></td>
                                    <td><?= htmlspecialchars($payment['DatePaid']) ?></td>
                                    <td>
                                        <span class="status <?= strtolower(str_replace(' ', '-', htmlspecialchars($payment['Status']))) ?>">
                                            <?= htmlspecialchars($payment['Status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Fees -->
            <div class="bento-card recent-fees">
                <div class="card-header">
                    <h3>Recent Fees Created</h3>
                    <a href="staff_organizations.php" class="view-more-btn">
                        <span>View More</span>
                        <i class='bx bx-right-arrow-alt'></i>
                    </a>
                </div>
                <div class="fees-list">
                    <div class="fee-item">
                        <div class="fee-info">
                            <h4>CSC Fee</h4>
                            <p>Created on Oct 10, 2024</p>
                            <span class="staff-name">Created by: Justine Carl Morgia</span>
                        </div>
                        <span class="fee-amount">₱500</span>
                    </div>
                    <div class="fees-list">
                    <div class="fee-item">
                        <div class="fee-info">
                            <h4>CSC Fee</h4>
                            <p>Created on Oct 10, 2024</p>
                            <span class="staff-name">Created by: Justine Carl Morgia</span>
                        </div>
                        <span class="fee-amount">₱500</span>
                    </div>
                    <div class="fees-list">
                    <div class="fee-item">
                        <div class="fee-info">
                            <h4>CSC Fee</h4>
                            <p>Created on Oct 10, 2024</p>
                            <span class="staff-name">Created by: Justine Carl Morgia</span>
                        </div>
                        <span class="fee-amount">₱500</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 