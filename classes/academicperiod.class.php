<?php
require_once 'database.class.php';

class AcademicPeriod {
    private $db;
    private $conn;

    function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    function addPeriod($school_year, $semester, $start_date, $end_date) {
        try {
            $sql = "INSERT INTO academic_periods (school_year, semester, start_date, end_date, is_current) 
                    VALUES (:school_year, :semester, :start_date, :end_date, 0)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':school_year' => $school_year,
                ':semester' => $semester,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
        } catch (PDOException $e) {
            error_log("Error adding period: " . $e->getMessage());
            return false;
        }
    }

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

            $stmt = $this->conn->prepare($sql);
            
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':school_year' => $school_year,
                ':semester' => $semester
            ]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking period existence: " . $e->getMessage());
            return false;
        }
    }

    function setCurrentPeriod($school_year, $semester) {
        try {
            $this->conn->beginTransaction();

            // First, set all periods to not current
            $sql1 = "UPDATE academic_periods SET is_current = 0";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute();

            // Then, set the selected period as current
            $sql2 = "UPDATE academic_periods 
                     SET is_current = 1 
                     WHERE school_year = :school_year 
                     AND semester = :semester";
            
            $stmt2 = $this->conn->prepare($sql2);
            $result = $stmt2->execute([
                ':school_year' => $school_year,
                ':semester' => $semester
            ]);

            $this->conn->commit();
            return $result;
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error setting current period: " . $e->getMessage());
            return false;
        }
    }

    function getCurrentPeriod() {
        try {
            $sql = "SELECT school_year, semester FROM academic_periods WHERE is_current = 1 LIMIT 1";
            $stmt = $this->db->connect()->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCurrentPeriod: " . $e->getMessage());
            return false;
        }
    }

    // function getCurrentPeriod() {
    //     try {
    //         $sql = "SELECT * FROM academic_periods WHERE is_current = 1 LIMIT 1";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->execute();
    //         return $stmt->fetch(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Error getting current period: " . $e->getMessage());
    //         return null;
    //     }
    // }
} 