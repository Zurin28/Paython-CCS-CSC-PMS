<?php
require_once '../classes/database.class.php';
require_once '../classes/Fee.class.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['fee_id']) && isset($data['org_id'])) {
    $feeId = $data['fee_id'];
    $orgId = $data['org_id'];

    $fee = new Fee();
    $result = $fee->deletePayment($feeId, $orgId);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        error_log("Failed to delete payment: FeeID = $feeId, OrgID = $orgId\n", 3, __DIR__ . '/debug.log');
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete payment']);
    }
} else {
    error_log("Invalid request: " . json_encode($data) . "\n", 3, __DIR__ . '/debug.log');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>