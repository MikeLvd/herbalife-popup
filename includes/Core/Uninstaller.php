<?php
/**
 * Plugin uninstallation
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Core;

class Uninstaller {
    
    /**
     * Uninstall plugin
     *
     * @return void
     */
    public static function uninstall(): void {
        // Check if we should remove data
        if (!defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }
        
        // Remove all plugin options
        $options = [
            'enabled',
            'show_loggedin',
            'bg_color',
            'accent_color',
            'cookie_days',
            'distributor',
            'title_1',
            'content_1',
            'title_2',
            'content_2',
            'footer_note',
            'delay',
            'max_views',
            'trigger',
            'scroll',
            'show_on_pages',
            'session_based',
        ];
        
        foreach ($options as $option) {
            delete_option('herbalife_popup_' . $option);
        }
        
        // Drop analytics table
        global $wpdb;
        $table_name = $wpdb->prefix . 'herbalife_popup_analytics';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}