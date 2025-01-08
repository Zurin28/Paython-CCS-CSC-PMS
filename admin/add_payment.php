<?php
require_once '../classes/database.class.php';
require_once '../classes/Fee.class.php';
require_once '../classes/academicperiod.class.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['org_id']) && isset($data['fee_name']) && isset($data['amount']) && isset($data['due_date']) && isset($data['description'])) {
    $orgId = $data['org_id'];
    $feeName = $data['fee_name'];
    $amount = $data['amount'];
    $dueDate = $data['due_date'];
    $description = $data['description'];

    // Get the current academic period
    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();

    if ($currentPeriod) {
        $schoolYear = $currentPeriod['school_year'];
        $semester = $currentPeriod['semester'];

        error_log("Attempting to add payment: OrgID = $orgId, FeeName = $feeName, Amount = $amount, DueDate = $dueDate, Description = $description, SchoolYear = $schoolYear, Semester = $semester\n", 3, __DIR__ . '/debug.log');

        $fee = new Fee();
        $result = $fee->addPayment($orgId, $feeName, $amount, $dueDate, $description, $schoolYear, $semester);

        if ($result) {
            error_log("Successfully added payment: OrgID = $orgId, FeeName = $feeName\n", 3, __DIR__ . '/debug.log');
            echo json_encode(['status' => 'success']);
        } else {
            $errorInfo = $fee->getLastError();
            error_log("Failed to add payment: OrgID = $orgId, FeeName = $feeName, Error: " . $errorInfo . "\n", 3, __DIR__ . '/debug.log');
            echo json_encode(['status' => 'error', 'message' => 'Failed to add payment', 'error' => $errorInfo]);
        }
    } else {
        error_log("Failed to get current academic period\n", 3, __DIR__ . '/debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Failed to get current academic period']);
    }
} else {
    error_log("Invalid request: " . json_encode($data) . "\n", 3, __DIR__ . '/debug.log');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>