<?php
require_once '../classes/paymentrequest.class.php';
session_start();
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

if (isset($_POST['studentID']) && isset($_POST['feeName']) && isset($_POST['status'])) {
    $paymentRequest = new PaymentRequest();
    $studentID = $_POST['studentID'];
    $feeName = $_POST['feeName'];
    $status = $_POST['status'];
    $staffID = $_SESSION['staffID'];

    if ($paymentRequest->updatePaymentStatus($studentID, $feeName, $status, $staffID)) {
        echo "Success";
    } else {
        echo "Error updating status in database.";
    }
} else {
    echo "Invalid input.";
}
?>