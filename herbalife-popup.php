<?php
/**
 * Plugin Name: Herbalife Popup Notice
 * Description: Advanced responsive popup notice for Herbalife sites with modern UI and enhanced controls.
 * Version: 2.0.0
 * Requires at least: 5.8
 * Requires PHP: 8.0
 * Author: Mike & ChatGPT
 * License: GPL v2 or later
 * Text Domain: herbalife-popup
 * Domain Path: /languages
 *
 * @package HerbalifePopup
 */

namespace HerbalifePopup;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HERBALIFE_POPUP_VERSION', '2.0.0');
define('HERBALIFE_POPUP_FILE', __FILE__);
define('HERBALIFE_POPUP_PATH', plugin_dir_path(__FILE__));
define('HERBALIFE_POPUP_URL', plugin_dir_url(__FILE__));
define('HERBALIFE_POPUP_BASENAME', plugin_basename(__FILE__));

// Check PHP version
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('Herbalife Popup requires PHP 8.0 or higher. Please upgrade your PHP version.', 'herbalife-popup'); ?></p>
        </div>
        <?php
    });
    return;
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'HerbalifePopup\\';
    $base_dir = __DIR__ . '/includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
add_action('plugins_loaded', function() {
    Core\Plugin::get_instance();
}, 10);

// Activation hook
register_activation_hook(__FILE__, function() {
    Core\Activator::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    Core\Deactivator::deactivate();
});

// Uninstall hook
register_uninstall_hook(__FILE__, [Core\Uninstaller::class, 'uninstall']);