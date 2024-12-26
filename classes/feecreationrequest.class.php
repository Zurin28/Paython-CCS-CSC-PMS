<?php
require_once 'database.class.php';

class FeeCreationRequest {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new fee creation request
    public function createFeeRequest($feeName, $amount, $organizationId, $adminId, $dueDate, $description, $schoolYear, $semester) {
        try {
            $sql = "INSERT INTO fee_creation_requests (OrganizationID, AdminID, fee_name, amount, due_date, description, created_by, submit_date, status, school_year, semester) 
                    VALUES (:organizationId, :adminId, :feeName, :amount, :dueDate, :description, :createdBy, NOW(), 'Pending', :schoolYear, :semester)";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':organizationId', $organizationId, PDO::PARAM_STR);
            $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':feeName', $feeName, PDO::PARAM_STR);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':dueDate', $dueDate, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':createdBy', $adminId, PDO::PARAM_STR); // Assuming created_by is the admin ID
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error in createFeeRequest: " . $e->getMessage());
            return false;
        }
    }

    // Read fee creation requests
    public function getFeeRequests($schoolYear = null, $semester = null, $status = null) {
        try {
            $sql = "SELECT * FROM fee_creation_requests WHERE 1=1";
            if ($schoolYear) {
                $sql .= " AND school_year = :schoolYear";
            }
            if ($semester) {
                $sql .= " AND semester = :semester";
            }
            if ($status) {
                $sql .= " AND status = :status";
            }
            $stmt = $this->db->connect()->prepare($sql);
            if ($schoolYear) {
                $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            }
            if ($semester) {
                $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            }
            if ($status) {
                $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getFeeRequests: " . $e->getMessage());
            return [];
        }
    }

    // Update a fee creation request
    public function updateFeeRequest($requestId, $status, $schoolYear, $semester) {
        try {
            $sql = "UPDATE fee_creation_requests SET status = :status 
                    WHERE request_id = :requestId AND school_year = :schoolYear AND semester = :semester";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error in updateFeeRequest: " . $e->getMessage());
            return false;
        }
    }

    // Delete a fee creation request
    public function deleteFeeRequest($requestId, $schoolYear, $semester) {
        try {
            $sql = "DELETE FROM fee_creation_requests 
                    WHERE request_id = :requestId AND school_year = :schoolYear AND semester = :semester";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':requestId', $requestId, PDO::PARAM_INT);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error in deleteFeeRequest: " . $e->getMessage());
            return false;
        }
    }

    // Get organizations for a staff member
    public function getOrganizationsForStaff($studentId) {
        try {
            $sql = "SELECT DISTINCT o.OrganizationID, o.OrgName 
                    FROM organizations o
                    JOIN staff s ON o.OrganizationID = s.OrganizationID
                    WHERE s.StudentID = :studentId";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getOrganizationsForStaff: " . $e->getMessage());
            return [];
        }
    }

    // Get current school year and semester
    public function getCurrentAcademicPeriod() {
        try {
            $sql = "SELECT school_year, semester FROM academic_periods WHERE is_current = 1 LIMIT 1";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCurrentAcademicPeriod: " . $e->getMessage());
            return null;
        }
    }

    public function getStaffNameByStudentId($studentId) {
        try {
            $sql = "SELECT CONCAT(first_name, ' ', last_name) AS staffName FROM staff WHERE StudentID = :studentId";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error in getStaffNameByStudentId: " . $e->getMessage());
            return null;
        }
    }
} 