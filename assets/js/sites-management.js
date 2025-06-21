/**
 * WeCoza Site Management JavaScript
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize the plugin
     */
    function init() {
        initDeleteConfirmation();
        initViewSiteModal();
        initFormValidation();
        initBulkOperations();
        initTooltips();
    }



    /**
     * Initialize delete confirmation
     */
    function initDeleteConfirmation() {
        $(document).off('click', '.btn-delete-site').on('click', '.btn-delete-site', function() {
            const siteId = $(this).data('site-id');
            const siteName = $(this).data('site-name');

            if (confirm(`${wecoza_site_management_ajax.messages.confirm_delete}\n\nSite: ${siteName}`)) {
                deleteSite(siteId, $(this));
            }
        });
    }

    /**
     * Delete a site via AJAX
     */
    function deleteSite(siteId, button) {
        const originalHTML = button.html();
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        const data = {
            action: 'wecoza_delete_site',
            nonce: wecoza_site_management_ajax.nonce,
            site_id: siteId
        };

        $.post(wecoza_site_management_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                    });
                    showSuccess(wecoza_site_management_ajax.messages.success_deleted);
                } else {
                    showError(response.data || wecoza_site_management_ajax.messages.error_occurred);
                    button.prop('disabled', false).html(originalHTML);
                }
            })
            .fail(function() {
                showError(wecoza_site_management_ajax.messages.error_occurred);
                button.prop('disabled', false).html(originalHTML);
            });
    }

    /**
     * Initialize view site modal
     */
    function initViewSiteModal() {
        $(document).off('click', '.btn-view-site').on('click', '.btn-view-site', function() {
            const siteId = $(this).data('site-id');
            loadSiteDetails(siteId);
        });
    }

    /**
     * Load site details in modal
     */
    function loadSiteDetails(siteId) {
        const modal = $('#siteDetailsModal');
        const modalBody = $('#siteDetailsModalBody');

        if (!modal.length) return;

        modalBody.html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
        modal.modal('show');

        const data = {
            action: 'wecoza_get_site_details',
            nonce: wecoza_site_management_ajax.nonce,
            site_id: siteId
        };

        $.post(wecoza_site_management_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    modalBody.html(formatSiteDetailsHTML(response.data));
                } else {
                    modalBody.html(`<div class="alert alert-subtle-danger">${response.data || 'Failed to load site details.'}</div>`);
                }
            })
            .fail(function() {
                modalBody.html('<div class="alert alert-subtle-danger">Failed to load site details.</div>');
            });
    }

    /**
     * Format site details HTML for modal
     */
    function formatSiteDetailsHTML(data) {
        const site = data.site;
        const client = data.client;

        return `
            <div class="row">
                <div class="col-md-6">
                    <h6>Site Information</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Name:</dt>
                        <dd class="col-sm-8">${escapeHtml(site.site_name)}</dd>
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">${site.site_id}</dd>
                        <dt class="col-sm-4">Client:</dt>
                        <dd class="col-sm-8">${client ? escapeHtml(client.client_name) : 'Unknown'}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h6>Address</h6>
                    <p>${site.address ? escapeHtml(site.address) : '<em>No address provided</em>'}</p>
                    <h6>Timestamps</h6>
                    <small class="text-muted">
                        Created: ${site.created_at ? formatDate(site.created_at) : 'Unknown'}<br>
                        Updated: ${site.updated_at ? formatDate(site.updated_at) : 'Never'}
                    </small>
                </div>
            </div>
        `;
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        const form = $('#site-form');
        if (!form.length) return;

        // Real-time validation
        form.find('input, select, textarea').on('blur', function() {
            validateField($(this));
        });

        // Form submission
        form.on('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    /**
     * Validate individual field
     */
    function validateField(field) {
        const value = field.val().trim();
        const fieldName = field.attr('name');
        let isValid = true;
        let errorMessage = '';

        // Remove existing validation classes
        field.removeClass('is-valid is-invalid');
        field.siblings('.invalid-feedback').remove();

        // Validate based on field
        switch (fieldName) {
            case 'site_name':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Site name is required.';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Site name must be at least 2 characters.';
                } else if (value.length > 100) {
                    isValid = false;
                    errorMessage = 'Site name must not exceed 100 characters.';
                }
                break;

            case 'client_id':
                if (!value || value === '0') {
                    isValid = false;
                    errorMessage = 'Please select a client.';
                }
                break;

            case 'address':
                if (value.length > 1000) {
                    isValid = false;
                    errorMessage = 'Address must not exceed 1000 characters.';
                }
                break;
        }

        // Apply validation result
        if (isValid) {
            field.addClass('is-valid');
        } else {
            field.addClass('is-invalid');
            field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
        }

        return isValid;
    }

    /**
     * Validate entire form
     */
    function validateForm(form) {
        let isValid = true;
        
        form.find('input[required], select[required], textarea[required]').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Initialize bulk operations
     */
    function initBulkOperations() {
        // Bulk select functionality could be added here
        // For now, this is a placeholder for future enhancement
    }

    /**
     * Initialize tooltips
     */
    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Utility functions
     */
    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'danger');
    }

    function showNotification(message, type) {
        const alertHTML = `
            <div class="alert alert-subtle-${type} alert-dismissible fade show" role="alert">
                ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Insert at top of container
        $('.wecoza-sites-list-container, .wecoza-site-form-container, .wecoza-site-details-container')
            .first().prepend(alertHTML);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }

    function truncateText(text, length) {
        if (!text) return '';
        return text.length > length ? text.substring(0, length) + '...' : text;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString();
    }

    function formatDateShort(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const options = { month: 'short', day: 'numeric', year: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }



    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);
