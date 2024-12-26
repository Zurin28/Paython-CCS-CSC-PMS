document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for buttons to show respective modals
    document.querySelectorAll('.view-members-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            document.getElementById('org-name-header').innerText = orgName;
            // Load members for the organization
            loadMembers(orgId);
            document.getElementById('view-members-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.view-payments-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            document.getElementById('org-name-payments').innerText = orgName;
            // Load payments for the organization
            loadPayments(orgId);
            document.getElementById('view-payments-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.add-member-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            document.getElementById('org-name-display').innerText = orgName;
            document.getElementById('member-org-id').value = orgId;
            document.getElementById('add-member-modal').style.display = 'block';
        });
    });

    // Close modals
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Handle delete organization button click
    document.querySelectorAll('.delete-org-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orgId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this organization? This will also delete all associated members and payments.')) {
                deleteOrganization(orgId);
            }
        });
    });
});

function loadMembers(orgId) {
    fetch(`get_members.php?org_id=${orgId}`)
        .then(response => response.json())
        .then(members => {
            const membersTableBody = document.getElementById('members-table-body');
            membersTableBody.innerHTML = ''; // Clear existing rows
            members.forEach((member, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${index + 1}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${member.StudentID}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${member.first_name} ${member.last_name}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${member.WmsuEmail}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${member.Position}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">
                        <button class="btn delete-member-btn" data-id="${member.StudentID}" data-org-id="${orgId}" style="background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                            Delete
                        </button>
                    </td>
                `;
                membersTableBody.appendChild(row);
            });
        });
}

function loadPayments(orgId) {
    fetch(`get_payments.php?org_id=${orgId}`)
        .then(response => response.json())
        .then(payments => {
            const paymentsTableBody = document.getElementById('payments-table-body');
            paymentsTableBody.innerHTML = ''; // Clear existing rows
            payments.forEach((payment, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${index + 1}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${payment.FeeName}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${payment.Amount}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${payment.DueDate}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">${payment.Description}</td>
                    <td style="border: 1px solid black; padding: 8px; text-align: left;">
                        <button class="btn delete-payment-btn" data-id="${payment.FeeID}" data-org-id="${orgId}" style="background-color: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                            Delete
                        </button>
                    </td>
                `;
                paymentsTableBody.appendChild(row);
            });
        });
}

function deleteOrganization(orgId) {
    fetch('delete_organization.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ org_id: orgId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Organization and all associated data deleted successfully.');
            location.reload();
        } else {
            alert('Failed to delete organization.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the organization.');
    });
}