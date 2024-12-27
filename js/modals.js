document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed');
    
    // Add event listeners for buttons to show respective modals
    document.querySelectorAll('.view-members-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            console.log(`View Members clicked for OrgID: ${orgId}, OrgName: ${orgName}`);
            document.getElementById('org-name-header').innerText = orgName;
            // Load members for the organization
            loadMembers(orgId);
            document.getElementById('view-members-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.view-payments-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            console.log(`View Payments clicked for OrgID: ${orgId}, OrgName: ${orgName}`);
            document.getElementById('org-name-payments').innerText = orgName;
            // Load payments for the organization
            loadPayments(orgId);
            document.getElementById('view-payments-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.add-member-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            console.log(`Add Member clicked for OrgID: ${orgId}, OrgName: ${orgName}`);
            document.getElementById('org-name-display').innerText = orgName;
            document.getElementById('member-org-id').value = orgId;
            document.getElementById('add-member-modal').style.display = 'block';
        });
    });

    document.querySelectorAll('.add-payment-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orgId = this.getAttribute('data-id');
            const orgName = this.getAttribute('data-name');
            console.log(`Add Payment clicked for OrgID: ${orgId}, OrgName: ${orgName}`);
            document.getElementById('org-name-payment-display').innerText = orgName;
            document.getElementById('payment-org-id').value = orgId;
            document.getElementById('add-payment-modal').style.display = 'block';
        });
    });

    // Handle add payment form submission
    document.getElementById('add-payment-form').addEventListener('submit', function (event) {
        event.preventDefault();
        const orgId = document.getElementById('payment-org-id').value;
        const feeName = document.getElementById('fee-name').value;
        const amount = document.getElementById('amount').value;
        const dueDate = document.getElementById('due-date').value;
        const description = document.getElementById('description').value;

        console.log(`Submitting payment for OrgID: ${orgId}, FeeName: ${feeName}, Amount: ${amount}, DueDate: ${dueDate}, Description: ${description}`);

        fetch('add_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                org_id: orgId,
                fee_name: feeName,
                amount: amount,
                due_date: dueDate,
                description: description
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Add payment response:', data);
            if (data.status === 'success') {
                alert('Payment added successfully.');
                loadPayments(orgId); // Reload payments
                document.getElementById('add-payment-modal').style.display = 'none';
            } else {
                alert('Failed to add payment.');
            }
        })
        .catch(error => {
            console.error('Error adding payment:', error);
            alert('An error occurred while adding the payment.');
        });
    });

    // Close modals
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function () {
            console.log('Close button clicked');
            this.closest('.modal').style.display = 'none';
        });
    });

    // Handle delete organization button click
    document.querySelectorAll('.delete-org-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orgId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this organization? This will also delete all associated members and payments.')) {
                console.log(`Delete Organization clicked for OrgID: ${orgId}`);
                deleteOrganization(orgId);
            }
        });
    });
});

function loadMembers(orgId) {
    console.log(`Loading members for OrgID: ${orgId}`);
    fetch(`get_members.php?org_id=${orgId}`)
        .then(response => response.json())
        .then(members => {
            console.log('Members loaded:', members);
            const membersTableBody = document.getElementById('members-table-body');
            membersTableBody.innerHTML = ''; // Clear existing rows
            members.forEach((member, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${member.StudentID}</td>
                    <td>${member.first_name} ${member.last_name}</td>
                    <td>${member.WmsuEmail}</td>
                    <td>${member.Position}</td>
                    <td>
                        <button class="btn delete-member-btn" data-id="${member.StudentID}" data-org-id="${orgId}">
                            Delete
                        </button>
                    </td>
                `;
                membersTableBody.appendChild(row);
            });

            // Add event listeners for delete member buttons
            document.querySelectorAll('.delete-member-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const studentId = this.getAttribute('data-id');
                    const orgId = this.getAttribute('data-org-id');
                    console.log(`Delete Member button clicked for StudentID: ${studentId}, OrgID: ${orgId}`);
                    if (confirm('Are you sure you want to delete this member?')) {
                        console.log(`Confirmed deletion for StudentID: ${studentId}, OrgID: ${orgId}`);
                        deleteMember(studentId, orgId);
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error loading members:', error);
        });
}

function loadPayments(orgId) {
    console.log(`Loading payments for OrgID: ${orgId}`);
    fetch(`get_payments.php?org_id=${orgId}`)
        .then(response => response.json())
        .then(payments => {
            console.log('Payments loaded:', payments);
            const paymentsTableBody = document.getElementById('payments-table-body');
            paymentsTableBody.innerHTML = ''; // Clear existing rows
            payments.forEach((payment, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${payment.FeeName}</td>
                    <td>${payment.Amount}</td>
                    <td>${payment.DueDate}</td>
                    <td>${payment.Description}</td>
                    <td>
                        <button class="btn delete-payment-btn" data-id="${payment.FeeID}" data-org-id="${orgId}">
                            Delete
                        </button>
                    </td>
                `;
                paymentsTableBody.appendChild(row);
            });

            // Add event listeners for delete payment buttons
            document.querySelectorAll('.delete-payment-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const feeId = this.getAttribute('data-id');
                    const orgId = this.getAttribute('data-org-id');
                    console.log(`Delete Payment button clicked for FeeID: ${feeId}, OrgID: ${orgId}`);
                    if (confirm('Are you sure you want to delete this payment?')) {
                        console.log(`Confirmed deletion for FeeID: ${feeId}, OrgID: ${orgId}`);
                        deletePayment(feeId, orgId);
                    }
                });
            });
        })
        .catch(error => {
            console.error('Error loading payments:', error);
        });
}

function deleteMember(studentId, orgId) {
    console.log(`Deleting member with StudentID: ${studentId}, OrgID: ${orgId}`);
    fetch('delete_member.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ student_id: studentId, org_id: orgId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete member response:', data);
        if (data.status === 'success') {
            alert('Member deleted successfully.');
            loadMembers(orgId); // Reload members
        } else {
            alert('Failed to delete member.');
        }
    })
    .catch(error => {
        console.error('Error deleting member:', error);
        alert('An error occurred while deleting the member.');
    });
}

function deletePayment(feeId, orgId) {
    console.log(`Deleting payment with FeeID: ${feeId}, OrgID: ${orgId}`);
    fetch('delete_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ fee_id: feeId, org_id: orgId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete payment response:', data);
        if (data.status === 'success') {
            alert('Payment deleted successfully.');
            loadPayments(orgId); // Reload payments
        } else {
            alert('Failed to delete payment.');
        }
    })
    .catch(error => {
        console.error('Error deleting payment:', error);
        alert('An error occurred while deleting the payment.');
    });
}

function deleteOrganization(orgId) {
    console.log(`Deleting organization with OrgID: ${orgId}`);
    fetch('delete_organization.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ org_id: orgId })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Delete organization response:', data);
        if (data.status === 'success') {
            alert('Organization and all associated data deleted successfully.');
            location.reload();
        } else {
            alert('Failed to delete organization.');
        }
    })
    .catch(error => {
        console.error('Error deleting organization:', error);
        alert('An error occurred while deleting the organization.');
    });
}