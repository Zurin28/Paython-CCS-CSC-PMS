<?php 
require_once "database.class.php";

class Account {
    protected $db;

    function __construct() {
        $this->db = Database::getInstance();
    }

    // CREATE
    function createAccount($studentId, $first_name, $last_name, $MI, $WmsuEmail, $password, $role, $school_year, $semester) {
        try {
            $sql = "INSERT INTO account (ID, first_name, last_name, MI, WmsuEmail, Password, Role, school_year, semester) 
                    VALUES (:studentId, :first_name, :last_name, :MI, :WmsuEmail, :password, :role, :school_year, :semester)";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $qry->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $qry->bindParam(':MI', $MI, PDO::PARAM_STR);
            $qry->bindParam(':WmsuEmail', $WmsuEmail, PDO::PARAM_STR);
            $qry->bindParam(':password', $password, PDO::PARAM_STR);
            $qry->bindParam(':role', $role, PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();
            
            return $studentId;
        } catch (PDOException $e) {
            error_log("Create account error: " . $e->getMessage());
            return false;
        }
    }

    // READ
    function accountExists($email) {
        try {
            $sql = "SELECT COUNT(*) FROM account WHERE WmsuEmail = :email";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check account exists error: " . $e->getMessage());
            return false;
        }
    }

    function login($email, $password, $school_year, $semester) {
        try {
            $sql = "SELECT * FROM account WHERE WmsuEmail = :email LIMIT 1";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();

            if ($qry->rowCount() === 0) {
                return "email_not_found";
            }

            $sql = "SELECT * FROM account 
                    WHERE WmsuEmail = :email 
                    AND school_year = :school_year 
                    AND semester = :semester 
                    LIMIT 1";
                    
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();

            if ($qry->rowCount() === 0) {
                return "not_in_period";
            }

            $record = $qry->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $record['Password'])) {
                return "success";
            }
            
            return "wrong_password";
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return "error";
        }
    }

    function fetch($email) {
        try {
            $sql = "SELECT * FROM account WHERE WmsuEmail = :email LIMIT 1";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':email', $email, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fetch account error: " . $e->getMessage());
            return false;
        }
    }

    function getById($id) {
        try {
            $sql = "SELECT * FROM account WHERE ID = :id";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':id', $id, PDO::PARAM_INT);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get account error: " . $e->getMessage());
            return false;
        }
    }

    // UPDATE
    function update($id, $first_name, $last_name, $MI, $WmsuEmail) {
        try {
            $sql = "UPDATE account 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        MI = :MI, 
                        WmsuEmail = :WmsuEmail 
                    WHERE ID = :id";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':id', $id, PDO::PARAM_INT);
            $qry->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $qry->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $qry->bindParam(':MI', $MI, PDO::PARAM_STR);
            $qry->bindParam(':WmsuEmail', $WmsuEmail, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Update account error: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    function delete($id) {
        try {
            $sql = "DELETE FROM account WHERE ID = :id";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':id', $id, PDO::PARAM_INT);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Delete account error: " . $e->getMessage());
            return false;
        }
    }
}
?>
