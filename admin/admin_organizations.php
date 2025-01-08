<?php
session_start();
require_once '../classes/organization.class.php';
require_once '../classes/academicperiod.class.php';
require_once '../classes/database.class.php';
require_once '../classes/staff.class.php';
require_once '../classes/Fee.class.php';

function logError($message) {
    error_log($message . "\n", 3, __DIR__ . '/debug.log');
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['StudentID'])) {
        throw new Exception("User not logged in");
    }

    // Get database instance
    $db = Database::getInstance()->connect();

    // Get current academic period
    $academicPeriod = new AcademicPeriod();
    $currentPeriod = $academicPeriod->getCurrentPeriod();

    if (!$currentPeriod) {
        throw new Exception("No active academic period found");
    }

    // Get user details
    $organization = new Organization();
    $user = $organization->getUserDetails($_SESSION['StudentID']);

    if (!$user) {
        throw new Exception("User not found");
    }

    $firstName = $user['first_name'];
    $lastName = $user['last_name'];

    // Get organizations for the current academic period
    $organizations = $organization->getOrganizationsByPeriod($currentPeriod['school_year'], $currentPeriod['semester']);

} catch (Exception $e) {
    logError($e->getMessage());
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_organization'])) {
            $organizationName = $_POST['org_name'];
            $orgID = $_POST['org_id'];

            // Get current academic period
            $currentPeriod = $academicPeriod->getCurrentPeriod();

            if (!$currentPeriod) {
                throw new Exception("No active academic period found");
            }

            // Add organization
            if ($organization->addOrganization($orgID, $organizationName, $currentPeriod['school_year'], $currentPeriod['semester'])) {
                echo "<script>
                    alert('Organization added successfully');
                    window.location.href = 'admin_organizations.php';
                </script>";
                exit();
            } else {
                throw new Exception("Failed to add organization");
            }
        } elseif (isset($_POST['add_member'])) {
            $studentID = $_POST['student_id'];
            $orgID = $_POST['org_id'];
            $position = $_POST['position'];

            // Log the OrganizationID and academic period
            logError("Attempting to add member to OrganizationID: " . $orgID);
            logError("Current academic period: " . $currentPeriod['school_year'] . " " . $currentPeriod['semester']);

            // Insert member into staff table
            $staff = new Staff();
            $result = $staff->addMember($studentID, $position, $orgID, $currentPeriod['school_year'], $currentPeriod['semester']);
            if ($result === true) {
                echo "<script>
                    alert('Member added successfully');
                    window.location.href = 'admin_organizations.php';
                </script>";
                exit();
            } else {
                throw new Exception("An error occurred while adding the member");
            }
        } elseif (isset($_POST['add_payment'])) {
            $feeID = $_POST['fee_id'];
            $feeName = $_POST['fee_name'];
            $amount = $_POST['amount'];
            $dueDate = $_POST['due_date'];
            $description = $_POST['description'];
            $orgID = $_POST['org_id'];

            // Log the OrganizationID and academic period
            logError("Attempting to add payment to OrganizationID: " . $orgID);
            logError("Current academic period: " . $currentPeriod['school_year'] . " " . $currentPeriod['semester']);

            // Insert payment into fees table
            $fee = new Fee();
            $result = $fee->addPayment($orgID, $feeID, $feeName, $amount, $dueDate, $description, $currentPeriod['school_year'], $currentPeriod['semester']);
            if ($result['status'] === 'success') {
                echo "<script>
                    alert('Payment added successfully');
                    window.location.href = 'admin_organizations.php';
                </script>";
                exit();
            } else {
                throw new Exception($result['message']);
            }
        }
    } catch (Exception $e) {
        logError("Error processing request: " . $e->getMessage());
        echo "<script>
            alert('An error occurred while processing the request: " . $e->getMessage() . "');
            window.location.href = 'admin_organizations.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Organizations - PayThon</title>
    <link rel="stylesheet" href="../css/organizations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <!-- Main Content -->
    <section class="home-section">
        <div class="home-content">
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="header-actions">
                        <button id="add-org-btn" class="btn">
                            <i class="fas fa-plus"></i> Add Organization
                        </button>
                        <div class="search-container">
                            <input type="text" id="searchInput" placeholder="Search organizations...">
                            <i class="fas fa-search search-icon"></i>
                        </div>
                    </div>
                </div>

                <table id="org-table">
                    <thead>
                        <tr>
                            <th>Organization ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $org): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($org['OrganizationID']); ?></td>
                            <td><?php echo htmlspecialchars($org['OrgName']); ?></td>
                            <td class="actions">
                                <button class="btn view-members-btn" data-id="<?php echo htmlspecialchars($org['OrganizationID']); ?>" data-name="<?php echo htmlspecialchars($org['OrgName']); ?>">
                                    <i class="fas fa-users"></i> View Members
                                </button>
                                <button class="btn view-payments-btn" data-id="<?php echo htmlspecialchars($org['OrganizationID']); ?>" data-name="<?php echo htmlspecialchars($org['OrgName']); ?>">
                                    <i class="fas fa-money-bill"></i> View Payments
                                </button>
                                <button class="btn add-member-btn" data-id="<?php echo htmlspecialchars($org['OrganizationID']); ?>" data-name="<?php echo htmlspecialchars($org['OrgName']); ?>">
                                    <i class="fas fa-user-plus"></i> Add Member
                                </button>
                                <button class="btn add-payment-btn" data-id="<?php echo htmlspecialchars($org['OrganizationID']); ?>" data-name="<?php echo htmlspecialchars($org['OrgName']); ?>">
                                    <i class="fas fa-plus-circle"></i> Add Payment
                                </button>
                                <button class="btn btn-danger delete-org-btn" data-id="<?php echo htmlspecialchars($org['OrganizationID']); ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modals -->
            <div id="org-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3 id="modal-title">Add Organization</h3>
                    <form id="add-org-form" method="post">
                        <input type="hidden" name="add_organization" value="1">
                        <div class="form-group">
                            <label for="org_name">Organization Name</label>
                            <input type="text" id="org_name" name="org_name" required>
                        </div>
                        <div class="form-group">
                            <label for="org_id">Organization ID</label>
                            <input type="text" id="org_id" name="org_id" required>
                        </div>
                        <button type="submit" class="btn">
                            <i class="fas fa-plus"></i> Add Organization
                        </button>
                    </form>
                </div>
            </div>

            <div id="payments-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Organization Payments</h3>
                    <div id="payments-list"></div>
                </div>
            </div>

            <!-- Add Member Modal -->
            <div id="add-member-modal" class="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Add Member to <span id="org-name-display"></span></h3>
                            <span class="close">&times;</span>
                        </div>
                        <div class="modal-body">
                            <form id="add-member-form" method="post">
                                <input type="hidden" name="add_member" value="1">
                                <input type="hidden" name="org_id" id="member-org-id">
                                <div class="form-group">
                                    <label for="student_id">Student ID</label>
                                    <input type="text" id="student_id" name="student_id" required pattern="[0-9]+" minlength="5" placeholder="Enter student ID">
                                </div>
                                <div class="form-group">
                                    <label for="position">Position</label>
                                    <select id="position" name="position" required>
                                        <option value="">Select Position</option>
                                        <option value="President">President</option>
                                        <option value="Vice President">Vice President</option>
                                        <option value="Secretary">Secretary</option>
                                        <option value="Treasurer">Treasurer</option>
                                        <option value="Member">Member</option>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn">
                                        <i class="fas fa-plus"></i> Add Member
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Add Payment Modal -->
<div id="add-payment-modal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Payment for <span id="org-name-payment-display"></span></h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-payment-form">
                    <input type="hidden" id="payment-org-id" name="org_id">
                    <div class="form-group">
                        <label for="fee-name">Fee Name</label>
                        <input type="text" id="fee-name" name="fee_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" id="amount" name="amount" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="due-date">Due Date</label>
                        <input type="date" id="due-date" name="due_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>
        </div>
    </section>

   <!-- View Members Modal -->
<!-- View Members Modal -->
<div id="view-members-modal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Members of <span id="org-name-header"></span></h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="members-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="members-table-body">
                            <!-- Members will be loaded here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Payments Modal -->
<div id="view-payments-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Payments for <span id="org-name-payments"></span></h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <table class="payments-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fee Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payments-table-body">
                    <!-- Payments will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="../js/organizations.js"></script>
    <script src="../js/add_organization.js"></script>
    <script src="../js/modals.js"></script>
    <script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const table = document.getElementById('org-table');
        const rows = table.getElementsByTagName('tr');

        for (let i = 1; i < rows.length; i++) { // Start from 1 to skip the header row
            const cells = rows[i].getElementsByTagName('td');
            if (cells.length > 0) {
                const orgId = cells[0].textContent.toLowerCase();
                const orgName = cells[1].textContent.toLowerCase();
                if (orgId.includes(searchValue) || orgName.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    });
</script>
</body>
</html>