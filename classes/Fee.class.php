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

function __construct() {
    $this->db = Database::getInstance();
}

function viewFees() {
    // Get current academic period
    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();
    if (!$currentPeriod) {
        return [];
    }

    $sql = "SELECT * FROM Fees WHERE school_year = :school_year AND semester = :semester";
    $qry = $this->db->connect()->prepare($sql);
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

        $qry = $this->db->connect()->prepare($sql);
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
    $qry = $this->db->connect()->prepare($sql);

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
function addPayment($orgID, $feeID, $feeName, $amount, $dueDate, $description, $schoolYear, $semester) {
    try {
        // Check if organization exists for the current academic period
        $stmt = $this->db->connect()->prepare("SELECT * FROM organizations WHERE OrganizationID = ? AND school_year = ? AND semester = ?");
        $stmt->execute([$orgID, $schoolYear, $semester]);
        $organization = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$organization) {
            throw new Exception("Organization ID does not exist for the current academic period");
        }

        // Insert payment into fees table
        $sql = "INSERT INTO fees (OrganizationID, FeeID, FeeName, Amount, DueDate, Description, school_year, semester) 
                VALUES (:orgID, :feeID, :feeName, :amount, :dueDate, :description, :schoolYear, :semester)";
        $qry = $this->db->connect()->prepare($sql);
        $qry->bindParam(':orgID', $orgID, PDO::PARAM_STR);
        $qry->bindParam(':feeID', $feeID, PDO::PARAM_STR);
        $qry->bindParam(':feeName', $feeName, PDO::PARAM_STR);
        $qry->bindParam(':amount', $amount, PDO::PARAM_STR);
        $qry->bindParam(':dueDate', $dueDate, PDO::PARAM_STR);
        $qry->bindParam(':description', $description, PDO::PARAM_STR);
        $qry->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
        $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
        $qry->execute();

        return ['status' => 'success'];
    } catch (PDOException $e) {
        error_log("Add payment error: " . $e->getMessage(), 3, __DIR__ . '/../admin/debug.log');
        return ['status' => 'error', 'message' => $e->getMessage()];
    } catch (Exception $e) {
        error_log("Add payment error: " . $e->getMessage(), 3, __DIR__ . '/../admin/debug.log');
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

}
?>
