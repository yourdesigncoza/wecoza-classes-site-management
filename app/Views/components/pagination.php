<?php
/**
 * Pagination Component
 *
 * @package WeCozaSiteManagement\Views\Components
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$current_page = isset($current_page) ? $current_page : 1;
$total_pages = isset($total_pages) ? $total_pages : 1;
$total_items = isset($total_items) ? $total_items : 0;
$per_page = isset($per_page) ? $per_page : 20;
$base_url = isset($base_url) ? $base_url : '';
$page_param = isset($page_param) ? $page_param : 'page';
$show_info = isset($show_info) ? $show_info : true;
$show_first_last = isset($show_first_last) ? $show_first_last : true;
$max_visible_pages = isset($max_visible_pages) ? $max_visible_pages : 5;

// Don't show pagination if there's only one page
if ($total_pages <= 1) {
    return;
}

// Calculate visible page range
$half_visible = floor($max_visible_pages / 2);
$start_page = max(1, $current_page - $half_visible);
$end_page = min($total_pages, $current_page + $half_visible);

// Adjust if we're near the beginning or end
if ($end_page - $start_page + 1 < $max_visible_pages) {
    if ($start_page == 1) {
        $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
    } else {
        $start_page = max(1, $end_page - $max_visible_pages + 1);
    }
}

// Helper function to build URL
function build_pagination_url($page, $base_url, $page_param) {
    if (empty($base_url)) {
        return add_query_arg($page_param, $page);
    }
    return add_query_arg($page_param, $page, $base_url);
}
?>

<nav aria-label="Pagination Navigation" class="pagination-nav">
    <ul class="pagination justify-content-center mb-0">
        
        <!-- First Page -->
        <?php if ($show_first_last && $current_page > 1): ?>
        <li class="page-item">
            <a class="page-link" 
               href="<?php echo esc_url(build_pagination_url(1, $base_url, $page_param)); ?>"
               title="First page">
                <i class="fas fa-angle-double-left"></i>
                <span class="d-none d-sm-inline ms-1">First</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Previous Page -->
        <?php if ($current_page > 1): ?>
        <li class="page-item">
            <a class="page-link" 
               href="<?php echo esc_url(build_pagination_url($current_page - 1, $base_url, $page_param)); ?>"
               title="Previous page">
                <i class="fas fa-chevron-left"></i>
                <span class="d-none d-sm-inline ms-1">Previous</span>
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">
                <i class="fas fa-chevron-left"></i>
                <span class="d-none d-sm-inline ms-1">Previous</span>
            </span>
        </li>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
            <?php if ($i === $current_page): ?>
                <span class="page-link">
                    <?php echo esc_html($i); ?>
                    <span class="visually-hidden">(current)</span>
                </span>
            <?php else: ?>
                <a class="page-link" 
                   href="<?php echo esc_url(build_pagination_url($i, $base_url, $page_param)); ?>"
                   title="<?php printf(esc_attr'Go to page %d', $i); ?>">
                    <?php echo esc_html($i); ?>
                </a>
            <?php endif; ?>
        </li>
        <?php endfor; ?>

        <!-- Next Page -->
        <?php if ($current_page < $total_pages): ?>
        <li class="page-item">
            <a class="page-link" 
               href="<?php echo esc_url(build_pagination_url($current_page + 1, $base_url, $page_param)); ?>"
               title="Next page">
                <span class="d-none d-sm-inline me-1">Next</span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </li>
        <?php else: ?>
        <li class="page-item disabled">
            <span class="page-link">
                <span class="d-none d-sm-inline me-1">Next</span>
                <i class="fas fa-chevron-right"></i>
            </span>
        </li>
        <?php endif; ?>

        <!-- Last Page -->
        <?php if ($show_first_last && $current_page < $total_pages): ?>
        <li class="page-item">
            <a class="page-link" 
               href="<?php echo esc_url(build_pagination_url($total_pages, $base_url, $page_param)); ?>"
               title="Last page">
                <span class="d-none d-sm-inline me-1">Last</span>
                <i class="fas fa-angle-double-right"></i>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Pagination Info -->
    <?php if ($show_info): ?>
    <div class="pagination-info text-center text-muted mt-2">
        <?php
        $start_item = ($current_page - 1) * $per_page + 1;
        $end_item = min($current_page * $per_page, $total_items);
        
        if ($total_items > 0) {
            printf(
                'Showing %s to %s of %s items',
                '<strong>' . number_format_i18n($start_item) . '</strong>',
                '<strong>' . number_format_i18n($end_item) . '</strong>',
                '<strong>' . number_format_i18n($total_items) . '</strong>'
            );
        } else {
            _e('No items found';
        }
        ?>
    </div>
    <?php endif; ?>
</nav>
