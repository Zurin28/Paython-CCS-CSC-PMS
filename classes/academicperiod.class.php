<?php
require_once 'database.class.php';

class AcademicPeriod {
    protected $db;

    function __construct() {
        $this->db = Database::getInstance();
    }

    // CREATE
    function addPeriod($school_year, $semester, $start_date, $end_date) {
        try {
            $sql = "INSERT INTO academic_periods (school_year, semester, start_date, end_date, is_current) 
                    VALUES (:school_year, :semester, :start_date, :end_date, 0)";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $qry->bindParam(':end_date', $end_date, PDO::PARAM_STR);
            return $qry->execute();
        } catch (PDOException $e) {
            error_log("Error adding period: " . $e->getMessage());
            return false;
        }
    }

    // READ
    function getAllPeriods($searchQuery = '') {
        try {
            $sql = "SELECT * FROM academic_periods";
            $params = [];

            if (!empty($searchQuery)) {
                $sql .= " WHERE school_year LIKE :search 
                         OR semester LIKE :search";
                $params[':search'] = "%$searchQuery%";
            }

            $sql .= " ORDER BY school_year DESC, 
                      CASE semester 
                        WHEN '1st' THEN 1 
                        WHEN '2nd' THEN 2 
                        WHEN 'Summer' THEN 3 
                      END";

            $qry = $this->db->connect()->prepare($sql);
            
            if (!empty($params)) {
                $qry->execute($params);
            } else {
                $qry->execute();
            }

            return $qry->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting periods: " . $e->getMessage());
            return [];
        }
    }

    function periodExists($school_year, $semester) {
        try {
            $sql = "SELECT COUNT(*) FROM academic_periods 
                    WHERE school_year = :school_year 
                    AND semester = :semester";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();
            
            return $qry->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking period existence: " . $e->getMessage());
            return false;
        }
    }

    function setCurrentPeriod($school_year, $semester) {
        try {
            $this->db->connect()->beginTransaction();

            // First, set all periods to not current
            $sql1 = "UPDATE academic_periods SET is_current = 0";
            $qry1 = $this->db->connect()->prepare($sql1);
            $qry1->execute();

            // Then, set the selected period as current
            $sql2 = "UPDATE academic_periods 
                     SET is_current = 1 
                     WHERE school_year = :school_year 
                     AND semester = :semester";
            
            $qry2 = $this->db->connect()->prepare($sql2);
            $qry2->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry2->bindParam(':semester', $semester, PDO::PARAM_STR);
            $result = $qry2->execute();

            $this->db->connect()->commit();
            return $result;
        } catch (PDOException $e) {
            if ($this->db->connect()->inTransaction()) {
                $this->db->connect()->rollBack();
            }
            error_log("Error setting current period: " . $e->getMessage());
            return false;
        }
    }

    function getCurrentPeriod() {
        try {
            $sql = "SELECT school_year, semester FROM academic_periods WHERE is_current = 1 LIMIT 1";
            $qry = $this->db->connect()->prepare($sql);
            $qry->execute();
            return $qry->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCurrentPeriod: " . $e->getMessage());
            return false;
        }
    }
}
?>