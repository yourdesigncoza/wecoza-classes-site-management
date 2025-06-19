<?php

/**
 * Plugin uninstall handler
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WeCoza Site Management Uninstaller class
 */
class WeCoza_Site_Management_Uninstaller {

    /**
     * Uninstall the plugin
     *
     * @since 1.0.0
     */
    public static function uninstall() {
        // Check if user has permission to uninstall
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Clear all plugin options
        self::clear_plugin_options();

        // Clear all transients
        self::clear_all_transients();

        // Remove upload directories (optional - commented out for safety)
        // self::remove_upload_directories();

        // Clear scheduled events
        self::clear_scheduled_events();

        // Note: We don't drop the PostgreSQL sites table as it may be used by other plugins
        // and contains important data

        // Log uninstall
        error_log('WeCoza Site Management Plugin uninstalled');
    }

    /**
     * Clear all plugin options
     *
     * @since 1.0.0
     */
    private static function clear_plugin_options() {
        // Get all options with our prefix
        global $wpdb;
        
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                'wecoza_site_management_%'
            )
        );

        // Delete each option
        foreach ($options as $option) {
            delete_option($option->option_name);
        }
    }

    /**
     * Clear all plugin transients
     *
     * @since 1.0.0
     */
    private static function clear_all_transients() {
        global $wpdb;
        
        // Clear transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_wecoza_site_management_%',
                '_transient_timeout_wecoza_site_management_%'
            )
        );
    }

    /**
     * Remove upload directories
     *
     * @since 1.0.0
     */
    private static function remove_upload_directories() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/wecoza-site-management';

        if (file_exists($plugin_upload_dir)) {
            self::recursive_rmdir($plugin_upload_dir);
        }
    }

    /**
     * Recursively remove directory
     *
     * @param string $dir Directory path
     * @since 1.0.0
     */
    private static function recursive_rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        self::recursive_rmdir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Clear scheduled events
     *
     * @since 1.0.0
     */
    private static function clear_scheduled_events() {
        $scheduled_events = array(
            'wecoza_site_management_cleanup',
            'wecoza_site_management_maintenance',
        );

        foreach ($scheduled_events as $event) {
            wp_clear_scheduled_hook($event);
        }
    }
}
