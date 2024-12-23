<?php
require_once 'database.class.php';

class PaymentRequest {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createPaymentRequest($studentID, $feeID, $schoolYear, $semester) {
        try {
            $sql = "INSERT INTO payment_requests (StudentID, fee_id, school_year, semester, Status) 
                    VALUES (:studentID, :feeID, :schoolYear, :semester, 'Pending')";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            error_log("CreatePaymentRequest executed successfully for StudentID=$studentID, FeeID=$feeID");
            return true;
        } catch (PDOException $e) {
            error_log("Error in createPaymentRequest: " . $e->getMessage());
            return false;
        }
    }

    public function updatePaymentRequest($studentID, $feeID, $schoolYear, $semester) {
        try {
            $updateSql = "UPDATE payment_requests SET Status = 'Pending' WHERE StudentID = :studentID AND fee_id = :feeID AND school_year = :schoolYear AND semester = :semester";
            $updateStmt = $this->db->connect()->prepare($updateSql);
            $updateStmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $updateStmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $updateStmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $updateStmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $updateStmt->execute();
            error_log("UpdatePaymentRequest executed successfully for StudentID=$studentID, FeeID=$feeID");
            return true;
        } catch (PDOException $e) {
            error_log("Error in updatePaymentRequest: " . $e->getMessage());
            return false;
        }
    }

    public function paymentRequestExists($studentID, $feeID, $schoolYear, $semester) {
        try {
            $checkSql = "SELECT COUNT(*) FROM payment_requests WHERE StudentID = :studentID AND fee_id = :feeID AND school_year = :schoolYear AND semester = :semester";
            $checkStmt = $this->db->connect()->prepare($checkSql);
            $checkStmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $checkStmt->bindParam(':feeID', $feeID, PDO::PARAM_INT);
            $checkStmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $checkStmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();
            error_log("Payment request exists check: StudentID=$studentID, FeeID=$feeID, Count=$count");
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Error in paymentRequestExists: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentRequestsForCurrentPeriod($orgId = null, $staffStudentId = null) {
        try {
            $sql = "
                SELECT 
                    pr.StudentID,
                    CONCAT(a.first_name, ' ', a.last_name) AS Name,
                    f.FeeName,
                    f.Amount,
                    'Cash' AS PaymentType,
                    pr.DatePaid,
                    pr.Status,
                    o.OrganizationID,
                    o.OrgName
                FROM 
                    payment_requests pr
                JOIN 
                    account a ON pr.StudentID = a.ID
                JOIN 
                    fees f ON pr.fee_id = f.FeeID
                JOIN 
                    organizations o ON f.OrganizationID = o.OrganizationID
                JOIN 
                    staff st ON o.OrganizationID = st.OrganizationID
                JOIN 
                    academic_periods ap ON pr.school_year = ap.school_year 
                    AND pr.semester = ap.semester
                WHERE 
                    ap.is_current = 1
                    AND st.StudentID = :staffStudentId";

            if ($orgId && $orgId !== '') {
                $sql .= " AND o.OrganizationID = :orgId";
            }

            $sql .= " ORDER BY pr.DatePaid DESC";

            $stmt = $this->db->connect()->prepare($sql);
            
            // Always bind staffStudentId
            $stmt->bindParam(':staffStudentId', $staffStudentId, PDO::PARAM_STR);
            
            if ($orgId && $orgId !== '') {
                $stmt->bindParam(':orgId', $orgId, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error fetching payment requests: " . $e->getMessage());
            return [];
        }
    }

    public function updatePaymentStatus($studentID, $feeName, $status, $staffID) {
        try {
            $sql = "UPDATE payment_requests 
                    SET Status = :status, staffID = :staffID 
                    WHERE StudentID = :studentID AND fee_id = (SELECT FeeID FROM fees WHERE FeeName = :feeName)";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':staffID', $staffID, PDO::PARAM_INT);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->bindParam(':feeName', $feeName, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentRequestCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM payment_requests";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error fetching payment request count: " . $e->getMessage());
            return 0;
        }
    }

    public function getPendingPaymentRequestCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM payment_requests WHERE Status = 'Pending'";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Error fetching pending payment request count: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalPaymentAmount() {
        try {
            $sql = "
                SELECT SUM(f.Amount) as totalAmount
                FROM payment_requests pr
                JOIN fees f ON pr.fee_id = f.FeeID
            ";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['totalAmount'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error fetching total payment amount: " . $e->getMessage());
            return 0;
        }
    }
}
