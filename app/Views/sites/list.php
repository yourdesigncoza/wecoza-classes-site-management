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
    <!-- Header -->
    <div class="sites-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="sites-title mb-0">
                    <?php _e('Sites Management', 'wecoza-site-management'); ?>
                    <span class="badge bg-secondary ms-2"><?php echo esc_html($total_sites); ?></span>
                </h3>
            </div>
            <div class="col-md-6 text-end">
                <?php if ($can_edit): ?>
                    <a href="<?php echo esc_url(add_query_arg('action', 'create')); ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-plus"></i> <?php _e('Add New Site', 'wecoza-site-management'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <?php if ($show_search): ?>
    <div class="sites-search-form mb-4">
        <form method="GET" class="row g-3" id="sites-search-form">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" 
                           class="form-control" 
                           name="sites_search" 
                           id="sites_search"
                           value="<?php echo esc_attr($search); ?>" 
                           placeholder="<?php esc_attr_e('Search sites by name or address...', 'wecoza-site-management'); ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> <?php _e('Search', 'wecoza-site-management'); ?>
                    </button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <?php if (!empty($search)): ?>
                        <a href="<?php echo esc_url(remove_query_arg(['sites_search', 'sites_page'])); ?>" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> <?php _e('Clear', 'wecoza-site-management'); ?>
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-primary" id="refresh-sites">
                        <i class="fas fa-sync-alt"></i> <?php _e('Refresh', 'wecoza-site-management'); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Loading Indicator -->
    <div id="sites-loading" class="text-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden"><?php _e('Loading...', 'wecoza-site-management'); ?></span>
        </div>
        <p class="mt-2 text-muted"><?php _e('Loading sites...', 'wecoza-site-management'); ?></p>
    </div>

    <!-- Sites Table -->
    <div class="sites-table-container" id="sites-table-container">
        <?php if (!empty($sites)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover sites-table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"><?php _e('Site Name', 'wecoza-site-management'); ?></th>
                        <th scope="col"><?php _e('Client', 'wecoza-site-management'); ?></th>
                        <th scope="col"><?php _e('Address', 'wecoza-site-management'); ?></th>
                        <th scope="col"><?php _e('Created', 'wecoza-site-management'); ?></th>
                        <?php if ($can_edit || $can_delete): ?>
                        <th scope="col" class="text-center"><?php _e('Actions', 'wecoza-site-management'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sites as $site): ?>
                    <tr data-site-id="<?php echo esc_attr($site->getSiteId()); ?>">
                        <td>
                            <strong><?php echo esc_html($site->getSiteName()); ?></strong>
                            <br>
                            <small class="text-muted">ID: <?php echo esc_html($site->getSiteId()); ?></small>
                        </td>
                        <td>
                            <?php 
                            $client = isset($clients[$site->getClientId()]) ? $clients[$site->getClientId()] : null;
                            if ($client): 
                            ?>
                                <span class="badge bg-info text-dark">
                                    <?php echo esc_html($client->getClientName()); ?>
                                </span>
                                <br>
                                <small class="text-muted">ID: <?php echo esc_html($site->getClientId()); ?></small>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Unknown Client', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($site->getAddress())): ?>
                                <span class="site-address" title="<?php echo esc_attr($site->getAddress()); ?>">
                                    <?php echo esc_html(wp_trim_words($site->getAddress(), 8, '...')); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('No address', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($site->getCreatedAt()): ?>
                                <span title="<?php echo esc_attr($site->getCreatedAt()); ?>">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($site->getCreatedAt()))); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($can_edit || $can_delete): ?>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" 
                                        class="btn btn-outline-info btn-view-site" 
                                        data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                        title="<?php esc_attr_e('View Details', 'wecoza-site-management'); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($can_edit): ?>
                                <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'site_id' => $site->getSiteId()])); ?>" 
                                   class="btn btn-outline-primary"
                                   title="<?php esc_attr_e('Edit Site', 'wecoza-site-management'); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($can_delete): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger btn-delete-site" 
                                        data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                        data-site-name="<?php echo esc_attr($site->getSiteName()); ?>"
                                        title="<?php esc_attr_e('Delete Site', 'wecoza-site-management'); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3"></i>
            <h5><?php _e('No Sites Found', 'wecoza-site-management'); ?></h5>
            <p class="mb-0">
                <?php if (!empty($search)): ?>
                    <?php printf(__('No sites match your search for "%s".', 'wecoza-site-management'), esc_html($search)); ?>
                    <br>
                    <a href="<?php echo esc_url(remove_query_arg(['sites_search', 'sites_page'])); ?>" 
                       class="btn btn-sm btn-outline-primary mt-2">
                        <?php _e('View All Sites', 'wecoza-site-management'); ?>
                    </a>
                <?php else: ?>
                    <?php _e('No sites have been created yet.', 'wecoza-site-management'); ?>
                    <?php if ($can_edit): ?>
                        <br>
                        <a href="<?php echo esc_url(add_query_arg('action', 'create')); ?>" 
                           class="btn btn-sm btn-primary mt-2">
                            <?php _e('Create Your First Site', 'wecoza-site-management'); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($show_pagination && $total_pages > 1): ?>
    <nav aria-label="<?php esc_attr_e('Sites pagination', 'wecoza-site-management'); ?>" class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- Previous Page -->
            <?php if ($current_page > 1): ?>
            <li class="page-item">
                <a class="page-link" 
                   href="<?php echo esc_url(add_query_arg('sites_page', $current_page - 1)); ?>">
                    <i class="fas fa-chevron-left"></i> <?php _e('Previous', 'wecoza-site-management'); ?>
                </a>
            </li>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                <a class="page-link" 
                   href="<?php echo esc_url(add_query_arg('sites_page', $i)); ?>">
                    <?php echo esc_html($i); ?>
                </a>
            </li>
            <?php endfor; ?>

            <!-- Next Page -->
            <?php if ($current_page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" 
                   href="<?php echo esc_url(add_query_arg('sites_page', $current_page + 1)); ?>">
                    <?php _e('Next', 'wecoza-site-management'); ?> <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- Pagination Info -->
        <div class="text-center text-muted mt-2">
            <?php
            $start_item = ($current_page - 1) * $per_page + 1;
            $end_item = min($current_page * $per_page, $total_sites);
            printf(
                __('Showing %d to %d of %d sites', 'wecoza-site-management'),
                $start_item,
                $end_item,
                $total_sites
            );
            ?>
        </div>
    </nav>
    <?php endif; ?>
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
