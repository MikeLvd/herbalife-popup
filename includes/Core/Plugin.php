<?php
/**
 * Main plugin class
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Core;

use HerbalifePopup\Admin\Settings;
use HerbalifePopup\Frontend\Display;
use HerbalifePopup\Frontend\Assets;

class Plugin {
    
    /**
     * Plugin instance
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;
    
    /**
     * Options cache
     *
     * @var array
     */
    private array $options_cache = [];
    
    /**
     * Get plugin instance
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     *
     * @return void
     */
    private function init(): void {
        // Load text domain
        add_action('init', [$this, 'load_textdomain']);
        
        // Initialize components
        if (is_admin()) {
            new Settings();
        } else {
            new Display();
            new Assets();
        }
        
        // Add AJAX handlers
        add_action('wp_ajax_herbalife_popup_track', [$this, 'track_popup_interaction']);
        add_action('wp_ajax_nopriv_herbalife_popup_track', [$this, 'track_popup_interaction']);
    }
    
    /**
     * Load plugin text domain
     *
     * @return void
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'herbalife-popup',
            false,
            dirname(HERBALIFE_POPUP_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Get plugin option with caching
     *
     * @param string $option Option name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_option(string $option, $default = null) {
        if (!isset($this->options_cache[$option])) {
            $this->options_cache[$option] = get_option('herbalife_popup_' . $option, $default);
        }
        return $this->options_cache[$option];
    }
    
    /**
     * Get all plugin options
     *
     * @return array
     */
    public function get_all_options(): array {
        return [
            'enabled' => (bool) $this->get_option('enabled', false),
            'show_loggedin' => (bool) $this->get_option('show_loggedin', false),
            'bg_color' => $this->get_option('bg_color', '#000000'),
            'accent_color' => $this->get_option('accent_color', '#7ac142'),
            'cookie_days' => (int) $this->get_option('cookie_days', 30),
            'distributor' => $this->get_option('distributor', 'Κωνσταντίνος Σαντούση'),
            'title_1' => $this->get_option('title_1', 'ΕΙΣΑΣΤΕ ΗΔΗ ΠΕΛΑΤΗΣ ΤΗΣ HERBALIFE NUTRITION;'),
            'content_1' => $this->get_option('content_1', '...'),
            'title_2' => $this->get_option('title_2', 'ΕΙΣΑΣΤΕ ΗΔΗ ΑΝΕΞΑΡΤΗΤΟΣ ΣΥΝΕΡΓΑΤΗΣ ΤΗΣ HERBALIFE NUTRITION;'),
            'content_2' => $this->get_option('content_2', '...'),
            'footer_note' => $this->get_option('footer_note', 'Αυτή την ιστοσελίδα τη διαχειρίζεται ένας Ανεξάρτητος Συνεργάτης της Herbalife Nutrition'),
            'delay' => (int) $this->get_option('delay', 0),
            'max_views' => (int) $this->get_option('max_views', 1),
            'trigger' => $this->get_option('trigger', 'delay'),
            'scroll' => (int) $this->get_option('scroll', 150),
            'show_on_pages' => $this->get_option('show_on_pages', 'all'),
            'session_based' => (bool) $this->get_option('session_based', false),
        ];
    }
    
    /**
     * Track popup interaction via AJAX
     *
     * @return void
     */
    public function track_popup_interaction(): void {
        // Verify nonce
        if (!check_ajax_referer('herbalife_popup_nonce', 'nonce', false)) {
            wp_die('Security check failed');
        }
        
        $action = sanitize_text_field($_POST['popup_action'] ?? '');
        
        // Track the interaction (you can extend this to save to database)
        do_action('herbalife_popup_interaction', $action);
        
        wp_send_json_success(['tracked' => true]);
    }
}