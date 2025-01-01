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
        // Fetch the student's name from the student table
        $sql = "
            SELECT CONCAT(first_name, ' ', MI, ' ', last_name) AS Name 
            FROM student 
            WHERE StudentID = :studentID
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            error_log("Student not found: StudentID=$studentID");
            return false;
        }

        $name = $student['Name'];

        // Insert the payment request with the student's name
        $sql = "
            INSERT INTO payment_requests (StudentID, fee_id, school_year, semester, DatePaid, Status, Name) 
            VALUES (:studentID, :feeID, :schoolYear, :semester, NOW(), 'Paid', :name)
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
        $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
        $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
        $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function updatePaymentRequest($studentID, $feeID, $schoolYear, $semester) {
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
    }

    public function paymentRequestExists($studentID, $feeID, $schoolYear, $semester) {
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
            // Ensure the staffID exists in the staff table
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM staff WHERE staffID = :staffID");
            $stmt->bindParam(':staffID', $staffID);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                $this->lastError = "Invalid staffID: $staffID";
                error_log("Error updating payment status: " . $this->lastError);
                return false;
            }

            $stmt = $this->db->prepare("
                UPDATE payment_requests
                SET status = :status, staffID = :staffID
                WHERE studentID = :studentID AND fee_id = :feeID
            ");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':staffID', $staffID);
            $stmt->bindParam(':studentID', $studentID);
            $stmt->bindParam(':feeID', $feeID);
            $result = $stmt->execute();
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                error_log("SQL Error: " . $this->lastError);
            }
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error updating payment status: " . $this->lastError);
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

    public function getAllPaymentRequestsForCurrentPeriod() {
        try {
            $currentPeriod = $this->getCurrentAcademicPeriod();
            if (!$currentPeriod) {
                throw new Exception("Current academic period not found.");
            }

            $sql = "
                SELECT DISTINCT
                    pr.StudentID,
                    CONCAT(s.first_name, ' ', s.MI, ' ', s.last_name) AS Name,
                    f.FeeName,
                    f.Amount,
                    'Cash' AS PaymentType,
                    pr.DatePaid,
                    COALESCE(pr.Status, 'Not Paid') AS Status,
                    f.FeeID
                FROM 
                    payment_requests pr
                LEFT JOIN 
                    fees f ON f.FeeID = pr.fee_id
                LEFT JOIN
                    student s ON s.StudentID = pr.StudentID
                WHERE 
                    pr.school_year = :schoolYear 
                    AND pr.semester = :semester
            ";

            error_log("SQL Query: " . $sql);
            error_log("Parameters: schoolYear=" . $currentPeriod['school_year'] . ", semester=" . $currentPeriod['semester']);

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':schoolYear', $currentPeriod['school_year'], PDO::PARAM_STR);
            $stmt->bindParam(':semester', $currentPeriod['semester'], PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log the results to check for NULL values
            foreach ($results as $result) {
                error_log("Result: " . print_r($result, true));
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getAllPaymentRequestsForCurrentPeriod: " . $e->getMessage());
            return [];
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return [];
        }
    }
}
?>