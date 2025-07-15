<?php
/**
 * Frontend popup display
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Frontend;

use HerbalifePopup\Core\Plugin;
use HerbalifePopup\Core\Helper;

class Display {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_footer', [$this, 'render_popup'], 99);
        
        // Handle preview mode
        add_action('init', [$this, 'handle_preview_mode']);
    }
    
    /**
     * Handle preview mode
     *
     * @return void
     */
    public function handle_preview_mode(): void {
        if (!isset($_GET['herbalife_popup_preview']) || !isset($_GET['nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_GET['nonce'], 'herbalife_popup_preview')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Force show popup in preview mode
        add_filter('herbalife_popup_should_display', '__return_true');
        
        // Add preview mode class
        add_filter('body_class', function($classes) {
            $classes[] = 'herbalife-popup-preview-mode';
            return $classes;
        });
        
        // Auto-show popup in preview mode
        add_action('wp_footer', function() {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(function() {
                        const wrapper = document.getElementById('herbalife-popup-wrapper');
                        if (wrapper) {
                            wrapper.classList.add('active');
                            wrapper.setAttribute('aria-hidden', 'false');
                        }
                    }, 500);
                });
            </script>
            <?php
        }, 100);
    }
    
    /**
     * Render popup HTML
     *
     * @return void
     */
    public function render_popup(): void {
        if (!Helper::should_display_popup()) {
            return;
        }
        
        $options = Plugin::get_instance()->get_all_options();
        
        // Prepare content
        $content_1 = wpautop(wp_kses_post($options['content_1']));
        $content_2 = wpautop(wp_kses_post($options['content_2']));
        ?>
        
        <style>
        :root {
            --herbalife-popup-bg: <?php echo esc_attr($options['bg_color']); ?>;
            --herbalife-popup-accent: <?php echo esc_attr($options['accent_color']); ?>;
        }
        </style>
        
        <div id="herbalife-popup-wrapper" class="herbalife-popup-wrapper" role="dialog" aria-modal="true" aria-labelledby="herbalife-popup-title">
            <div class="herbalife-popup-overlay" aria-hidden="true"></div>
            <div class="herbalife-popup-container">
                <button type="button" 
                        class="herbalife-popup-close" 
                        aria-label="<?php esc_attr_e('Close popup', 'herbalife-popup'); ?>"
                        data-action="close">
                    <span aria-hidden="true">&times;</span>
                </button>
                
                <div class="herbalife-popup-content">
                <div class="herbalife-popup-header" id="herbalife-popup-title">
                    <p class="herbalife-popup-distributor">
                        <span class="herbalife-popup-icon" aria-hidden="true">ℹ️</span><?php echo esc_html($options['footer_note']); ?>: <strong><?php echo esc_html($options['distributor']); ?></strong>
                    </p>
                </div>
                    <div class="herbalife-popup-body">
                        <div class="herbalife-popup-column herbalife-popup-column--left">
                            <h2 class="herbalife-popup-title"><?php echo esc_html($options['title_1']); ?></h2>
                            <div class="herbalife-popup-text"><?php echo $content_1; ?></div>
                        </div>
                        
                        <div class="herbalife-popup-column herbalife-popup-column--right">
                            <h2 class="herbalife-popup-title"><?php echo esc_html($options['title_2']); ?></h2>
                            <div class="herbalife-popup-text"><?php echo $content_2; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}