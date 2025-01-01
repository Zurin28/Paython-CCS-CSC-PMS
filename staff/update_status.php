<?php
session_start();
require_once '../classes/database.class.php';
require_once '../classes/paymentrequest.class.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

if (!isset($_SESSION['staffID'])) {
    error_log('Error: Staff not logged in');
    echo 'Error: Staff not logged in';
    exit;
}

$staffID = $_SESSION['staffID'];
$studentID = $_POST['studentID'];
$feeID = $_POST['feeID'];
$status = $_POST['status'];

error_log("Received data: studentID=$studentID, feeID=$feeID, status=$status, staffID=$staffID");

$paymentRequest = new PaymentRequest();
$result = $paymentRequest->updatePaymentStatus($studentID, $feeID, $status, $staffID);

if ($result) {
    echo 'Success';
} else {
    error_log('Error updating payment status: ' . $paymentRequest->getLastError());
    echo 'Error updating payment status: ' . $paymentRequest->getLastError();
}
?>