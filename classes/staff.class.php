<?php 
require_once "database.class.php";

class Staff {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // CREATE
    function createStaff($staffId, $first_name, $last_name, $MI, $email, $password, $role) {
        try {
            $sql = "INSERT INTO staff (ID, first_name, last_name, MI, Email, Password, Role) 
                    VALUES (:staffId, :first_name, :last_name, :MI, :email, :password, :role)";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':staffId', $staffId, PDO::PARAM_INT);
            $qry->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $qry->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $qry->bindParam(':MI', $MI, PDO::PARAM_STR);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->bindParam(':password', $password, PDO::PARAM_STR);
            $qry->bindParam(':role', $role, PDO::PARAM_STR);
            $qry->execute();
            
            return $staffId;
        } catch (PDOException $e) {
            error_log("Create staff error: " . $e->getMessage());
            return false;
        }
    }

    // Function to check if a student exists
    public function studentExists($studentID) {
        try {
            $sql = "SELECT COUNT(*) FROM students WHERE ID = :studentID";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $qry->execute();
            return $qry->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check student exists error: " . $e->getMessage());
            return false;
        }
    }

    // Function to add a member to the staff table
    public function addMember($studentID, $position, $orgID, $schoolYear, $semester) {
        try {
            // Fetch student details
            $stmt = $this->db->connect()->prepare("SELECT * FROM student WHERE StudentID = ?");
            $stmt->execute([$studentID]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                throw new Exception("Student ID does not exist");
            }

            // Log student details
            error_log("Student details: " . print_r($student, true), 3, __DIR__ . '/../admin/debug.log');

            // Check if organization exists for the current academic period
            $stmt = $this->db->connect()->prepare("SELECT * FROM organizations WHERE OrganizationID = ? AND school_year = ? AND semester = ?");
            $stmt->execute([$orgID, $schoolYear, $semester]);
            $organization = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$organization) {
                throw new Exception("Organization ID does not exist for the current academic period");
            }

            // Ensure student details are populated
            $requiredFields = ['first_name', 'last_name', 'MI', 'WmsuEmail', 'Password', 'Section'];
            foreach ($requiredFields as $field) {
                if (empty($student[$field])) {
                    error_log("Missing student detail: " . $field, 3, __DIR__ . '/../admin/debug.log');
                    throw new Exception("Student details are incomplete");
                }
            }

            // Insert member into staff table
            $sql = "INSERT INTO staff (StudentID, Position, OrganizationID, school_year, semester, first_name, last_name, MI, WmsuEmail, Password, Section) 
                    VALUES (:studentID, :position, :orgID, :schoolYear, :semester, :first_name, :last_name, :MI, :WmsuEmail, :Password, :Section)";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $qry->bindParam(':position', $position, PDO::PARAM_STR);
            $qry->bindParam(':orgID', $orgID, PDO::PARAM_STR);
            $qry->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->bindParam(':first_name', $student['first_name'], PDO::PARAM_STR);
            $qry->bindParam(':last_name', $student['last_name'], PDO::PARAM_STR);
            $qry->bindParam(':MI', $student['MI'], PDO::PARAM_STR);
            $qry->bindParam(':WmsuEmail', $student['WmsuEmail'], PDO::PARAM_STR);
            $qry->bindParam(':Password', $student['Password'], PDO::PARAM_STR);
            $qry->bindParam(':Section', $student['Section'], PDO::PARAM_STR);
            $qry->execute();

            return true;
        } catch (PDOException $e) {
            error_log("Add member error: " . $e->getMessage(), 3, __DIR__ . '/../admin/debug.log');
            return false;
        } catch (Exception $e) {
            error_log("Add member error: " . $e->getMessage(), 3, __DIR__ . '/../admin/debug.log');
            return false;
        }
    }

    // READ
    function staffExists($email) {
        try {
            $sql = "SELECT COUNT(*) FROM staff WHERE Email = :email";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check staff exists error: " . $e->getMessage());
            return false;
        }
    }

    function fetch($email) {
        try {
            $sql = "SELECT * FROM staff WHERE Email = :email LIMIT 1";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fetch staff error: " . $e->getMessage());
            return false;
        }
    }

    function getById($studentId) {
        try {
            $sql = "SELECT * FROM staff WHERE StudentID = :studentId";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get staff error: " . $e->getMessage());
            return false;
        }
    }

    // UPDATE
    function update($id, $first_name, $last_name, $MI, $email) {
        try {
            $sql = "UPDATE staff 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        MI = :MI, 
                        Email = :email 
                    WHERE ID = :id";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':id', $id, PDO::PARAM_INT);
            $qry->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $qry->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $qry->bindParam(':MI', $MI, PDO::PARAM_STR);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Update staff error: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    function delete($id) {
        try {
            $sql = "DELETE FROM staff WHERE ID = :id";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':id', $id, PDO::PARAM_INT);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Delete staff error: " . $e->getMessage());
            return false;
        }
    }

    // Add this new method to the Staff class
    function getStaffOrganizations($studentId) {
        try {
            $sql = "SELECT DISTINCT o.* 
                    FROM organizations o
                    INNER JOIN (
                        SELECT OrganizationID 
                        FROM staff 
                        WHERE StudentID = :studentId
                        GROUP BY OrganizationID
                    ) s ON o.OrganizationID = s.OrganizationID
                    WHERE o.school_year = (SELECT school_year FROM academic_periods WHERE is_current = 1)
                    AND o.semester = (SELECT semester FROM academic_periods WHERE is_current = 1)
                    ORDER BY o.OrgName";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->execute();
            
            $result = $qry->fetchAll(PDO::FETCH_ASSOC);
            error_log("Staff organizations found: " . print_r($result, true)); // Debug log
            return $result;
        } catch (PDOException $e) {
            error_log("Get staff organizations error: " . $e->getMessage());
            return [];
        }
    }
}
?>
