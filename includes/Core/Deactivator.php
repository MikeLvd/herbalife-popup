<?php
/**
 * Plugin deactivation
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Core;

class Deactivator {
    
    /**
     * Deactivate plugin
     *
     * @return void
     */
    public static function deactivate(): void {
        // Clear any scheduled hooks
        wp_clear_scheduled_hook('herbalife_popup_cleanup');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}