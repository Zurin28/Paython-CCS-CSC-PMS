<?php
require_once "database.class.php";

class Organization {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // CREATE
    function createOrganization($organizationID, $school_year, $semester, $orgName) {
        try {
            $sql = "INSERT INTO organizations (OrganizationID, school_year, semester, OrgName) 
                    VALUES (:organizationID, :school_year, :semester, :orgName)";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':organizationID', $organizationID, PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->bindParam(':orgName', $orgName, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Create organization error: " . $e->getMessage());
            return false;
        }
    }

    // READ
    function getOrganizationById($organizationID) {
        try {
            $sql = "SELECT * FROM organizations WHERE OrganizationID = :organizationID";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':organizationID', $organizationID, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get organization error: " . $e->getMessage());
            return false;
        }
    }

    function getAllOrganizations() {
        try {
            $sql = "SELECT * FROM organizations";
            $qry = $this->db->connect()->prepare($sql);
            $qry->execute();
            return $qry->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all organizations error: " . $e->getMessage());
            return false;
        }
    }

    function getTotalOrganizations($school_year, $semester) {
        try {
            $sql = "SELECT COUNT(*) FROM organizations WHERE school_year = :school_year AND semester = :semester";
            $qry = $this->db->connect()->prepare($sql);
            $qry->execute([':school_year' => $school_year, ':semester' => $semester]);
            return $qry->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total organizations: " . $e->getMessage());
            return 0;
        }
    }

    public function getOrganizationsByPeriod($schoolYear, $semester) {
        try {
            $sql = "SELECT * FROM organizations WHERE school_year = :schoolYear AND semester = :semester";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE
    function updateOrganization($organizationID, $school_year, $semester, $orgName) {
        try {
            $sql = "UPDATE organizations 
                    SET school_year = :school_year, 
                        semester = :semester, 
                        OrgName = :orgName 
                    WHERE OrganizationID = :organizationID";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':organizationID', $organizationID, PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->bindParam(':orgName', $orgName, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Update organization error: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    function deleteOrganization($organizationID) {
        try {
            $sql = "DELETE FROM organizations WHERE OrganizationID = :organizationID";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':organizationID', $organizationID, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Delete organization error: " . $e->getMessage());
            return false;
        }
    }

    // Additional functions
    public function getUserDetails($studentID) {
        try {
            $sql = "SELECT first_name, last_name FROM account WHERE ID = :studentID";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':studentID', $studentID, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user details error: " . $e->getMessage());
            return false;
        }
    }

    public function addOrganization($orgID, $organizationName, $schoolYear, $semester) {
        try {
            $sql = "INSERT INTO organizations (OrganizationID, OrgName, school_year, semester) 
                    VALUES (:orgID, :organizationName, :schoolYear, :semester)";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->bindParam(':orgID', $orgID, PDO::PARAM_STR);
            $stmt->bindParam(':organizationName', $organizationName, PDO::PARAM_STR);
            $stmt->bindParam(':schoolYear', $schoolYear, PDO::PARAM_STR);
            $stmt->bindParam(':semester', $semester, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Add organization error: " . $e->getMessage());
            return false;
        }
    }
}
?>
