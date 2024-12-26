<?php
require_once "database.class.php";
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);
class Student {
    protected $db;

    function __construct() {
        $this->db = new Database();
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
            error_log("Error creating student: " . $e->getMessage());
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

    function getStudentFeeDetails($orgId = null, $staffStudentId = null) {
        try {
            error_log("Function called with orgId: " . ($orgId ? $orgId : "null") . " and staffStudentId: " . $staffStudentId);

            $sql = "
                SELECT 
                    s.StudentID,
                    CONCAT(s.first_name, ' ', s.last_name) AS Name,
                    s.Course,
                    s.Year,
                    s.Section,
                    f.FeeName,
                    f.Amount,
                    COALESCE(pr.Status, 'Not Paid') as Status,
                    o.OrganizationID,
                    o.OrgName
                FROM 
                    student s
                CROSS JOIN 
                    fees f
                LEFT JOIN 
                    payment_requests pr ON s.StudentID = pr.StudentID AND f.FeeID = pr.fee_id
                JOIN 
                    organizations o ON f.OrganizationID = o.OrganizationID
                JOIN 
                    staff st ON o.OrganizationID = st.OrganizationID
                WHERE 
                    f.school_year = (SELECT school_year FROM academic_periods WHERE is_current = 1)
                    AND f.semester = (SELECT semester FROM academic_periods WHERE is_current = 1)
                    AND st.StudentID = :staffStudentId";

            // Add specific organization filter if selected
            if ($orgId && $orgId !== '') {
                $sql .= " AND o.OrganizationID = :orgId";
            }

            $sql .= " ORDER BY s.StudentID, f.FeeName";

            error_log("SQL Query: " . $sql);

            $qry = $this->db->connect()->prepare($sql);
            
            // Always bind staffStudentId
            $qry->bindParam(':staffStudentId', $staffStudentId, PDO::PARAM_STR);
            
            if ($orgId && $orgId !== '') {
                $qry->bindParam(':orgId', $orgId, PDO::PARAM_STR);
                error_log("Binding orgId parameter: " . $orgId);
            }

            $qry->execute();
            $results = $qry->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Number of results: " . count($results));
            error_log("Results: " . print_r($results, true));

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getStudentFeeDetails: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    function getTotalStudents($school_year, $semester) {
        try {
            $sql = "SELECT COUNT(*) FROM student WHERE school_year = :school_year AND semester = :semester";
            $qry = $this->db->connect()->prepare($sql);
            $qry->execute([':school_year' => $school_year, ':semester' => $semester]);
            return $qry->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total students: " . $e->getMessage());
            return 0;
        }
    }

    function getStudentsByPeriod($school_year, $semester) {
        try {
            $sql = "SELECT * FROM student WHERE school_year = :school_year AND semester = :semester";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting students by period: " . $e->getMessage());
            return [];
        }
    }

    function searchStudentsByPeriod($searchQuery, $school_year, $semester) {
        try {
            $sql = "SELECT * FROM student WHERE (first_name LIKE :searchQuery OR last_name LIKE :searchQuery OR WmsuEmail LIKE :searchQuery) AND school_year = :school_year AND semester = :semester";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindValue(':searchQuery', "%$searchQuery%", PDO::PARAM_STR);
            $qry->bindParam(':school_year', $school_year, PDO::PARAM_STR);
            $qry->bindParam(':semester', $semester, PDO::PARAM_STR);
            $qry->execute();
            return $qry->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error searching students by period: " . $e->getMessage());
            return [];
        }
    }

    // DELETE
    function deleteByStudentId($studentId) {
        try {
            // Delete the student record
            $sql = "DELETE FROM student WHERE StudentID = :studentId";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Delete student error: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    function deleteStudentByStudentId($studentId) {
        try {
            // Delete the student record
            $sql = "DELETE FROM student WHERE StudentID = :studentId";
            $qry = $this->db->connect()->prepare($sql);
            $qry->bindParam(':studentId', $studentId, PDO::PARAM_INT);
            $qry->execute();
            
            return true;
        } catch (PDOException $e) {
            error_log("Delete student error: " . $e->getMessage());
            return false;
        }
    }
}