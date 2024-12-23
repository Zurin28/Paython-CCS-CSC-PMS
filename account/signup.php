<?php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');
error_reporting(E_ALL);

error_log("Starting signup process...");

require_once "../classes/account.class.php";
require_once "../classes/academicperiod.class.php";
require_once "../classes/student.class.php";
require_once "../classes/database.class.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get current academic period
    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();
    
    if (!$currentPeriod) {
        echo "<p style='color: red;'>No active academic period set. Please contact the administrator.</p>";
        exit;
    }

    // Collect and validate user input
    $studentId = trim($_POST['studentId']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $mi = trim($_POST['mi']);
    $wmsuEmail = trim($_POST['wmsuEmail']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $course = $_POST['course'];
    $year = $_POST['year'];
    
    // Debugging: Log passwords
    error_log("Password: $password");
    error_log("Confirm Password: $confirmPassword");
    
    // Convert year to proper format
    switch($year) {
        case "1": $year = "1st"; break;
        case "2": $year = "2nd"; break;
        case "3": $year = "3rd"; break;
        case "4": $year = "4th"; break;
        case "5": $year = "Over 4 years"; break;
    }
    
    $section = trim($_POST['section']);

    if ($password !== $confirmPassword) {
        echo "<p style='color: red;'>Passwords do not match. Please try again.</p>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $db = new Database();
        $conn = $db->connect();
        
        $accObj = new Account();
        $studentObj = new Student();

        // Check if account or student already exists
        if ($accObj->accountExists($wmsuEmail)) {
            echo "<p style='color: red;'>WMSU Email already exists. Please try again with different credentials.</p>";
        } else if ($studentObj->studentExists($studentId)) {
            echo "<p style='color: red;'>Student ID already exists. Please try again with different credentials.</p>";
        } else {
            try {
                // Start transaction
                $conn->beginTransaction();
                error_log("Transaction started");

                // Create account first
                $accountId = $accObj->createAccount(
                    $studentId,
                    $first_name, 
                    $last_name, 
                    $mi, 
                    $wmsuEmail, 
                    $hashedPassword,
                    'student',
                    $currentPeriod['school_year'],
                    $currentPeriod['semester']
                );

                error_log("Account creation result: " . ($accountId ? "Success ID: $accountId" : "Failed"));

                if ($accountId) {
                    // Use accountId for StudentID
                    $studentCreated = $studentObj->createStudent(
                        $accountId, // Use the account ID as the student ID
                        $first_name,
                        $last_name,
                        $mi,
                        $wmsuEmail,
                        $password,
                        $course,
                        $year,
                        $section,
                        $currentPeriod['school_year'],
                        $currentPeriod['semester']
                    );

                    error_log("Student creation result: " . ($studentCreated ? "Success" : "Failed"));

                    if ($studentCreated) {
                        $conn->commit();
                        error_log("Transaction committed successfully");
                        echo "<p style='color: green;'>Account created successfully. You can now log in.</p>";
                        echo "<meta http-equiv='refresh' content='3;url=login.php'>";
                        exit;
                    } else {
                        throw new Exception("Failed to create student record");
                    }
                } else {
                    throw new Exception("Failed to create account");
                }
            } catch (Exception $e) {
                $conn->rollBack();
                error_log("Signup error: " . $e->getMessage());
                echo "<p style='color: red;'>Failed to create account. Please try again later.</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="../css/signup.css">
</head>
<body>
    <h1 style="text-align: center;">Signup Page</h1>
    <form method="POST" action="">
        <label for="studentId">Student ID</label>
        <input type="text" id="studentId" name="studentId" required>

        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="mi">Middle Initial</label>
        <input type="text" id="mi" name="mi" maxlength="1" required>

        <label for="wmsuEmail">WMSU Email</label>
        <input type="email" id="wmsuEmail" name="wmsuEmail" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="course">Course</label>
        <select id="course" name="course" required>
            <option value="Computer Science">Computer Science</option>
            <option value="Information Technology">Information Technology</option>
            <option value="Associate in Computer Technology">Associate in Computer Technology</option>
            <option value="Application Development">Application Development</option>
        </select>

        <label for="year">Year</label>
        <select id="year" name="year" required>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
            <option value="5">Over 4 years</option>
        </select>

        <label for="section">Section</label>
        <input type="text" id="section" name="section" required>

        <button type="submit">Signup</button>
    </form>
</body>
</html>
