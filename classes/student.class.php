<?php
require_once "database.class.php";
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);
class Student {
    protected $db;

    function __construct() {
        $this->db = Database::getInstance();
    }

    function createStudent($studentId, $first_name, $last_name, $MI, $WmsuEmail, $password, $course, $year, $section, $school_year, $semester) {
        try {
            $sql = "INSERT INTO student (StudentID, first_name, last_name, MI, WmsuEmail, Password, Course, Year, Section, school_year, semester) 
                    VALUES (:studentId, :first_name, :last_name, :MI, :WmsuEmail, :password, :course, :year, :section, :school_year, :semester)";
            
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->bindParam(':first_name', $first_name, PDO::PARAM_STR);
            $qry->bindParam(':last_name', $last_name, PDO::PARAM_STR);
            $qry->bindParam(':MI', $MI, PDO::PARAM_STR);
            $qry->bindParam(':WmsuEmail', $WmsuEmail, PDO::PARAM_STR);
            $qry->bindParam(':password', $password, PDO::PARAM_STR);
            $qry->bindParam(':course', $course, PDO::PARAM_STR);
            $qry->bindParam(':year', $year, PDO::PARAM_STR);
            $qry->bindParam(':section', $section, PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Create student error: " . $e->getMessage());
            return false;
        }
    }

    function studentExists($studentId) {
        try {
            $sql = "SELECT COUNT(*) FROM student WHERE StudentID = :studentId";
            $qry = $this->db->connect()->prepare($sql);
            $qry->execute([':studentId' => $studentId]);
            return $qry->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Check student exists error: " . $e->getMessage());
            return false;
        }
    }

    function getStudentFeeDetails() {
        try {
            $sql = "
                SELECT 
                    s.StudentID,
                    CONCAT(s.first_name, ' ', s.last_name) AS Name,
                    s.Course,
                    s.Year,
                    s.Section,
                    f.FeeName,
                    f.Amount,
                    COALESCE(pr.Status, 'Not Paid') as Status
                FROM 
                    student s
                CROSS JOIN 
                    fees f
                LEFT JOIN 
                    payment_requests pr ON s.StudentID = pr.StudentID AND f.FeeID = pr.fee_id
                WHERE 
                    f.school_year = (SELECT school_year FROM academic_periods WHERE is_current = 1)
                    AND f.semester = (SELECT semester FROM academic_periods WHERE is_current = 1)
                ORDER BY 
                    s.StudentID, f.FeeName";

            $qry = $this->db->connect()->prepare($sql);
            
            // Log the SQL query
            error_log("Executing SQL: " . $sql);

            $qry->execute();

            $results = $qry->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug output
            if (empty($results)) {
                error_log("No data returned from getStudentFeeDetails.");
            } else {
                error_log("Data fetched successfully: " . print_r($results, true));
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getStudentFeeDetails: " . $e->getMessage());
            return [];
        }
    }
}