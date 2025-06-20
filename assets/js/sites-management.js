/**
 * WeCoza Site Management JavaScript
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Global variables
    let isLoading = false;

    /**
     * Initialize the plugin
     */
    function init() {
        initSearchFunctionality();
        initDeleteConfirmation();
        initViewSiteModal();
        initFormValidation();
        initBulkOperations();
        initTooltips();
    }

    /**
     * Initialize search functionality
     */
    function initSearchFunctionality() {
        const searchForm = $('#sites-search-form');
        const searchInput = $('#sites_search');
        const refreshBtn = $('#refresh-sites');

        if (searchInput.length) {
            // Handle Enter key only - no auto-submit on input
            searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performSearch($(this).val().trim());
                }
            });
        }

        // Refresh button
        if (refreshBtn.length) {
            refreshBtn.on('click', function() {
                refreshSitesList();
            });
        }

        // Search form submission
        if (searchForm.length) {
            searchForm.on('submit', function(e) {
                e.preventDefault();
                performSearch(searchInput.val().trim());
            });
        }
    }

    /**
     * Perform AJAX search
     */
    function performSearch(searchValue, page = 1) {
        if (isLoading) return;

        isLoading = true;
        showLoading();

        const data = {
            action: 'wecoza_live_search_sites',
            nonce: wecoza_site_management_ajax.nonce,
            search: searchValue,
            page: page,
            per_page: 20
        };

        $.post(wecoza_site_management_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    updateSitesTable(response.data);
                    updatePagination(response.data);
                } else {
                    showError(response.data || wecoza_site_management_ajax.messages.error_occurred);
                }
            })
            .fail(function() {
                showError(wecoza_site_management_ajax.messages.error_occurred);
            })
            .always(function() {
                hideLoading();
                isLoading = false;
            });
    }

    /**
     * Update sites table with new data
     */
    function updateSitesTable(data) {
        const container = $('#sites-table-container');

        // Handle empty results without search - show alert only
        if (data.sites.length === 0 && !data.search) {
            container.html(getNoResultsHTML(data.search));
            return;
        }

        // Calculate statistics
        const totalSites = data.sites.length;
        const uniqueClients = [...new Set(data.sites.map(site => site.client_id))].length;

        let tableHTML = `
            <div class="card shadow-none border my-3" data-component-card="data-component-card">
                <div class="card-header p-3 border-bottom">
                    <div class="row g-3 justify-content-between align-items-center mb-3">
                        <div class="col-12 col-md">
                            <h4 class="text-body mb-0" data-anchor="data-anchor" id="sites-table-header">
                                Sites Management
                                <i class="bi bi-building ms-2"></i>
                            </h4>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshSites()">
                                    Refresh
                                    <i class="bi bi-arrow-clockwise ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Summary strip -->
                    <div class="col-12">
                        <div class="scrollbar">
                            <div class="row g-0 flex-nowrap">
                                <div class="col-auto border-end pe-4">
                                    <h6 class="text-body-tertiary">Total Sites : ${totalSites}</h6>
                                </div>
                                <div class="col-auto px-4 border-end">
                                    <h6 class="text-body-tertiary">Active Sites : ${totalSites}</h6>
                                </div>
                                <div class="col-auto px-4 border-end">
                                    <h6 class="text-body-tertiary">Unique Clients : ${uniqueClients}</h6>
                                </div>
                                ${data.search ? `<div class="col-auto px-4">
                                    <h6 class="text-body-tertiary">Search Results : ${totalSites > 0 ? `${totalSites} <div class="badge badge-phoenix fs-10 badge-phoenix-info">filtered</div>` : '<span class="badge badge-phoenix fs-10 badge-phoenix-danger">No Search Results</span>'}</h6>
                                </div>` : ''}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 py-2">
                    <div class="table-responsive">
                        <table id="sites-table" class="table table-hover table-sm fs-9 mb-0 overflow-hidden">
                            <thead class="border-bottom">
                                <tr>
                                    <th scope="col" class="border-0 ps-4">
                                        ID
                                        <i class="bi bi-hash ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Site Name
                                        <i class="bi bi-building ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Client
                                        <i class="bi bi-person-badge ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Address
                                        <i class="bi bi-geo-alt ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        Created
                                        <i class="bi bi-calendar-date ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0 pe-4">
                                        Actions
                                        <i class="bi bi-gear ms-1"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        // Handle empty search results
        if (data.sites.length === 0 && data.search) {
            tableHTML += `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1 mb-3 d-block"></i>
                            <h6 class="mb-2">No sites match your search</h6>
                            <p class="mb-0">No sites found for "${escapeHtml(data.search)}"</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            data.sites.forEach(function(site) {
                const client = data.clients[site.client_id] || null;
                const clientName = client ? client.client_name : 'Unknown Client';
                const address = site.address ? truncateText(site.address, 30) : 'No address';
                const createdDate = site.created_at ? formatDateShort(site.created_at) : '-';

                tableHTML += `
                    <tr data-site-id="${site.site_id}">
                        <td class="py-2 align-middle text-center fs-8 white-space-nowrap">
                            <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                #${site.site_id}
                            </span>
                        </td>
                        <td>
                            <span class="fw-medium">
                                ${escapeHtml(site.site_name)}
                            </span>
                        </td>
                        <td>
                            ${client ? `
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    ${escapeHtml(clientName)}
                                </span>
                                <br>
                                <small class="text-muted">ID: ${site.client_id}</small>
                            ` : `
                                <span class="badge fs-10 badge-phoenix badge-phoenix-warning">
                                    Unknown Client
                                    <i class="bi bi-exclamation-triangle ms-1"></i>
                                </span>
                            `}
                        </td>
                        <td>
                            <span class="site-address text-nowrap" title="${escapeHtml(site.address || '')}">
                                ${escapeHtml(address)}
                            </span>
                        </td>
                        <td>
                            <span class="text-nowrap">${createdDate}</span>
                        </td>
                        <td class="pe-4">
                            <div class="dropdown">
                                <button class="btn btn-link text-body btn-sm dropdown-toggle"
                                        style="text-decoration: none;"
                                        type="button"
                                        id="dropdownMenuButton${site.site_id}"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${site.site_id}">
                                    <li>
                                        <button type="button" class="dropdown-item btn-view-site"
                                                data-site-id="${site.site_id}">
                                            View Details
                                            <i class="bi bi-eye ms-2"></i>
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="?action=edit&site_id=${site.site_id}">
                                            Edit Site
                                            <i class="bi bi-pencil ms-2"></i>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button type="button" class="dropdown-item text-danger btn-delete-site"
                                                data-site-id="${site.site_id}"
                                                data-site-name="${escapeHtml(site.site_name)}">
                                            Delete Site
                                            <i class="bi bi-trash ms-2"></i>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        tableHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        container.html(tableHTML);

        // Reinitialize event handlers for new elements
        initDeleteConfirmation();
        initViewSiteModal();
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
                    modalBody.html(`<div class="alert alert-danger">${response.data || 'Failed to load site details.'}</div>`);
                }
            })
            .fail(function() {
                modalBody.html('<div class="alert alert-danger">Failed to load site details.</div>');
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
    function showLoading() {
        $('#sites-loading').show();
        $('#sites-table-container').hide();
    }

    function hideLoading() {
        $('#sites-loading').hide();
        $('#sites-table-container').show();
    }

    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'danger');
    }

    function showNotification(message, type) {
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
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

    function refreshSitesList() {
        window.location.reload();
    }

    function getNoResultsHTML(search) {
        if (search) {
            return `
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-1">No Sites Found</h6>
                        <p class="mb-0">No sites match your search for "${escapeHtml(search)}".</p>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="alert alert-info d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                    <div>
                        <h6 class="alert-heading mb-1">No Sites Found</h6>
                        <p class="mb-0">There are currently no sites in the database. Create a new site to get started.</p>
                    </div>
                </div>
            `;
        }
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

    function updatePagination(data) {
        // Pagination update logic would go here
        // For now, this is a placeholder
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);
