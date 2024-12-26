<?php
require_once '../classes/database.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $orgID = $data['org_id'];

    try {
        $db = Database::getInstance()->connect();
        $db->beginTransaction();

        // Delete members
        $stmt = $db->prepare("DELETE FROM staff WHERE OrganizationID = ?");
        $stmt->execute([$orgID]);

        // Delete payments
        $stmt = $db->prepare("DELETE FROM fees WHERE OrganizationID = ?");
        $stmt->execute([$orgID]);

        // Delete organization
        $stmt = $db->prepare("DELETE FROM organizations WHERE OrganizationID = ?");
        $stmt->execute([$orgID]);

        $db->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>