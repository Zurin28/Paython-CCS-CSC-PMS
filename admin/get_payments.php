<?php
require_once '../classes/database.class.php';

if (isset($_GET['org_id'])) {
    $orgID = $_GET['org_id'];
    $db = Database::getInstance()->connect();

    $stmt = $db->prepare("SELECT * FROM fees WHERE OrganizationID = ?");
    $stmt->execute([$orgID]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Payments fetched for OrganizationID $orgID: " . print_r($payments, true), 3, __DIR__ . '/debug.log');

    echo json_encode($payments);
}
?>