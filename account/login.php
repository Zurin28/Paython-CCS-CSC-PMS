<?php 
require_once '../classes/account.class.php';
require_once '../tools/functions.php';
require_once '../classes/academicperiod.class.php';
require_once '../classes/staff.class.php';

session_start();

$accObj = new Account();
$academicPeriod = new AcademicPeriod();
$currentPeriod = $academicPeriod->getCurrentPeriod();

if (!$currentPeriod) {
    echo "<p style='color: red;'>No active academic period set. Please contact the administrator.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = clean_input($_POST["email"]);
    $password = clean_input($_POST['password']);

    // Check login with specific error messages
    $loginResult = $accObj->login($email, $password, $currentPeriod['school_year'], $currentPeriod['semester']);

    switch($loginResult) {
        case "success":
            $account = $accObj->fetch($email);
            $_SESSION['account'] = $account;
            $_SESSION['StudentID'] = $_SESSION['account']['ID'];

            // Check if the student is also a staff member
            $staffObj = new Staff();
            $staff = $staffObj->getById($_SESSION['StudentID']);
            if ($staff) {
                $_SESSION['staffID'] = $staff['staffID'];
                echo $_SESSION['staffID'];
            }

            // Redirect based on role
            switch($account['Role']) {
                case 'student':
                    $_SESSION['Name'] = $_SESSION['account']['first_name'];
                    header("Location: ../student/student_overview.php");
                    break;
                case 'staff':
                    $_SESSION['Name'] = $_SESSION['account']['first_name'];
                    header("Location: ../staff/student.staff.php");
                    break;
                case 'admin':
                    $_SESSION['Name'] = $_SESSION['account']['first_name'];
                    header("Location: ../admin/admin_dashboard.php");
                    break;
                default:
                    echo '<p class="errorMsg">*Invalid account role</p>';
            }
            exit;
            

        case "email_not_found":
            echo '<p class="errorMsg">*Email address not found</p>';
            break;

        case "wrong_password":
            echo '<p class="errorMsg">*Incorrect password</p>';
            break;

        case "not_in_period":
            echo '<p class="errorMsg">*Your account is not registered for the current academic period</p>';
            break;

        default:
            echo '<p class="errorMsg">*An error occurred. Please try again later.</p>';
            break;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
</head>
<body>
    <section class="login-container">
        <div class="login">
            <img src="../img/logoccs.png" alt="Logo">
            <h2>Welcome to PayThon</h2>
            <p>This is a Payment Management System for WMSU's College of Computing Studies, College Student Council</p>
            <form action="" method="post">
                <label for="email">Enter your WMSU email:</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" placeholder="WMSU Email" required>

                <label for="password">Password:</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Password" required>


                <button type="loginbtn">Log In</button>
            </form>
            <p class="signup-link">Don't have an account? <a href="signup.php">Create Account</a></p>
        </div>
    </section>
</body>
</html>
