<?php
/**
 * Sites List View Template
 *
 * @package WeCozaSiteManagement\Views
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$sites = isset($sites) ? $sites : [];
$clients = isset($clients) ? $clients : [];
$total_sites = isset($total_sites) ? $total_sites : 0;
$unique_clients = isset($unique_clients) ? $unique_clients : 0;
$current_page = isset($current_page) ? $current_page : 1;
$total_pages = isset($total_pages) ? $total_pages : 1;
$per_page = isset($per_page) ? $per_page : 20;
$search = isset($search) ? $search : '';
$show_search = isset($show_search) ? $show_search : true;
$show_pagination = isset($show_pagination) ? $show_pagination : true;
$can_edit = isset($can_edit) ? $can_edit : false;
$can_delete = isset($can_delete) ? $can_delete : false;
?>

<div class="wecoza-sites-list-container">
    <!-- Sites Content -->
    <div id="sites-content">
        <!-- Sites Table Card (always show for consistency) -->
        <?php if (empty($sites) && empty($search)): ?>
            <!-- No Sites Found (only when no search) -->
            <div class="alert alert-subtle-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1"><?php _e('No Sites Found', 'wecoza-site-management'); ?></h6>
                    <p class="mb-0">
                        <?php _e('There are currently no sites in the database. Create a new site to get started.', 'wecoza-site-management'); ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <!-- Sites Table -->
            <div class="card shadow-none border my-3" data-component-card="data-component-card">
                <div class="card-header p-3 border-bottom">
                    <div class="row g-3 justify-content-between align-items-center mb-3">
                        <div class="col-12 col-md">
                            <h4 class="text-body mb-0" data-anchor="data-anchor" id="sites-table-header">
                                <?php _e('Sites Management', 'wecoza-site-management'); ?>
                                <i class="bi bi-building ms-2"></i>
                            </h4>
                        </div>
                        <?php if ($show_search): ?>
                        <div class="search-box col-auto">
                            <form class="position-relative" method="GET" id="sites-search-form">
                                <input class="form-control search-input search form-control-sm"
                                       type="search"
                                       name="sites_search"
                                       id="sites_search"
                                       value="<?php echo esc_attr($search); ?>"
                                       placeholder="<?php esc_attr_e('Search sites... (Press Enter)', 'wecoza-site-management'); ?>"
                                       aria-label="Search"
                                       title="<?php esc_attr_e('Type your search query and press Enter to search', 'wecoza-site-management'); ?>">
                                <svg class="svg-inline--fa fa-magnifying-glass search-box-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"></path></svg>
                            </form>
                        </div>
                        <?php endif; ?>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshSites()">
                                    <?php _e('Refresh', 'wecoza-site-management'); ?>
                                    <i class="bi bi-arrow-clockwise ms-1"></i>
                                </button>
                                <?php if ($can_edit): ?>
                                <a href="<?php echo esc_url(add_query_arg('action', 'create', remove_query_arg(['sites_search', 'sites_page', 'site_id']))); ?>"
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    <?php _e('Add New Site', 'wecoza-site-management'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <!-- Summary strip -->
                    <div class="col-12">
                        <div class="scrollbar">
                            <div class="row g-0 flex-nowrap">
                                <div class="col-auto border-end pe-4">
                                    <h6 class="text-body-tertiary"><?php _e('Total Sites', 'wecoza-site-management'); ?> : <?php echo $total_sites; ?></h6>
                                </div>
                                <div class="col-auto px-4 border-end">
                                    <h6 class="text-body-tertiary" id="unique-clients-count"><?php _e('Unique Clients', 'wecoza-site-management'); ?> : <?php echo $unique_clients; ?></h6>
                                </div>
                                <?php if (!empty($search)): ?>
                                <div class="col-auto px-4">
                                    <h6 class="text-body-tertiary">
                                        <?php _e('Search Results', 'wecoza-site-management'); ?> :
                                        <?php if (count($sites) > 0): ?>
                                            <?php echo count($sites); ?> <div class="badge badge-phoenix fs-10 badge-phoenix-info">filtered</div>
                                        <?php else: ?>
                                            <span class="badge badge-phoenix fs-10 badge-phoenix-danger"><?php _e('No Search Results', 'wecoza-site-management'); ?></span>
                                        <?php endif; ?>
                                    </h6>
                                </div>
                                <?php endif; ?>
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
                                        <?php _e('ID', 'wecoza-site-management'); ?>
                                        <i class="bi bi-hash ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        <?php _e('Site Name', 'wecoza-site-management'); ?>
                                        <i class="bi bi-building ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        <?php _e('Client', 'wecoza-site-management'); ?>
                                        <i class="bi bi-person-badge ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        <?php _e('Address', 'wecoza-site-management'); ?>
                                        <i class="bi bi-geo-alt ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0">
                                        <?php _e('Created', 'wecoza-site-management'); ?>
                                        <i class="bi bi-calendar-date ms-1"></i>
                                    </th>
                                    <th scope="col" class="border-0 pe-4">
                                        <?php _e('Actions', 'wecoza-site-management'); ?>
                                        <i class="bi bi-gear ms-1"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sites) && !empty($search)): ?>
                                    <!-- Empty search results row -->
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-search fs-1 mb-3 d-block"></i>
                                                <h6 class="mb-2"><?php _e('No sites match your search', 'wecoza-site-management'); ?></h6>
                                                <p class="mb-0"><?php printf(__('No sites found for "%s"', 'wecoza-site-management'), esc_html($search)); ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sites as $site): ?>
                                    <tr data-site-id="<?php echo esc_attr($site->getSiteId()); ?>">
                                        <td class="py-2 align-middle text-center fs-8 white-space-nowrap">
                                            <span class="badge fs-10 badge-phoenix badge-phoenix-secondary">
                                                #<?php echo esc_html($site->getSiteId()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">
                                                <?php echo esc_html($site->getSiteName()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $client = isset($clients[$site->getClientId()]) ? $clients[$site->getClientId()] : null;
                                            if ($client):
                                            ?>
                                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                                    <?php echo esc_html($client->getClientName()); ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">ID: <?php echo esc_html($site->getClientId()); ?></small>
                                            <?php else: ?>
                                                <span class="badge fs-10 badge-phoenix badge-phoenix-warning">
                                                    <?php _e('Unknown Client', 'wecoza-site-management'); ?>
                                                    <i class="bi bi-exclamation-triangle ms-1"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($site->getAddress())): ?>
                                                <span class="site-address text-nowrap" title="<?php echo esc_attr($site->getAddress()); ?>">
                                                    <?php echo esc_html(wp_trim_words($site->getAddress(), 6, '...')); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?php _e('No address', 'wecoza-site-management'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($site->getCreatedAt()): ?>
                                                <span class="text-nowrap" title="<?php echo esc_attr($site->getCreatedAt()); ?>">
                                                    <?php echo esc_html(date_i18n('M j, Y', strtotime($site->getCreatedAt()))); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-link text-body btn-sm dropdown-toggle"
                                                        style="text-decoration: none;"
                                                        type="button"
                                                        id="dropdownMenuButton<?php echo $site->getSiteId(); ?>"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $site->getSiteId(); ?>">
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item btn-view-site"
                                                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>">
                                                            <?php _e('View Details', 'wecoza-site-management'); ?>
                                                            <i class="bi bi-eye ms-2"></i>
                                                        </button>
                                                    </li>
                                                    <?php if ($can_edit): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'site_id' => $site->getSiteId()])); ?>">
                                                            <?php _e('Edit Site', 'wecoza-site-management'); ?>
                                                            <i class="bi bi-pencil ms-2"></i>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($can_delete): ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item text-danger btn-delete-site"
                                                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                                                data-site-name="<?php echo esc_attr($site->getSiteName()); ?>">
                                                            <?php _e('Delete Site', 'wecoza-site-management'); ?>
                                                            <i class="bi bi-trash ms-2"></i>
                                                        </button>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- AJAX Pagination Container (positioned within the card) -->
                <div id="sites-pagination" class="d-flex justify-content-between mt-3 px-4 pb-3" style="display: none;">
                    <!-- Pagination content will be dynamically inserted here -->
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<!-- Site Details Modal -->
<div class="modal fade" id="siteDetailsModal" tabindex="-1" aria-labelledby="siteDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="siteDetailsModalLabel">
                    <?php _e('Site Details', 'wecoza-site-management'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="siteDetailsModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php _e('Close', 'wecoza-site-management'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // The AJAX pagination functionality is handled by sites-table-pagination.js
    // This script only handles the refresh button and modal functionality
});

function refreshSites() {
    // Use the AJAX pagination reset function if available
    if (typeof WeCozaSitesPagination !== 'undefined') {
        WeCozaSitesPagination.reset();
    } else {
        // Fallback: reload the page
        const url = new URL(window.location);
        url.searchParams.delete('sites_search');
        url.searchParams.delete('sites_page');
        window.location.href = url.toString();
    }
}

// View site details functionality (if modal exists)
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-view-site')) {
        const button = e.target.closest('.btn-view-site');
        const siteId = button.getAttribute('data-site-id');

        // Check if modal exists, otherwise redirect to details page
        const modal = document.getElementById('siteDetailsModal');
        if (modal && typeof wecoza_site_management_ajax !== 'undefined') {
            // Load content via AJAX if available
            loadSiteDetailsModal(siteId);
        } else {
            // Fallback: redirect to details page
            window.location.href = '?action=view&site_id=' + siteId;
        }
    }
});

function loadSiteDetailsModal(siteId) {
    const modal = new bootstrap.Modal(document.getElementById('siteDetailsModal'));
    const modalBody = document.getElementById('siteDetailsModalBody');

    if (modalBody) {
        modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        modal.show();

        // Load content via AJAX (if AJAX handler exists)
        if (typeof wecoza_site_management_ajax !== 'undefined') {
            const formData = new FormData();
            formData.append('action', 'wecoza_get_site_details');
            formData.append('nonce', wecoza_site_management_ajax.nonce);
            formData.append('site_id', siteId);

            fetch(wecoza_site_management_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalBody.innerHTML = data.data;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-subtle-danger">Failed to load site details.</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="alert alert-subtle-danger">An error occurred while loading site details.</div>';
            });
        }
    }
}
</script>
