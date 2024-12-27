<?php 
require_once "database.class.php";
class Fee{

public $FeeID;

public $orgID;
public $FeeName;
public $Amount;
public $Duedate;
public $Description;

public $status;

protected $db;
private $lastError;

function __construct() {
    $this->db = Database::getInstance()->connect();
}

function viewFees() {
    // Get current academic period
    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();
    if (!$currentPeriod) {
        return [];
    }

    $sql = "SELECT * FROM Fees WHERE school_year = :school_year AND semester = :semester";
    $qry = $this->db->prepare($sql);
    $qry->bindParam(':school_year', $currentPeriod['school_year']);
    $qry->bindParam(':semester', $currentPeriod['semester']);
    if ($qry->execute()) {
        return $qry->fetchAll();
    }
    return [];
}


  // In Fee.class.php
function getFeeStatus($student_id) {
    try {
        $sql = "
            SELECT 
                f.FeeID,
                f.FeeName,
                f.Amount,
                f.DueDate,
                o.OrgName as organization,
                COALESCE(pr.Status, 'Not Paid') as paymentStatus
            FROM 
                fees f
            JOIN 
                organizations o ON f.OrganizationID = o.OrganizationID
            LEFT JOIN 
                payment_requests pr ON f.FeeID = pr.fee_id AND pr.StudentID = :student_id
            WHERE 
                f.school_year = (SELECT school_year FROM academic_periods WHERE is_current = 1)
                AND f.semester = (SELECT semester FROM academic_periods WHERE is_current = 1)
            ORDER BY 
                o.OrgName, f.FeeName";

        $qry = $this->db->prepare($sql);
        $qry->bindParam(':student_id', $student_id, PDO::PARAM_INT);
        $qry->execute();

        return $qry->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getFeeStatus: " . $e->getMessage());
        return [];
    }
}



public function getOrganizationPayments($orgId, $schoolYear, $semester) {
    // Prepare the SQL query
    $sql = "SELECT * FROM payments WHERE org_id = :org_id AND school_year = :school_year AND semester = :semester";
    $qry = $this->db->prepare($sql);

    // Bind parameters
    $qry->bindParam(':org_id', $orgId, PDO::PARAM_INT);
    $qry->bindParam(':school_year', $schoolYear, PDO::PARAM_STR);
    $qry->bindParam(':semester', $semester, PDO::PARAM_STR);

    // Execute the query and fetch the results
    if ($qry->execute()) {
        return $qry->fetchAll(PDO::FETCH_ASSOC);
    }

    // Return an empty array if the query fails
    return [];
}




// Add this new method to insert a payment into the fees table
public function addPayment($orgId, $feeName, $amount, $dueDate, $description, $schoolYear, $semester) {
    try {
        $stmt = $this->db->prepare("INSERT INTO fees (OrganizationID, FeeName, Amount, DueDate, Description, school_year, semester) VALUES (:orgId, :feeName, :amount, :dueDate, :description, :schoolYear, :semester)");
        $stmt->bindParam(':orgId', $orgId);
        $stmt->bindParam(':feeName', $feeName);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':dueDate', $dueDate);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':schoolYear', $schoolYear);
        $stmt->bindParam(':semester', $semester);
        $result = $stmt->execute();
        error_log("SQL executed: " . $stmt->queryString . "\n", 3, __DIR__ . '/debug.log');
        return $result;
    } catch (PDOException $e) {
        $this->lastError = $e->getMessage();
        error_log("Error adding payment: " . $this->lastError . "\n", 3, __DIR__ . '/debug.log');
        return false;
    }
}

public function deletePayment($feeId, $orgId) {
    try {
        $stmt = $this->db->prepare("DELETE FROM fees WHERE FeeID = :feeId AND OrganizationID = :orgId");
        $stmt->bindParam(':feeId', $feeId);
        $stmt->bindParam(':orgId', $orgId);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error deleting payment: " . $e->getMessage());
        return false;
    }
}

public function getLastError() {
    return $this->lastError;
}

}
?>
