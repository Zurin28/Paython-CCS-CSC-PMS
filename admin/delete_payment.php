<?php
require_once '../classes/database.class.php';

if (isset($_POST['fee_id']) && isset($_POST['org_id'])) {
    $feeID = $_POST['fee_id'];
    $orgID = $_POST['org_id'];
    $db = Database::getInstance()->connect();

    $stmt = $db->prepare("DELETE FROM fees WHERE FeeID = ? AND OrganizationID = ?");
    $stmt->execute([$feeID, $orgID]);

    echo json_encode(['status' => 'success']);
}
?>