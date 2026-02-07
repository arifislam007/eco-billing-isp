// Main JavaScript for ISP Billing System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                lengthMenu: 'Show _MENU_ entries',
                search: 'Search:',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            }
        });
    }
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Confirm delete
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-BD', {
            style: 'currency',
            currency: 'BDT'
        }).format(amount);
    }
    
    // Format date
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').alert('close');
    }, 5000);
});
