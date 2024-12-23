<?php 
require_once "database.class.php";

class Staff {
    protected $db;

    function __construct() {
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
}
?>
