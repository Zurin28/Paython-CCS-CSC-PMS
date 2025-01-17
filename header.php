<header class="header">
    <div class="logo">
        <i class='bx bx-wallet'></i>
        PayThon
    </div>
    <nav class="nav">
        <a href="student_overview.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'student_overview.php') ? 'class="active"' : ''; ?>>Overview</a>
        <a href="student_payment.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'student_payment.php') ? 'class="active"' : ''; ?>>Payments</a>
    </nav>
    <div style="display: flex; align-items: center; gap: 20px;">
        <?php if (basename($_SERVER['PHP_SELF']) == 'student_payment.php'): ?>
        <div class="search-container">
            <i class='bx bx-search search-icon'></i>
            <input type="text" id="headerSearchInput" class="search-input" placeholder="Search payments...">
        </div>
        <?php endif; ?>
        <div class="user-icon-container">
            <div class="user-icon" onclick="toggleDropdown()">
                <i class='bx bx-user'></i>
            </div>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="../account/login.php" class="dropdown-item">Logout</a>
            </div>
        </div>
    </div>
</header>

<script>
    function toggleDropdown() {
        var dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.classList.toggle('show');
    }

    // Search functionality for the payment table
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('headerSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var filter = searchInput.value.toLowerCase();
                var rows = document.querySelectorAll('.payment-table tbody tr');

                rows.forEach(function(row) {
                    var organization = row.cells[0].textContent.toLowerCase();
                    var fee = row.cells[1].textContent.toLowerCase();
                    var status = row.cells[2].textContent.toLowerCase();
                    var amount = row.cells[3].textContent.toLowerCase();
                    var due = row.cells[4].textContent.toLowerCase();

                    if (organization.includes(filter) || fee.includes(filter) || status.includes(filter) || amount.includes(filter) || due.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>