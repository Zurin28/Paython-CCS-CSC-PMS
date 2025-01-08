<?php
session_start();
require_once '../classes/database.class.php';
require_once '../classes/paymentrequest.class.php';

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

$staffID = $_SESSION['StaffID'];
$studentID = isset($_POST['studentID']) ? $_POST['studentID'] : null;
$feeID = isset($_POST['feeID']) ? $_POST['feeID'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;

error_log("Received data: studentID=$studentID, feeID=$feeID, status=$status, staffID=$staffID");

if ($studentID === null || $feeID === null || $status === null) {
    error_log('Error: Missing required POST parameters');
    echo 'Error: Missing required POST parameters';
    exit;
}

$paymentRequest = new PaymentRequest();
$result = $paymentRequest->updatePaymentStatus($studentID, $feeID, $status, $staffID);

if ($result) {
    echo 'Success';
} else {
    error_log('Error updating payment status: ' . $paymentRequest->getLastError());
    echo 'Error updating payment status: ' . $paymentRequest->getLastError();
}
?>