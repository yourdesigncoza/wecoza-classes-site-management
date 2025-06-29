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
    <?php
    // Load data using CACHED OPTIMIZED QUERY (eliminates N+1 problem + caching)
    // Check if force refresh is requested via URL parameter
    $force_refresh = isset($_GET['refresh_cache']) && $_GET['refresh_cache'] === '1';
    $debug_result = \WeCozaSiteManagement\Models\SiteModel::getCachedSitesWithClientsForDebug(1000, $force_refresh);

    // Extract data from cached/optimized query result
    $debug_sites_with_clients = $debug_result['sites_with_clients'];

    // POPULATE TABLE DATA: Transform cached data for table display
    // Override empty $sites and $clients arrays with cached data
    if (!empty($debug_sites_with_clients) && empty($sites)) {
        // Load ALL cached data without PHP filtering (JavaScript will handle search/pagination)
        $sites = [];
        $clients = [];

        foreach ($debug_sites_with_clients as $site_data) {
            // Create a simple site object with getter methods
            $site_obj = new class($site_data) {
                private $data;

                public function __construct($data) {
                    $this->data = $data;
                }

                public function getSiteId() { return $this->data['site_id']; }
                public function getClientId() { return $this->data['client_id']; }
                public function getSiteName() { return $this->data['site_name']; }
                public function getAddress() { return $this->data['address']; }
                public function getCreatedAt() { return $this->data['created_at']; }
                public function getUpdatedAt() { return $this->data['updated_at']; }
            };

            $sites[] = $site_obj;

            // Build clients array for lookup
            if (!empty($site_data['client_name'])) {
                $client_obj = new class($site_data) {
                    private $data;

                    public function __construct($data) {
                        $this->data = $data;
                    }

                    public function getClientId() { return $this->data['client_id']; }
                    public function getClientName() { return $this->data['client_name']; }
                };

                $clients[$site_data['client_id']] = $client_obj;
            }
        }

        // Update statistics (all data loaded, no filtering)
        $total_sites = count($debug_sites_with_clients);
        $unique_clients = count($clients);
    }
    ?>

    <!-- Sites Content -->
    <div id="sites-content">
        <!-- Sites Table Card (always show for consistency) -->
        <?php if (empty($sites) && empty($search)): ?>
            <!-- No Sites Found (only when no search) -->
            <div class="alert alert-subtle-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                <div>
                    <h6 class="alert-heading mb-1">No Sites Found</h6>
                    <p class="mb-0">
                        There are currently no sites in the database. Create a new site to get started.
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
                                Sites Management
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
                                       placeholder="Search sites... (Press Enter)"
                                       aria-label="Search"
                                       title="Type your search query and press Enter to search">
                                <svg class="svg-inline--fa fa-magnifying-glass search-box-icon" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"></path></svg>
                            </form>
                        </div>
                        <?php endif; ?>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshSites()">
                                    Refresh
                                    <i class="bi bi-arrow-clockwise ms-1"></i>
                                </button>
                                <?php if ($can_edit): ?>
                                <a href="<?php echo esc_url(add_query_arg('action', 'create', remove_query_arg(['sites_search', 'sites_page', 'site_id']))); ?>"
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Add New Site
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
                                    <h6 class="text-body-tertiary">Total Sites : <?php echo $total_sites; ?></h6>
                                </div>
                                <div class="col-auto px-4 border-end">
                                    <h6 class="text-body-tertiary" id="unique-clients-count">Unique Clients : <?php echo $unique_clients; ?></h6>
                                </div>
                                <?php if (!empty($search)): ?>
                                <div class="col-auto px-4">
                                    <h6 class="text-body-tertiary">
                                        Search Results :
                                        <?php if (count($sites) > 0): ?>
                                            <?php echo count($sites); ?> <div class="badge badge-phoenix fs-10 badge-phoenix-info">filtered</div>
                                        <?php else: ?>
                                            <span class="badge badge-phoenix fs-10 badge-phoenix-danger">No Search Results</span>
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
                                    <th scope="col" class="border-0 ps-4" data-sortable="true" data-sort-key="site_id" data-sort-type="numeric">
                                        ID
                                        <i class="bi bi-hash ms-1"></i>
                                        <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                                    </th>
                                    <th scope="col" class="border-0" data-sortable="true" data-sort-key="site_name" data-sort-type="text">
                                        Site Name
                                        <i class="bi bi-building ms-1"></i>
                                        <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                                    </th>
                                    <th scope="col" class="border-0" data-sortable="true" data-sort-key="client_name" data-sort-type="text">
                                        Client
                                        <i class="bi bi-person-badge ms-1"></i>
                                        <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                                    </th>
                                    <th scope="col" class="border-0" data-sortable="true" data-sort-key="address" data-sort-type="text">
                                        Address
                                        <i class="bi bi-geo-alt ms-1"></i>
                                        <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                                    </th>
                                    <th scope="col" class="border-0" data-sortable="true" data-sort-key="created_at" data-sort-type="date">
                                        Created
                                        <i class="bi bi-calendar-date ms-1"></i>
                                        <span class="sort-indicator d-none"><i class="bi bi-chevron-up"></i></span>
                                    </th>
                                    <th scope="col" class="border-0 pe-4" data-sortable="false">
                                        Actions
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
                                                <h6 class="mb-2">No sites match your search</h6>
                                                <p class="mb-0"><?php printf('No sites found for "%s"', esc_html($search)); ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php elseif (empty($sites)): ?>
                                    <!-- No sites at all -->
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-building fs-1 mb-3 d-block"></i>
                                                <h6 class="mb-2">No sites found</h6>
                                                <p class="mb-0">No sites have been created yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sites as $site): ?>
                                    <tr data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                        data-site-name="<?php echo esc_attr($site->getSiteName()); ?>"
                                        data-client-id="<?php echo esc_attr($site->getClientId()); ?>"
                                        data-client-name="<?php echo esc_attr(isset($clients[$site->getClientId()]) ? $clients[$site->getClientId()]->getClientName() : ''); ?>"
                                        data-address="<?php echo esc_attr($site->getAddress()); ?>"
                                        data-created-at="<?php echo esc_attr($site->getCreatedAt()); ?>"
                                        data-created-timestamp="<?php echo esc_attr($site->getCreatedAt() ? strtotime($site->getCreatedAt()) : 0); ?>">
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
                                                </span> <small class="text-muted">ID: <?php echo esc_html($site->getClientId()); ?></small>
                                            <?php else: ?>
                                                <span class="badge fs-10 badge-phoenix badge-phoenix-warning">
                                                    Unknown Client
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
                                                <span class="text-muted">No address</span>
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
                                                <!--
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item btn-view-site"
                                                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>">
                                                            View Details
                                                            <i class="bi bi-eye ms-2"></i>
                                                        </button>
                                                    </li>
                                                -->
                                                    <?php if ($can_edit): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'site_id' => $site->getSiteId()])); ?>">
                                                            Edit Site
                                                            <i class="bi bi-pencil ms-2"></i>
                                                        </a>
                                                    </li>
                                                    <?php endif; ?>
                                                <!--
                                                    <?php if ($can_delete): ?>
                                                
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button type="button"
                                                                class="dropdown-item text-danger btn-delete-site"
                                                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                                                data-site-name="<?php echo esc_attr($site->getSiteName()); ?>">
                                                            Delete Site
                                                            <i class="bi bi-trash ms-2"></i>
                                                        </button>
                                                    </li>
                                                    <?php endif; ?>
                                                    -->
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

                <!-- Pagination Container -->
                <div class="card-footer bg-body-tertiary py-2" id="sites-pagination-container" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="pagination-info">
                            <small class="text-muted">
                                Showing <span id="pagination-start">1</span> to <span id="pagination-end">20</span>
                                of <span id="pagination-total"><?php echo count($sites); ?></span> sites
                            </small>
                        </div>
                        <nav aria-label="Sites pagination">
                            <ul class="pagination pagination-sm mb-0" id="sites-pagination">
                                <!-- Pagination buttons will be generated here -->
                            </ul>
                        </nav>
                    </div>
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
                    Site Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="siteDetailsModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Basic functionality for sites management
});

function refreshSites() {
    // Simple page reload for refresh functionality
    window.location.reload();
}

// View site details functionality
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
    // Modal functionality for site details
    alert('Site details modal. Site ID: ' + siteId);
}
</script>
