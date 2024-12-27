<?php
require_once '../classes/database.class.php';
require_once '../classes/Staff.class.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['student_id']) && isset($data['org_id'])) {
    $studentId = $data['student_id'];
    $orgId = $data['org_id'];

    $staff = new Staff();
    $result = $staff->deleteMember($studentId, $orgId);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        error_log("Failed to delete member: StudentID = $studentId, OrgID = $orgId\n", 3, __DIR__ . '/debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete member']);
    }
} else {
    error_log("Invalid request: " . json_encode($data) . "\n", 3, __DIR__ . '/debug.log');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>