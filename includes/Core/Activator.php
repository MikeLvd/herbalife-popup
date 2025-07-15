<?php
/**
 * Plugin activation
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Core;

class Activator {
    
    /**
     * Activate plugin
     *
     * @return void
     */
    public static function activate(): void {
        // Set default options
        $defaults = [
            'enabled' => 0,
            'show_loggedin' => 0,
            'bg_color' => '#000000',
            'accent_color' => '#7ac142',
            'cookie_days' => 30,
            'distributor' => 'Κωνσταντίνος Σαντούση',
            'title_1' => 'ΕΙΣΑΣΤΕ ΗΔΗ ΠΕΛΑΤΗΣ ΤΗΣ HERBALIFE NUTRITION;',
            'content_1' => 'Για να συνεχίσετε τις αγορές σας και να έχετε πρόσβαση στα προϊόντα, παρακαλούμε επικοινωνήστε με τον Ανεξάρτητο Συνεργάτη που σας εξυπηρετεί.',
            'title_2' => 'ΕΙΣΑΣΤΕ ΗΔΗ ΑΝΕΞΑΡΤΗΤΟΣ ΣΥΝΕΡΓΑΤΗΣ ΤΗΣ HERBALIFE NUTRITION;',
            'content_2' => 'Αυτή η ιστοσελίδα προορίζεται μόνο για τους πελάτες μου. Για παραγγελίες και εργαλεία για την επιχείρησή σας, παρακαλούμε επισκεφθείτε το MyHerbalife.com',
            'footer_note' => 'Αυτή την ιστοσελίδα τη διαχειρίζεται ένας Ανεξάρτητος Συνεργάτης της Herbalife Nutrition',
            'delay' => 2000,
            'max_views' => 1,
            'trigger' => 'delay',
            'scroll' => 150,
            'show_on_pages' => 'all',
            'session_based' => 0,
        ];
        
        foreach ($defaults as $option => $value) {
            if (get_option('herbalife_popup_' . $option) === false) {
                add_option('herbalife_popup_' . $option, $value);
            }
        }
        
        // Create database table for analytics (optional)
        self::create_analytics_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create analytics table
     *
     * @return void
     */
    private static function create_analytics_table(): void {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'herbalife_popup_analytics';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            action varchar(50) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            referrer text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}