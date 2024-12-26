document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-org-btn').addEventListener('click', function() {
        document.getElementById('org-modal').style.display = 'block';
    });

    // Close modals when the close button is clicked
    document.querySelectorAll('.modal .close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Close modals when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
});