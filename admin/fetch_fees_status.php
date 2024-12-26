<?php
require_once '../classes/database.class.php';
require_once '../classes/academicperiod.class.php';
require_once '../classes/paymentrequest.class.php';

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

header('Content-Type: application/json');

if (isset($_GET['studentId'])) {
    $studentId = $_GET['studentId'];

    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();

    if ($currentPeriod) {
        try {
            $paymentRequest = new PaymentRequest();
            $fees = $paymentRequest->getFeesStatusByStudent($studentId, $currentPeriod['school_year'], $currentPeriod['semester']);
            echo json_encode($fees);
        } catch (Exception $e) {
            error_log("Error fetching fees status: " . $e->getMessage());
            echo json_encode(['error' => 'Error fetching fees status: ' . $e->getMessage()]);
        }
    } else {
        error_log("No active academic period set.");
        echo json_encode(['error' => 'No active academic period set']);
    }
} else {
    error_log("Student ID not provided.");
    echo json_encode(['error' => 'Student ID not provided']);
}
?>