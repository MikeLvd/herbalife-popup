<?php
/**
 * Helper functions
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Core;

class Helper {
    
    /**
     * Check if popup should be displayed
     *
     * @return bool
     */
    public static function should_display_popup(): bool {
        $plugin = Plugin::get_instance();
        
        // Allow filtering for preview mode
        $force_display = apply_filters('herbalife_popup_should_display', false);
        if ($force_display) {
            return true;
        }
        
        // Check if enabled
        if (!$plugin->get_option('enabled', false)) {
            return false;
        }
        
        // Check logged-in users
        if (!$plugin->get_option('show_loggedin', false) && is_user_logged_in()) {
            return false;
        }
        
        // Check page restrictions
        return self::check_page_restrictions();
    }
    
    /**
     * Check page restrictions
     *
     * @return bool
     */
    private static function check_page_restrictions(): bool {
        $show_on = Plugin::get_instance()->get_option('show_on_pages', 'all');
        
        switch ($show_on) {
            case 'home':
                return is_front_page() || is_home();
                
            case 'cart':
                return class_exists('WooCommerce') && is_cart();
                
            case 'checkout':
                return class_exists('WooCommerce') && is_checkout();
                
            case 'all':
            default:
                return true;
        }
    }
    
    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip(): string {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var(
                        $ip,
                        FILTER_VALIDATE_IP,
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                    ) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}