/**
 * Sites Table Pagination JavaScript for WeCoza Site Management Plugin
 *
 * Implements client-side pagination and search functionality for the sites table.
 * Searches site name, address, and client name fields with debouncing for performance.
 * Uses cached data for optimal performance without additional database queries.
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
        tableRowSelector: 'tbody tr',
        paginationContainerSelector: '#sites-pagination-container',
        itemsPerPage: 20           // Number of sites to display per page
    };

    /**
     * Search state
     */
    let searchTimeout = null;
    let $searchInput = null;
    let $table = null;
    let $tableRows = null;
    let $paginationContainer = null;
    let totalRows = 0;
    let visibleRows = 0;

    /**
     * Pagination state
     */
    let currentPage = 1;
    let totalPages = 1;
    let filteredRows = [];

    /**
     * Initialization state
     */
    let isInitialized = false;

    /**
     * Initialize the pagination and search functionality
     */
    function sites_init_table_pagination() {
        // Prevent duplicate initialization
        if (isInitialized) {
            console.log('WeCoza Sites: Already initialized, skipping duplicate initialization');
            return;
        }

        // Find elements
        $searchInput = $(PAGINATION_CONFIG.searchInputSelector);
        $table = $(PAGINATION_CONFIG.tableSelector);
        $tableRows = $table.find(PAGINATION_CONFIG.tableRowSelector);
        $paginationContainer = $(PAGINATION_CONFIG.paginationContainerSelector);

        // Validate elements exist
        if ($table.length === 0) {
            console.warn('WeCoza Sites: Table not found');
            return;
        }

        if ($tableRows.length === 0) {
            console.warn('WeCoza Sites: No table rows found');
            return;
        }

        if ($paginationContainer.length === 0) {
            console.warn('WeCoza Sites: Pagination container not found');
            return;
        }

        // Initialize counters
        totalRows = $tableRows.length;
        visibleRows = totalRows;

        // Bind search event with debouncing (if search input exists)
        if ($searchInput.length > 0) {
            $searchInput.on('input keyup paste', function() {
                const searchTerm = $(this).val();
                sites_debounced_search(searchTerm);
            });

            // Clear search on form reset
            $searchInput.closest('form').on('reset', function() {
                setTimeout(function() {
                    sites_perform_search('');
                }, 10);
            });
        }

        // Add search status indicator
        sites_add_search_status_indicator();

        // Initialize pagination
        sites_init_pagination();

        // Show initial page
        sites_update_pagination_display();

        // Mark as initialized
        isInitialized = true;

        console.log('WeCoza Sites: Table pagination and search initialized successfully');
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
            sites_perform_search(searchTerm);
        }, PAGINATION_CONFIG.debounceDelay);
    }

    /**
     * Perform the actual search filtering
     *
     * @param {string} searchTerm - The search term to filter by
     */
    function sites_perform_search(searchTerm) {
        // Normalize search term
        const normalizedSearchTerm = searchTerm.toLowerCase().trim();
        filteredRows = [];

        // Filter rows based on search term
        $tableRows.filter('[data-site-id]').each(function() {
            const $row = $(this);

            // Get data from row attributes
            const siteName = ($row.data('site-name') || '').toString().toLowerCase();
            const address = ($row.data('address') || '').toString().toLowerCase();
            const clientName = ($row.data('client-name') || '').toString().toLowerCase();

            // Check if search term matches any field
            let isMatch = true;
            if (normalizedSearchTerm.length >= PAGINATION_CONFIG.minSearchLength) {
                isMatch = sites_search_matches(siteName, address, clientName, normalizedSearchTerm);
            }

            if (isMatch) {
                filteredRows.push($row);
            }
        });

        visibleRows = filteredRows.length;

        // Reset to page 1 when search changes
        currentPage = 1;

        // Update pagination and display
        sites_update_pagination_display();
        sites_update_search_status(searchTerm, visibleRows, totalRows);
    }

    /**
     * Check if search term matches the site data
     *
     * @param {string} siteName - The site name to search in
     * @param {string} address - The address to search in
     * @param {string} clientName - The client name to search in
     * @param {string} searchTerm - The search term to look for
     * @returns {boolean} - True if match found
     */
    function sites_search_matches(siteName, address, clientName, searchTerm) {
        // Direct substring match in any field
        if (siteName.includes(searchTerm) ||
            address.includes(searchTerm) ||
            clientName.includes(searchTerm)) {
            return true;
        }

        // Split search term and check if all parts match
        const searchParts = searchTerm.split(/\s+/).filter(part => part.length > 0);
        if (searchParts.length > 1) {
            return searchParts.every(part =>
                siteName.includes(part) ||
                address.includes(part) ||
                clientName.includes(part)
            );
        }

        return false;
    }

    /**
     * Update search status indicator
     *
     * @param {string} searchTerm - The current search term
     * @param {number} visible - Number of visible rows
     * @param {number} total - Total number of rows
     */
    function sites_update_search_status(searchTerm, visible, total) {
        const $statusIndicator = $('#sites-search-status');

        if ($statusIndicator.length === 0) {
            return;
        }

        // Show/hide status based on search activity
        if (searchTerm.trim().length === 0) {
            $statusIndicator.hide();
            return;
        }

        // Update status text
        let statusText = '';
        if (visible === 0) {
            statusText = `No sites found matching "${searchTerm}"`;
        } else if (visible === total) {
            statusText = `Showing all ${total} sites`;
        } else {
            statusText = `Showing ${visible} of ${total} sites matching "${searchTerm}"`;
        }

        $statusIndicator.text(statusText).show();
    }

    /**
     * Reset search functionality
     */
    function sites_reset_search() {
        if ($searchInput && $searchInput.length > 0) {
            $searchInput.val('');
            currentPage = 1;
            sites_perform_search('');
        }
    }

    /**
     * Add search status indicator to the interface
     */
    function sites_add_search_status_indicator() {
        // Check if status indicator already exists
        if ($('#sites-search-status').length > 0) {
            return;
        }
        
        // Create status indicator
        const $statusIndicator = $('<span>', {
            id: 'sites-search-status',
            class: 'badge badge-phoenix badge-phoenix-primary mb-2',
            style: 'display: none;'
        });

        // Insert before the table (between card header and table)
        $table.before($statusIndicator);
    }

    /**
     * Initialize pagination functionality
     */
    function sites_init_pagination() {
        // Initialize filtered rows with all rows (excluding empty state rows)
        filteredRows = $tableRows.filter(':not([data-site-id=""])').toArray().map(row => $(row));
        totalRows = filteredRows.length;
        visibleRows = totalRows;

        // Calculate initial pagination
        sites_calculate_pagination_info();

        // Show pagination container if needed
        if (totalRows > PAGINATION_CONFIG.itemsPerPage) {
            $paginationContainer.show();
        }
    }

    /**
     * Calculate pagination information
     */
    function sites_calculate_pagination_info() {
        totalPages = Math.ceil(filteredRows.length / PAGINATION_CONFIG.itemsPerPage);
        if (totalPages === 0) totalPages = 1;

        // Ensure current page is within bounds
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }
    }

    /**
     * Update pagination display and show appropriate rows
     */
    function sites_update_pagination_display() {
        // Calculate pagination info
        sites_calculate_pagination_info();

        // Hide all data rows first (keep empty state rows visible)
        $tableRows.filter('[data-site-id]').hide();

        // Show only rows for current page
        const startIndex = (currentPage - 1) * PAGINATION_CONFIG.itemsPerPage;
        const endIndex = startIndex + PAGINATION_CONFIG.itemsPerPage;

        for (let i = startIndex; i < endIndex && i < filteredRows.length; i++) {
            filteredRows[i].show();
        }

        // Update pagination controls
        sites_update_pagination_controls();
    }

    /**
     * Update pagination controls HTML
     */
    function sites_update_pagination_controls() {
        if (!$paginationContainer || $paginationContainer.length === 0) return;

        // Calculate display range
        const startItem = filteredRows.length === 0 ? 0 : (currentPage - 1) * PAGINATION_CONFIG.itemsPerPage + 1;
        const endItem = Math.min(currentPage * PAGINATION_CONFIG.itemsPerPage, filteredRows.length);
        const totalItems = filteredRows.length;

        // Update pagination info spans
        $paginationContainer.find('#pagination-start').text(startItem);
        $paginationContainer.find('#pagination-end').text(endItem);
        $paginationContainer.find('#pagination-total').text(totalItems);

        // Build pagination navigation HTML
        let paginationHTML = '';

        // Previous button
        const prevDisabled = currentPage <= 1;
        if (prevDisabled) {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&laquo;</span>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" data-sites-pagination="prev" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const isActive = i === currentPage;
            if (isActive) {
                paginationHTML += `<li class="page-item active" aria-current="page">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHTML += `<li class="page-item">
                    <a class="page-link" href="#" data-page-number="${i}">${i}</a>
                </li>`;
            }
        }

        // Next button
        const nextDisabled = currentPage >= totalPages;
        if (nextDisabled) {
            paginationHTML += `<li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&raquo;</span>
            </li>`;
        } else {
            paginationHTML += `<li class="page-item">
                <a class="page-link" href="#" data-sites-pagination="next" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>`;
        }

        // Update pagination navigation
        const $paginationNav = $paginationContainer.find('#sites-pagination');
        if ($paginationNav.length > 0) {
            $paginationNav.html(paginationHTML);
        }

        // Bind click events
        sites_bind_pagination_events();

        // Show/hide pagination container based on need
        if (filteredRows.length > PAGINATION_CONFIG.itemsPerPage) {
            $paginationContainer.show();
        } else {
            $paginationContainer.hide();
        }
    }

    /**
     * Navigate to specific page
     *
     * @param {number} pageNumber - Page number to navigate to
     */
    function sites_go_to_page(pageNumber) {
        if (pageNumber < 1 || pageNumber > totalPages) {
            return;
        }

        currentPage = pageNumber;
        sites_update_pagination_display();
    }

    /**
     * Reset pagination to first page
     */
    function sites_reset_pagination() {
        currentPage = 1;
        sites_update_pagination_display();
    }

    /**
     * Bind pagination event handlers
     */
    function sites_bind_pagination_events() {
        if (!$paginationContainer || $paginationContainer.length === 0) return;

        // Previous button
        $paginationContainer.find('[data-sites-pagination="prev"]').off('click').on('click', function(e) {
            e.preventDefault();
            if (!$(this).closest('.page-item').hasClass('disabled')) {
                sites_go_to_page(currentPage - 1);
            }
        });

        // Next button
        $paginationContainer.find('[data-sites-pagination="next"]').off('click').on('click', function(e) {
            e.preventDefault();
            if (!$(this).closest('.page-item').hasClass('disabled')) {
                sites_go_to_page(currentPage + 1);
            }
        });

        // Page number buttons
        $paginationContainer.find('[data-page-number]').off('click').on('click', function(e) {
            e.preventDefault();
            const pageNumber = parseInt($(this).data('page-number'));
            sites_go_to_page(pageNumber);
        });
    }

    /**
     * Get current search and pagination statistics
     *
     * @returns {object} - Object containing search and pagination statistics
     */
    function sites_get_pagination_stats() {
        return {
            totalRows: totalRows,
            visibleRows: visibleRows,
            filteredRows: filteredRows.length,
            searchTerm: $searchInput && $searchInput.length > 0 ? $searchInput.val() : '',
            isSearchActive: $searchInput && $searchInput.length > 0 ? $searchInput.val().trim().length > 0 : false,
            currentPage: currentPage,
            totalPages: totalPages,
            itemsPerPage: PAGINATION_CONFIG.itemsPerPage
        };
    }

    /**
     * Public API for external access
     */
    window.WeCozaSitesPagination = {
        init: sites_init_table_pagination,
        goToPage: sites_go_to_page,
        reset: sites_reset_search,
        resetPagination: sites_reset_pagination,
        getStats: sites_get_pagination_stats,
        forceReinit: function() {
            isInitialized = false;
            sites_init_table_pagination();
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Small delay to ensure all elements are rendered
        setTimeout(function() {
            sites_init_table_pagination();
        }, 100);
    });

})(jQuery);
