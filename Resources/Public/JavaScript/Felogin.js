(function() {
    function showModals() {
        var loginModal = document.getElementById('loginModal');
        if (loginModal) {
            var modal = bootstrap.Modal.getOrCreateInstance(loginModal);
            modal.show();
        }

        var recoveryModal = document.getElementById('recoveryModal');
        if (recoveryModal) {
            var modal = bootstrap.Modal.getOrCreateInstance(recoveryModal);
            modal.show();
        }
    }

    function init() {
        // Wait for Bootstrap to be available
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            showModals();
        } else {
            // Retry after a short delay if Bootstrap is not yet loaded
            setTimeout(init, 50);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
