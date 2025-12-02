/**
 * Common script to reset modals and clear URL parameters
 * Use this for all add/edit modals to prevent data persistence issues
 */

(function() {
    'use strict';
    
    /**
     * Clear URL parameters (action, id) without page reload
     */
    function clearUrlParams() {
        const url = new URL(window.location);
        url.searchParams.delete('action');
        url.searchParams.delete('id');
        window.history.replaceState({}, '', url);
    }
    
    /**
     * Reset form and clear edit mode
     */
    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            // Clear any hidden edit ID fields
            const editIdInputs = form.querySelectorAll('input[type="hidden"][name*="_id"]');
            editIdInputs.forEach(input => {
                if (input.name !== 'page' && input.name !== 'panel') {
                    input.value = '';
                }
            });
        }
        clearUrlParams();
    }
    
    /**
     * Initialize modal reset handlers for all modals
     */
    function initGlobalModalReset() {
        // Handle all modals with forms
        document.querySelectorAll('.modal').forEach(modal => {
            const form = modal.querySelector('form');
            if (!form) return;
            
            const modalId = modal.id;
            
            // Reset when modal is closed
            modal.addEventListener('hidden.bs.modal', function() {
                const isEditMode = window.location.search.includes('action=edit');
                if (isEditMode) {
                    clearUrlParams();
                }
                // Always reset form when modal closes
                form.reset();
                // Clear hidden ID fields
                form.querySelectorAll('input[type="hidden"][name*="_id"]').forEach(input => {
                    if (input.name !== 'page' && input.name !== 'panel') {
                        input.value = '';
                    }
                });
            });
            
            // Reset when "Add" button is clicked (if not in edit mode)
            document.querySelectorAll(`[data-bs-target="#${modalId}"], [data-bs-toggle="modal"][href="#${modalId}"]`).forEach(button => {
                button.addEventListener('click', function() {
                    const isEditMode = window.location.search.includes('action=edit');
                    if (isEditMode) {
                        clearUrlParams();
                    }
                    // Small delay to ensure modal is ready
                    setTimeout(function() {
                        if (!window.location.search.includes('action=edit')) {
                            form.reset();
                            form.querySelectorAll('input[type="hidden"][name*="_id"]').forEach(input => {
                                if (input.name !== 'page' && input.name !== 'panel') {
                                    input.value = '';
                                }
                            });
                        }
                    }, 100);
                });
            });
            
            // Reset when modal opens if not in edit mode
            modal.addEventListener('show.bs.modal', function() {
                const isEditMode = window.location.search.includes('action=edit');
                if (!isEditMode) {
                    form.reset();
                    form.querySelectorAll('input[type="hidden"][name*="_id"]').forEach(input => {
                        if (input.name !== 'page' && input.name !== 'panel') {
                            input.value = '';
                        }
                    });
                }
            });
        });
    }
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalModalReset);
    } else {
        initGlobalModalReset();
    }
    
    // Export functions for manual use
    window.resetForm = resetForm;
    window.clearUrlParams = clearUrlParams;
})();

