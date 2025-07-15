<?php
/**
 * Frontend assets
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Frontend;

use HerbalifePopup\Core\Plugin;
use HerbalifePopup\Core\Helper;

class Assets {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }
    
    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueue_assets(): void {
        if (!Helper::should_display_popup()) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style(
            'herbalife-popup',
            HERBALIFE_POPUP_URL . 'assets/css/popup.css',
            [],
            HERBALIFE_POPUP_VERSION
        );
        
        // Enqueue scripts
        wp_enqueue_script(
            'herbalife-popup',
            HERBALIFE_POPUP_URL . 'assets/js/popup.js',
            [],
            HERBALIFE_POPUP_VERSION,
            true
        );
        
        // Localize script
        $options = Plugin::get_instance()->get_all_options();
        
        wp_localize_script('herbalife-popup', 'HerbalifePopup', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('herbalife_popup_nonce'),
            'settings' => [
                'cookieDays' => $options['cookie_days'],
                'cookieName' => 'herbalife_popup_closed',
                'viewsCookieName' => 'herbalife_popup_views',
                'delay' => $options['delay'],
                'maxViews' => $options['max_views'],
                'trigger' => $options['trigger'],
                'scrollDistance' => $options['scroll'],
                'sessionBased' => $options['session_based'],
            ],
            'i18n' => [
                'close' => __('Close', 'herbalife-popup'),
            ],
        ]);
    }
}