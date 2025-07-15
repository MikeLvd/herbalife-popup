<?php
/**
 * Admin settings
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup\Admin;

use HerbalifePopup\Core\Plugin;

class Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }
    
    /**
     * Register plugin settings
     *
     * @return void
     */
    public function register_settings(): void {
        $settings = [
            'enabled' => ['type' => 'boolean', 'sanitize_callback' => 'absint'],
            'show_loggedin' => ['type' => 'boolean', 'sanitize_callback' => 'absint'],
            'bg_color' => ['type' => 'string', 'sanitize_callback' => 'sanitize_hex_color'],
            'accent_color' => ['type' => 'string', 'sanitize_callback' => 'sanitize_hex_color'],
            'cookie_days' => ['type' => 'integer', 'sanitize_callback' => [$this, 'sanitize_cookie_days']],
            'distributor' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'title_1' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'content_1' => ['type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
            'title_2' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'content_2' => ['type' => 'string', 'sanitize_callback' => 'wp_kses_post'],
            'footer_note' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'delay' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            'max_views' => ['type' => 'integer', 'sanitize_callback' => [$this, 'sanitize_max_views']],
            'trigger' => ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_trigger']],
            'scroll' => ['type' => 'integer', 'sanitize_callback' => 'absint'],
            'show_on_pages' => ['type' => 'string', 'sanitize_callback' => [$this, 'sanitize_show_on_pages']],
            'session_based' => ['type' => 'boolean', 'sanitize_callback' => 'absint'],
        ];
        
        foreach ($settings as $setting => $args) {
            register_setting(
                'herbalife_popup_options_group',
                'herbalife_popup_' . $setting,
                $args
            );
        }
    }
    
    /**
     * Add settings page to admin menu
     *
     * @return void
     */
    public function add_settings_page(): void {
        add_options_page(
            __('Herbalife Popup Settings', 'herbalife-popup'),
            __('Herbalife Popup', 'herbalife-popup'),
            'manage_options',
            'herbalife-popup-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        if ('settings_page_herbalife-popup-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'herbalife-popup-admin',
            HERBALIFE_POPUP_URL . 'assets/css/admin.css',
            [],
            HERBALIFE_POPUP_VERSION
        );
        
        wp_enqueue_script(
            'herbalife-popup-admin',
            HERBALIFE_POPUP_URL . 'assets/js/admin.js',
            ['jquery', 'wp-color-picker'],
            HERBALIFE_POPUP_VERSION,
            true
        );
        
        // Localize script with necessary data
        wp_localize_script('herbalife-popup-admin', 'herbalifePopupAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('herbalife_popup_admin'),
            'previewUrl' => add_query_arg([
                'herbalife_popup_preview' => '1',
                'nonce' => wp_create_nonce('herbalife_popup_preview')
            ], home_url()),
            'i18n' => [
                'saveFirst' => __('Please save your settings before previewing.', 'herbalife-popup'),
                'previewError' => __('Could not load preview. Please try again.', 'herbalife-popup'),
            ]
        ]);
        
        wp_enqueue_style('wp-color-picker');
    }
    
    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $options = Plugin::get_instance()->get_all_options();
        ?>
        <div class="wrap herbalife-popup-settings">
            <h1><?php esc_html_e('Herbalife Popup Settings', 'herbalife-popup'); ?></h1>
            
            <?php if (isset($_GET['settings-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved successfully!', 'herbalife-popup'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('herbalife_popup_options_group');
                do_settings_sections('herbalife_popup_options_group');
                ?>
                
                <div class="herbalife-settings-tabs">
                    <h2 class="nav-tab-wrapper">
                        <a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e('General', 'herbalife-popup'); ?></a>
                        <a href="#content" class="nav-tab"><?php esc_html_e('Content', 'herbalife-popup'); ?></a>
                        <a href="#appearance" class="nav-tab"><?php esc_html_e('Appearance', 'herbalife-popup'); ?></a>
                        <a href="#behavior" class="nav-tab"><?php esc_html_e('Behavior', 'herbalife-popup'); ?></a>
                    </h2>
                    
                    <!-- General Tab -->
                    <div id="general" class="tab-content active">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Enable Popup', 'herbalife-popup'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="herbalife_popup_enabled" value="1" <?php checked(1, $options['enabled']); ?> />
                                        <?php esc_html_e('Enable the popup on your website', 'herbalife-popup'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Show to Logged-in Users', 'herbalife-popup'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="herbalife_popup_show_loggedin" value="1" <?php checked(1, $options['show_loggedin']); ?> />
                                        <?php esc_html_e('Display popup to logged-in users', 'herbalife-popup'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Display On', 'herbalife-popup'); ?></th>
                                <td>
                                    <select name="herbalife_popup_show_on_pages">
                                        <option value="all" <?php selected($options['show_on_pages'], 'all'); ?>><?php esc_html_e('All Pages', 'herbalife-popup'); ?></option>
                                        <option value="home" <?php selected($options['show_on_pages'], 'home'); ?>><?php esc_html_e('Homepage Only', 'herbalife-popup'); ?></option>
                                        <option value="cart" <?php selected($options['show_on_pages'], 'cart'); ?>><?php esc_html_e('Cart Page Only', 'herbalife-popup'); ?></option>
                                        <option value="checkout" <?php selected($options['show_on_pages'], 'checkout'); ?>><?php esc_html_e('Checkout Page Only', 'herbalife-popup'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Preview', 'herbalife-popup'); ?></th>
                                <td>
                                    <button type="button" class="button button-secondary" id="preview-popup">
                                        <?php esc_html_e('Preview Popup', 'herbalife-popup'); ?>
                                    </button>
                                    <span class="description"><?php esc_html_e('See how your popup will look', 'herbalife-popup'); ?></span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Content Tab -->
                    <div id="content" class="tab-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Distributor Name', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_distributor" 
                                           value="<?php echo esc_attr($options['distributor']); ?>" 
                                           class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    <h3><?php esc_html_e('Left Column', 'herbalife-popup'); ?></h3>
                                </th>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Title', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_title_1" 
                                           value="<?php echo esc_attr($options['title_1']); ?>" 
                                           class="large-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Content', 'herbalife-popup'); ?></th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $options['content_1'],
                                        'herbalife_popup_content_1',
                                        [
                                            'textarea_name' => 'herbalife_popup_content_1',
                                            'textarea_rows' => 5,
                                            'media_buttons' => false,
                                            'teeny' => true,
                                        ]
                                    );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">
                                    <h3><?php esc_html_e('Right Column', 'herbalife-popup'); ?></h3>
                                </th>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Title', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_title_2" 
                                           value="<?php echo esc_attr($options['title_2']); ?>" 
                                           class="large-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Content', 'herbalife-popup'); ?></th>
                                <td>
                                    <?php
                                    wp_editor(
                                        $options['content_2'],
                                        'herbalife_popup_content_2',
                                        [
                                            'textarea_name' => 'herbalife_popup_content_2',
                                            'textarea_rows' => 5,
                                            'media_buttons' => false,
                                            'teeny' => true,
                                        ]
                                    );
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Footer Note', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_footer_note" 
                                           value="<?php echo esc_attr($options['footer_note']); ?>" 
                                           class="large-text" />
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Appearance Tab -->
                    <div id="appearance" class="tab-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Background Color', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_bg_color" 
                                           value="<?php echo esc_attr($options['bg_color']); ?>" 
                                           class="color-picker" />
                                    <p class="description"><?php esc_html_e('Overlay background color', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Accent Color', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="text" name="herbalife_popup_accent_color" 
                                           value="<?php echo esc_attr($options['accent_color']); ?>" 
                                           class="color-picker" />
                                    <p class="description"><?php esc_html_e('Color for titles, buttons, and borders', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Behavior Tab -->
                    <div id="behavior" class="tab-content">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('Trigger Type', 'herbalife-popup'); ?></th>
                                <td>
                                    <select name="herbalife_popup_trigger" id="herbalife_popup_trigger">
                                        <option value="delay" <?php selected($options['trigger'], 'delay'); ?>><?php esc_html_e('Time Delay', 'herbalife-popup'); ?></option>
                                        <option value="scroll" <?php selected($options['trigger'], 'scroll'); ?>><?php esc_html_e('Scroll Position', 'herbalife-popup'); ?></option>
                                        <option value="exit" <?php selected($options['trigger'], 'exit'); ?>><?php esc_html_e('Exit Intent', 'herbalife-popup'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="trigger-option trigger-delay">
                                <th scope="row"><?php esc_html_e('Delay (milliseconds)', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="number" name="herbalife_popup_delay" 
                                           value="<?php echo esc_attr($options['delay']); ?>" 
                                           min="0" step="100" />
                                    <p class="description"><?php esc_html_e('e.g., 2000 = 2 seconds', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                            <tr class="trigger-option trigger-scroll">
                                <th scope="row"><?php esc_html_e('Scroll Distance (pixels)', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="number" name="herbalife_popup_scroll" 
                                           value="<?php echo esc_attr($options['scroll']); ?>" 
                                           min="0" step="10" />
                                    <p class="description"><?php esc_html_e('Show popup after scrolling this many pixels', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Max Views Per Visitor', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="number" name="herbalife_popup_max_views" 
                                           value="<?php echo esc_attr($options['max_views']); ?>" 
                                           min="1" max="100" />
                                    <p class="description"><?php esc_html_e('Maximum times to show popup to the same visitor', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Cookie Duration (days)', 'herbalife-popup'); ?></th>
                                <td>
                                    <input type="number" name="herbalife_popup_cookie_days" 
                                           value="<?php echo esc_attr($options['cookie_days']); ?>" 
                                           min="1" max="365" />
                                    <p class="description"><?php esc_html_e('Remember visitor preferences for this many days', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Session-Based Display', 'herbalife-popup'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="herbalife_popup_session_based" value="1" <?php checked(1, $options['session_based'] ?? 0); ?> />
                                        <?php esc_html_e('Show popup on every new session (ignores cookie duration)', 'herbalife-popup'); ?>
                                    </label>
                                    <p class="description"><?php esc_html_e('When enabled, the popup will show again each time the visitor returns to your site, even if they closed it before.', 'herbalife-popup'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Sanitize cookie days
     *
     * @param mixed $value Input value
     * @return int
     */
    public function sanitize_cookie_days($value): int {
        $value = absint($value);
        return min(max($value, 1), 365);
    }
    
    /**
     * Sanitize max views
     *
     * @param mixed $value Input value
     * @return int
     */
    public function sanitize_max_views($value): int {
        $value = absint($value);
        return min(max($value, 1), 100);
    }
    
    /**
     * Sanitize trigger type
     *
     * @param string $value Input value
     * @return string
     */
    public function sanitize_trigger(string $value): string {
        $allowed = ['delay', 'scroll', 'exit'];
        return in_array($value, $allowed, true) ? $value : 'delay';
    }
    
    /**
     * Sanitize show on pages
     *
     * @param string $value Input value
     * @return string
     */
    public function sanitize_show_on_pages(string $value): string {
        $allowed = ['all', 'home', 'cart', 'checkout'];
        return in_array($value, $allowed, true) ? $value : 'all';
    }
}