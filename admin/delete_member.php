<?php
require_once '../classes/database.class.php';

if (isset($_POST['student_id']) && isset($_POST['org_id'])) {
    $studentID = $_POST['student_id'];
    $orgID = $_POST['org_id'];
    $db = Database::getInstance()->connect();

    $stmt = $db->prepare("DELETE FROM staff WHERE StudentID = ? AND OrganizationID = ?");
    $stmt->execute([$studentID, $orgID]);

    echo json_encode(['status' => 'success']);
}
?>