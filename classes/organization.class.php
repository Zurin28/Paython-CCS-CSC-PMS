<?php
require_once "database.class.php";

class Organization {
    protected $db;

    function __construct() {
        $this->db = new Database;
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
}
?>
