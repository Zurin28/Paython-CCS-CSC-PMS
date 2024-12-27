<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/admin_logs.css" rel="stylesheet">
    <style>
        
 /* .container {
    background-color: #dc3545;
    color: #333;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    max-width: 100%;
}
.nav-tabs {
    border: none;
    margin-bottom: 30px;
}
.nav-tabs .nav-link {
    border: none;
    color: #666;
    padding: 15px 25px;
    font-size: 16px;
   position: relative;
    background: transparent;
}
.nav-tabs .nav-link.active {
    color: #1a7431;
    background: transparent;
    font-weight: 500;
}
.nav-tabs .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: #1a7431;
}
.tab-count {
    background: #e8e8e8;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 5px;
    color: #666;
}
.nav-link.active .tab-count {
    background: #1a7431;
    color: white;
}
.admin-logs-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.admin-logs-table thead {
    background-color: #fcfeff;
}
.admin-logs-table th {
    padding-right: 50px;
     text-align: left; 
    border-bottom: 2px solid #dee2e6;
    color: #333;
    font-weight: 600;
}
.admin-logs-table td {
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}
 .admin-logs-table tbody tr:hover {
    background-color: #f1f3f5;
    transition: background-color 0.3s ease;
} 
.status-badge {
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    display: inline-block;
}
.status-approved {
    background: #e6f4ea;
    color: #1a7431;
}
.status-rejected {
    background: #fce8e6;
    color: #d93025;
}
.action-buttons {
    display: flex;
    gap: 4px;
}
.action-buttons button {
    padding: 8px;
    border-radius: 4px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    border: 1px solid #ddd;
    background: white;
    color: #666;
}
.home-section {
    padding: 20px;
}
.content-wrapper {
    background: rgb(243, 243, 243);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 50px;
}
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.actions {
    display: flex;
    gap: 10px;
}
.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}
.btn i {
    margin-right: 5px;
}
.btn-danger {
    background-color: #dc3545;
    color: white;
}
.btn-danger:hover {
    background-color: #c82333;
}
.btn-success {
    background-color: #1a7431;
    color: white;
}
.btn-success:hover {
    background-color: #155c25;
}
.search-filter {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 15px;
}

.search-filter form {
    display: flex;
    align-items: center;
}

.search-bar {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px 0 0 4px;
    width: 200px;
    font-size: 12px;
    transition: border-color 0.3s ease;
}

.search-bar:focus {
    outline: none;
    border-color: #1a7431;
}

.search-button {
    padding: 8px 15px;
    background-color: #1a7431;
    color: white;
    border: 1px solid #1a7431;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.3s ease;
}

.search-button:hover {
    background-color: #155c25;
}

.btn-custom-green {
    background-color: #1a7431;
    color: white;
    border: 1px solid #1a7431;
}

.btn-custom-green:hover {
    background-color: #155c25;
    color: white;
}

.btn-custom-red {
    background-color: #dc3545;
    color: white;
    border: 1px solid #dc3545;
}

.btn-custom-red:hover {
    background-color: #c82333;
    color: white;
} 

    </style> */
</head>
<body>
    <?php include '../navbar.php'; ?>

    <section class="home-section">
        <div class="home-content">
            <div class="content-wrapper">
                <div class="search-filter">
                    <form method="POST">
                        <input 
                            type="text" 
                            name="searchQuery" 
                            id="searchBar" 
                            placeholder="Search..." 
                            class="search-bar"
                            value="<?= isset($_POST['searchQuery']) ? htmlspecialchars($_POST['searchQuery']) : '' ?>"
                        >
                        <button type="submit" class="search-button">Search</button>
                    </form>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#login_history">
                            Staff Login History <span class="tab-count">2</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#fee_requests">
                            Fee Requests <span class="tab-count">2</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#staff_activity">
                            Staff Activity <span class="tab-count">2</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Staff Login History Tab -->
                    <div class="tab-pane fade show active" id="login_history">
                        <table class="admin-logs-table">
                            <thead>
                                <tr>
                                    <th>StudentID</th>
                                    <th>Staff Name</th>
                                    <th>Organization</th>
                                    <th>Login Date & Time</th>
                                    <th>Logout Date & Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2202301283</td>
                                    <td>Joel Josh Que</td>
                                    <td>venom</td>
                                    <td>2024-01-11 08:30:00</td>
                                    <td>2024-01-11 17:15:00</td>
                                    <td class="actions">
                                        <button class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                <td>2202301283</td>
                                    <td>Joel Que</td>
                                    <td>CSC</td>
                                    <td>2024-01-11 08:30:00</td>
                                    <td>2024-01-11 17:15:00</td>
                                    <td class="actions">
                                        <button class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Fee Requests Tab -->
                    <div class="tab-pane fade" id="fee_requests">
                        <table class="admin-logs-table">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Fee ID</th>
                                    <th>Fee Name</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Description</th>
                                    <th>Created By</th>
                                    <th>Submit Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Gender</td>
                                    <td>Gender123</td>
                                    <td>Gender Fee</td>
                                    <td>₱500.00</td>
                                    <td>2024-12-15</td>
                                    <td>Yearly membership fee</td>
                                    <td>Ethan Wayne</td>
                                    <td>2024-011-10</td>
                                    <td class="actions">
                                        <button class="btn btn-custom-green">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                        <button class="btn btn-custom-red">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                <td>Gender</td>
                                    <td>Gender123</td>
                                    <td>Gender Fee</td>
                                    <td>₱500.00</td>
                                    <td>2024-12-15</td>
                                    <td>Yearly membership fee</td>
                                    <td>Ethan Wayne</td>
                                    <td>2024-011-10</td>
                                    <td class="actions">
                                        <button class="btn btn-custom-green">
                                            <i class="fas fa-check"></i> Accept
                                        </button>
                                        <button class="btn btn-custom-red">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Staff Activity Tab -->
                    <div class="tab-pane fade" id="staff_activity">
                        <table class="admin-logs-table">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Fee ID</th>
                                    <th>Fee Name</th>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Staff Name</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                    <th>Action Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>CSC</td>
                                    <td>CSC123</td>
                                    <td>CSC Fee</td>
                                    <td>202301283</td>
                                    <td>Joel Josh Que</td>
                                    <td>Ethan Wayne</td>
                                    <td>
                                        <span class="status-badge status-approved">Accepted</span>
                                    </td>
                                    <td>-</td>
                                    <td>2024-01-10 14:30:00</td>
                                    <td class="actions">
                                        <button class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>CSC</td>
                                    <td>CSC123</td>
                                    <td>CSC Fee</td>
                                    <td>202301283</td>
                                    <td>Joel Josh Que</td>
                                    <td>Ethan Wayne</td>
                                    <td>
                                        <span class="status-badge status-rejected">Rejected</span>
                                    </td>
                                    <td>Amount exceeds maximum limit</td>
                                    <td>2024-01-10 15:45:00</td>
                                    <td class="actions">
                                        <button class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>