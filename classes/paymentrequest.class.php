<?php
require_once 'database.class.php';

class PaymentRequest {
    private $db;
    private $lastError;

    public function __construct() {
        try {
            $this->db = Database::getInstance()->connect();
            error_log("Database connection established.");
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error establishing database connection: " . $this->lastError);
        }
    }

    public function createPaymentRequest($studentID, $feeID, $schoolYear, $semester) {
        try {
            // Insert the payment request without the student's name
            $sql = "
                INSERT INTO payment_requests (StudentID, fee_id, school_year, semester, DatePaid, Status) 
                VALUES (:studentID, :feeID, :schoolYear, :semester, NOW(), 'Pending')
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating payment request: " . $e->getMessage());
            return false;
        }
    }

    public function updatePaymentRequest($studentID, $feeID, $schoolYear, $semester) {
        try {
            $sql = "
                UPDATE payment_requests 
                SET DatePaid = NOW(), Status = 'Paid' 
                WHERE StudentID = :studentID 
                AND fee_id = :feeID 
                AND school_year = :schoolYear 
                AND semester = :semester
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating payment request: " . $e->getMessage());
            return false;
        }
    }

    public function paymentRequestExists($studentID, $feeID, $schoolYear, $semester) {
        try {
            $sql = "
                SELECT COUNT(*) 
                FROM payment_requests 
                WHERE StudentID = :studentID 
                AND fee_id = :feeID 
                AND school_year = :schoolYear 
                AND semester = :semester
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking payment request existence: " . $e->getMessage());
            return false;
        }
    }

    public function getFilteredPaymentRequests($organizationIDs, $selectedOrg) {
        try {
            $orgFilter = '';
            if (!empty($selectedOrg)) {
                $orgFilter = 'AND f.OrganizationID = :selectedOrg';
            } elseif (!empty($organizationIDs)) {
                $orgFilter = 'AND f.OrganizationID IN (' . implode(',', array_map('intval', $organizationIDs)) . ')';
            }

            $sql = "
                SELECT 
                    pr.StudentID,
                    s.Name,
                    f.FeeName,
                    f.Amount,
                    pr.PaymentType,
                    pr.DatePaid,
                    COALESCE(pr.Status, 'Not Paid') AS Status,
                    f.FeeID
                FROM 
                    fees f
                LEFT JOIN 
                    payment_requests pr 
                ON 
                    f.FeeID = pr.fee_id 
                LEFT JOIN
                    organizations o
                ON
                    f.OrganizationID = o.OrganizationID
                JOIN
                    staff s
                ON
                    s.OrganizationID = o.OrganizationID
                WHERE 
                    1=1
                    $orgFilter
            ";

            $stmt = $this->db->prepare($sql);
            if (!empty($selectedOrg)) {
                $stmt->bindParam(':selectedOrg', $selectedOrg, PDO::PARAM_INT);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getFilteredPaymentRequests: " . $e->getMessage());
            return [];
        }
    }

    private function getCurrentAcademicPeriod() {
        try {
            $sql = "SELECT school_year, semester FROM academic_periods WHERE is_current = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching current academic period: " . $e->getMessage());
            return false;
        }
    }

    public function updatePaymentStatus($studentID, $feeID, $status, $staffID) {
        try {
            $sql = "UPDATE payment_requests SET Status = :status WHERE StudentID = :studentID AND fee_id = :feeID";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Log the SQL query and parameters
            error_log("SQL Query: " . $sql);
            error_log("Parameters: status=$status, studentID=$studentID, feeID=$feeID");

            return $result;
        } catch (PDOException $e) {
            error_log("Error in updatePaymentStatus: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentRequestCount($schoolYear, $semester) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM payment_requests WHERE school_year = :schoolYear AND semester = :semester");
            $stmt->bindParam(':schoolYear', $schoolYear);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error fetching payment request count: " . $this->lastError . "\n", 3, __DIR__ . '/debug.log');
            return 0;
        }
    }

    public function getPendingPaymentRequestCount($schoolYear, $semester) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM payment_requests WHERE status = 'pending' AND school_year = :schoolYear AND semester = :semester");
            $stmt->bindParam(':schoolYear', $schoolYear);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error fetching pending payment request count: " . $this->lastError . "\n", 3, __DIR__ . '/debug.log');
            return 0;
        }
    }

    public function getTotalPaymentAmount($schoolYear, $semester) {
        try {
            $stmt = $this->db->prepare("SELECT SUM(amount) FROM payment_requests WHERE status = 'Paid' AND school_year = :schoolYear AND semester = :semester");
            $stmt->bindParam(':schoolYear', $schoolYear);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error fetching total payment amount: " . $this->lastError . "\n", 3, __DIR__ . '/debug.log');
            return 0;
        }
    }

    public function getLastError() {
        return $this->lastError;
    }


    public function getFeesStatusByStudent($studentId, $schoolYear, $semester) {
        try {
            $sql = "
                SELECT 
                    o.OrgName, 
                    f.FeeName, 
                    f.Amount, 
                    COALESCE(pr.Status, 'Not Paid') AS Status
                FROM 
                    fees f
                LEFT JOIN 
                    payment_requests pr 
                ON 
                    f.FeeID = pr.fee_id 
                    AND pr.StudentID = :studentId 
                    AND pr.school_year = :schoolYear 
                    AND pr.semester = :semester
                LEFT JOIN
                    organizations o
                ON
                    f.OrganizationID = o.OrganizationID
                WHERE 
                    f.school_year = :schoolYear 
                    AND f.semester = :semester
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllPaymentRequestsForCurrentPeriod($organizationIDs) {
        try {
            // Ensure organization IDs are properly quoted for SQL
            $organizationIDs = array_map(function($id) {
                return "'" . $id . "'";
            }, $organizationIDs);

            $sql = "
                SELECT pr.*, pr.StudentID,
                    CONCAT(s.first_name, ' ', s.MI, ' ', s.last_name) AS Name,
                    'Cash' AS PaymentType,
                     f.FeeName, f.Amount, f.FeeID
                FROM payment_requests pr
                JOIN student s ON pr.StudentID = s.StudentID
                JOIN fees f ON pr.fee_id = f.FeeID
                WHERE f.school_year = (SELECT school_year FROM academic_periods WHERE is_current = 1)
                AND f.semester = (SELECT semester FROM academic_periods WHERE is_current = 1)
                AND f.OrganizationID IN (" . implode(',', $organizationIDs) . ")
                ORDER BY pr.DatePaid DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log the results to check for NULL values
            error_log("SQL Query: " . $sql);
            error_log("Organization IDs: " . implode(',', $organizationIDs));
            error_log("Results: " . print_r($results, true));

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getAllPaymentRequestsForCurrentPeriod: " . $e->getMessage());
            return [];
        }
    }
}
?>