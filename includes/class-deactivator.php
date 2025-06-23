<?php

/**
 * Plugin deactivation handler
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WeCoza Site Management Deactivator class
 */
class WeCoza_Site_Management_Deactivator {

    /**
     * Deactivate the plugin
     *
     * @since 1.0.0
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();

        // Clear transients
        self::clear_transients();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Update deactivation flag
        update_option('wecoza_site_management_plugin_activated', false);
        update_option('wecoza_site_management_deactivated_at', current_time('mysql'));

        // Log deactivation
        error_log('WeCoza Site Management Plugin deactivated');
    }

    /**
     * Clear scheduled events
     *
     * @since 1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear any scheduled cron events
        $scheduled_events = array(
            'wecoza_site_management_cleanup',
            'wecoza_site_management_maintenance',
        );

        foreach ($scheduled_events as $event) {
            $timestamp = wp_next_scheduled($event);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $event);
            }
        }
    }

    /**
     * Clear plugin transients
     *
     * @since 1.0.0
     */
    private static function clear_transients() {
        // Clear plugin-specific transients
        $transients = array(
            'wecoza_site_management_sites_cache',
            'wecoza_site_management_clients_cache',
            'wecoza_site_management_stats_cache',
            'wecoza_sites_debug_cache_v1', // Current cache key
        );

        foreach ($transients as $transient) {
            delete_transient($transient);
        }
    }
}
