<?php
require_once '../classes/paymentrequest.class.php';
require_once '../classes/academicperiod.class.php';
session_start();
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

if (!isset($_SESSION['StudentID'])) {
    die("Unauthorized access.");
}

$studentID = $_SESSION['StudentID'];
$feeID = $_POST['feeID'] ?? null;

if (!$feeID) {
    error_log("Invalid fee ID: " . print_r($_POST, true));
    die("Invalid fee ID.");
}

// Get the current academic period
$academicPeriod = new AcademicPeriod();
$currentPeriod = $academicPeriod->getCurrentPeriod();

if ($currentPeriod) {
    $schoolYear = $currentPeriod['school_year'];
    $semester = $currentPeriod['semester'];

    $paymentRequest = new PaymentRequest();

    if ($paymentRequest->paymentRequestExists($studentID, $feeID, $schoolYear, $semester)) {
        $success = $paymentRequest->updatePaymentRequest($studentID, $feeID, $schoolYear, $semester);
        error_log("Update request: StudentID=$studentID, FeeID=$feeID, Success=$success");
    } else {
        $success = $paymentRequest->createPaymentRequest($studentID, $feeID, $schoolYear, $semester);
        error_log("Create request: StudentID=$studentID, FeeID=$feeID, Success=$success");
    }

    if ($success) {
        echo "Success";
    } else {
        error_log("Failed to process payment request for StudentID: $studentID, FeeID: $feeID");
        echo "Error";
    }
} else {
    error_log("No current academic period found for StudentID: $studentID");
    echo "No current academic period found.";
}

// Redirect to student_payment.php after 1 second
echo '<script>
    setTimeout(function() {
        window.location.href = "student_payment.php";
    }, 1000);
</script>';