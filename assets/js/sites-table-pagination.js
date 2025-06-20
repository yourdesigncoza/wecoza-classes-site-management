/**
 * Sites Table AJAX Pagination JavaScript for WeCoza Site Management Plugin
 *
 * Implements AJAX-based pagination functionality for the sites display table.
 * Includes search functionality with debouncing for performance.
 * 
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Pagination configuration
     */
    const PAGINATION_CONFIG = {
        debounceDelay: 300,        // Milliseconds to wait before executing search
        minSearchLength: 0,        // Minimum characters to trigger search (0 = search on empty)
        searchInputSelector: '#sites_search',
        tableSelector: '#sites-table',
        tableBodySelector: '#sites-table tbody',
        itemsPerPage: 20,          // Number of items to display per page
        ajaxAction: 'wecoza_search_sites'
    };

    /**
     * Pagination state
     */
    let searchTimeout = null;
    let $searchInput = null;
    let $table = null;
    let $tableBody = null;
    let $paginationContainer = null;
    let currentPage = 1;
    let totalPages = 1;
    let totalSites = 0;
    let currentSearch = '';
    let isLoading = false;

    /**
     * Initialization state
     */
    let isInitialized = false;

    /**
     * Initialize the pagination functionality
     */
    function sites_init_ajax_pagination() {
        // Prevent duplicate initialization
        if (isInitialized) {
            console.log('WeCoza Sites: Already initialized, skipping duplicate initialization');
            return;
        }

        // Find elements
        $searchInput = $(PAGINATION_CONFIG.searchInputSelector);
        $table = $(PAGINATION_CONFIG.tableSelector);
        $tableBody = $(PAGINATION_CONFIG.tableBodySelector);
        $paginationContainer = $('#sites-pagination');

        // Validate elements exist
        if ($searchInput.length === 0) {
            console.warn('WeCoza Sites: Search input not found');
            return;
        }

        if ($table.length === 0) {
            console.warn('WeCoza Sites: Table not found');
            return;
        }

        if ($paginationContainer.length === 0) {
            console.warn('WeCoza Sites: Pagination container not found');
            return;
        }

        // Bind search event with debouncing
        $searchInput.on('input keyup paste', function() {
            const searchTerm = $(this).val();
            sites_debounced_search(searchTerm);
        });

        // Handle search form submission
        $('#sites-search-form').on('submit', function(e) {
            e.preventDefault();
            const searchTerm = $searchInput.val();
            sites_perform_search(searchTerm, 1);
        });

        // Initialize with current search term and load first page
        currentSearch = $searchInput.val() || '';
        sites_load_page(1);

        // Mark as initialized
        isInitialized = true;

        console.log('WeCoza Sites: AJAX pagination initialized successfully');
    }

    /**
     * Debounced search function to improve performance
     * 
     * @param {string} searchTerm - The search term to filter by
     */
    function sites_debounced_search(searchTerm) {
        // Clear existing timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Set new timeout
        searchTimeout = setTimeout(function() {
            sites_perform_search(searchTerm, 1);
        }, PAGINATION_CONFIG.debounceDelay);
    }

    /**
     * Perform search and load results
     *
     * @param {string} searchTerm - The search term to filter by
     * @param {number} page - Page number to load
     */
    function sites_perform_search(searchTerm, page = 1) {
        currentSearch = searchTerm;
        currentPage = page;
        sites_load_page(page);
    }

    /**
     * Load a specific page via AJAX
     *
     * @param {number} page - Page number to load
     */
    function sites_load_page(page) {
        if (isLoading) return;

        // Validate AJAX object exists
        if (typeof wecoza_site_management_ajax === 'undefined') {
            console.error('WeCoza Sites: AJAX object not found');
            return;
        }

        isLoading = true;
        currentPage = page;

        // Show loading state
        sites_show_loading();

        const data = {
            action: PAGINATION_CONFIG.ajaxAction,
            nonce: wecoza_site_management_ajax.nonce,
            search: currentSearch,
            page: page,
            per_page: PAGINATION_CONFIG.itemsPerPage
        };

        $.post(wecoza_site_management_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    sites_update_table(response.data);
                    sites_update_pagination(response.data);
                    sites_update_summary(response.data);
                } else {
                    sites_show_error(response.data || 'An error occurred while loading sites.');
                }
            })
            .fail(function() {
                sites_show_error('Failed to load sites. Please try again.');
            })
            .always(function() {
                sites_hide_loading();
                isLoading = false;
            });
    }

    /**
     * Update the sites table with new data
     *
     * @param {object} data - Response data from AJAX call
     */
    function sites_update_table(data) {
        if (!$tableBody || $tableBody.length === 0) return;

        const sites = data.sites || [];
        const clients = data.clients || {};

        if (sites.length === 0) {
            // Show empty state
            const emptyMessage = currentSearch ? 
                `No sites found matching "${currentSearch}"` : 
                'No sites found';
            
            $tableBody.html(`
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1 mb-3 d-block"></i>
                            <h6 class="mb-2">${emptyMessage}</h6>
                            ${currentSearch ? `<p class="mb-0">Try adjusting your search criteria</p>` : ''}
                        </div>
                    </td>
                </tr>
            `);
        } else {
            // Build table rows
            let tableHTML = '';
            sites.forEach(function(site) {
                const client = clients[site.client_id] || null;
                tableHTML += sites_build_table_row(site, client);
            });
            $tableBody.html(tableHTML);
        }
    }

    /**
     * Build a table row for a site
     *
     * @param {object} site - Site data
     * @param {object} client - Client data
     * @returns {string} HTML for table row
     */
    function sites_build_table_row(site, client) {
        const clientDisplay = client ? 
            `<span class="badge bg-primary bg-opacity-10 text-primary">${client.client_name}</span><br><small class="text-muted">ID: ${site.client_id}</small>` :
            `<span class="badge fs-10 badge-phoenix badge-phoenix-warning">Unknown Client <i class="bi bi-exclamation-triangle ms-1"></i></span>`;

        const addressDisplay = site.address ? 
            `<span class="site-address text-nowrap" title="${site.address}">${site.address.length > 30 ? site.address.substring(0, 30) + '...' : site.address}</span>` :
            `<span class="text-muted">No address</span>`;

        const createdDisplay = site.created_at ? 
            `<span class="text-nowrap" title="${site.created_at}">${new Date(site.created_at).toLocaleDateString()}</span>` :
            `<span class="text-muted">-</span>`;

        return `
            <tr data-site-id="${site.site_id}">
                <td class="py-2 align-middle text-center fs-8 white-space-nowrap">
                    <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">#${site.site_id}</span>
                </td>
                <td>
                    <span class="fw-medium">${site.site_name}</span>
                </td>
                <td>${clientDisplay}</td>
                <td>${addressDisplay}</td>
                <td>${createdDisplay}</td>
                <td class="pe-4">
                    <div class="dropdown">
                        <button class="btn btn-link text-body btn-sm dropdown-toggle" style="text-decoration: none;" type="button" id="dropdownMenuButton${site.site_id}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${site.site_id}">
                            <li><button type="button" class="dropdown-item btn-view-site" data-site-id="${site.site_id}">View Details <i class="bi bi-eye ms-2"></i></button></li>
                            <li><a class="dropdown-item" href="?action=edit&site_id=${site.site_id}">Edit Site <i class="bi bi-pencil ms-2"></i></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button type="button" class="dropdown-item text-danger btn-delete-site" data-site-id="${site.site_id}" data-site-name="${site.site_name}">Delete Site <i class="bi bi-trash ms-2"></i></button></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Update pagination controls
     *
     * @param {object} data - Response data from AJAX call
     */
    function sites_update_pagination(data) {
        if (!$paginationContainer) return;

        totalSites = data.total || 0;
        totalPages = data.total_pages || 1;
        currentPage = data.page || 1;

        // Show pagination container if there are results
        if (totalSites > 0) {
            $paginationContainer.show();
            
            // Calculate display range
            const startItem = totalSites === 0 ? 0 : (currentPage - 1) * PAGINATION_CONFIG.itemsPerPage + 1;
            const endItem = Math.min(currentPage * PAGINATION_CONFIG.itemsPerPage, totalSites);

            // Build pagination HTML
            let paginationHTML = '';

            // Info display (left side)
            paginationHTML += `<span class="d-none d-sm-inline-block" data-list-info="data-list-info">
                ${startItem} to ${endItem} <span class="text-body-tertiary"> Items of </span>${totalSites}
            </span>`;

            // Navigation controls (right side)
            if (totalPages > 1) {
                paginationHTML += '<nav aria-label="Sites pagination">';
                paginationHTML += '<ul class="pagination pagination-sm">';

                // Previous button
                const prevDisabled = currentPage <= 1;
                if (prevDisabled) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link" aria-hidden="true">&laquo;</span></li>`;
                } else {
                    paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page-action="prev" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>`;
                }

                // Page numbers
                for (let i = 1; i <= totalPages; i++) {
                    const isActive = i === currentPage;
                    if (isActive) {
                        paginationHTML += `<li class="page-item active" aria-current="page"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page-number="${i}">${i}</a></li>`;
                    }
                }

                // Next button
                const nextDisabled = currentPage >= totalPages;
                if (nextDisabled) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link" aria-hidden="true">&raquo;</span></li>`;
                } else {
                    paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page-action="next" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>`;
                }

                paginationHTML += '</ul></nav>';
            }

            // Update container
            $paginationContainer.html(paginationHTML);

            // Bind click events
            sites_bind_pagination_events();
        } else {
            $paginationContainer.hide();
        }
    }

    /**
     * Bind pagination event handlers
     */
    function sites_bind_pagination_events() {
        if (!$paginationContainer) return;

        // Previous button
        $paginationContainer.find('[data-page-action="prev"]').off('click').on('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                sites_load_page(currentPage - 1);
            }
        });

        // Next button
        $paginationContainer.find('[data-page-action="next"]').off('click').on('click', function(e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                sites_load_page(currentPage + 1);
            }
        });

        // Page number buttons
        $paginationContainer.find('[data-page-number]').off('click').on('click', function(e) {
            e.preventDefault();
            const pageNumber = parseInt($(this).data('page-number'));
            if (pageNumber >= 1 && pageNumber <= totalPages) {
                sites_load_page(pageNumber);
            }
        });
    }

    /**
     * Update summary information
     *
     * @param {object} data - Response data from AJAX call
     */
    function sites_update_summary(data) {
        // Update total sites count
        const $totalSitesElement = $('.text-body-tertiary').filter(function() {
            return $(this).text().includes('Total Sites');
        });

        if ($totalSitesElement.length > 0) {
            $totalSitesElement.html(`Total Sites : ${data.total || 0}`);
        }

        // Update unique clients count using server-provided data
        const $uniqueClientsElement = $('#unique-clients-count');
        if ($uniqueClientsElement.length > 0) {
            $uniqueClientsElement.html(`Unique Clients : ${data.unique_clients || 0}`);
        }

        // Update search results indicator if search is active
        const $searchResultsContainer = $('.col-auto.px-4').filter(function() {
            return $(this).find('.text-body-tertiary').text().includes('Search Results');
        });

        if (currentSearch && currentSearch.trim().length > 0) {
            if ($searchResultsContainer.length === 0) {
                // Add search results indicator
                const $summaryRow = $('.row.g-0.flex-nowrap');
                if ($summaryRow.length > 0) {
                    $summaryRow.append(`
                        <div class="col-auto px-4">
                            <h6 class="text-body-tertiary">
                                Search Results : ${data.total || 0}
                                <div class="badge badge-phoenix fs-10 badge-phoenix-info">filtered</div>
                            </h6>
                        </div>
                    `);
                }
            } else {
                // Update existing search results indicator
                $searchResultsContainer.find('.text-body-tertiary').html(`
                    Search Results : ${data.total || 0}
                    <div class="badge badge-phoenix fs-10 badge-phoenix-info">filtered</div>
                `);
            }
        } else {
            // Remove search results indicator if no search
            $searchResultsContainer.remove();
        }
    }

    /**
     * Show loading state
     */
    function sites_show_loading() {
        if ($tableBody) {
            $tableBody.html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="spinner-border text-primary me-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">Loading sites...</span>
                    </td>
                </tr>
            `);
        }
    }

    /**
     * Hide loading state
     */
    function sites_hide_loading() {
        // Loading state is replaced by actual content in sites_update_table
    }

    /**
     * Show error message
     *
     * @param {string} message - Error message to display
     */
    function sites_show_error(message) {
        if ($tableBody) {
            $tableBody.html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="alert alert-subtle-danger mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${message}
                        </div>
                    </td>
                </tr>
            `);
        }
    }

    /**
     * Reset pagination to first page
     */
    function sites_reset_pagination() {
        currentPage = 1;
        currentSearch = '';
        if ($searchInput) {
            $searchInput.val('');
        }
        sites_load_page(1);
    }

    /**
     * Get current pagination statistics
     *
     * @returns {object} - Object containing pagination statistics
     */
    function sites_get_pagination_stats() {
        return {
            currentPage: currentPage,
            totalPages: totalPages,
            totalSites: totalSites,
            currentSearch: currentSearch,
            isSearchActive: currentSearch && currentSearch.trim().length > 0,
            itemsPerPage: PAGINATION_CONFIG.itemsPerPage,
            isLoading: isLoading
        };
    }

    /**
     * Public API for external access
     */
    window.WeCozaSitesPagination = {
        init: sites_init_ajax_pagination,
        loadPage: sites_load_page,
        reset: sites_reset_pagination,
        getStats: sites_get_pagination_stats
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Small delay to ensure all elements are rendered
        setTimeout(function() {
            sites_init_ajax_pagination();
        }, 100);
    });

})(jQuery);
