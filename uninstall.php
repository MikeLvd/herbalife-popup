<?php
/**
 * Uninstall Herbalife Popup
 *
 * @package HerbalifePopup
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load the uninstaller class and run it
require_once plugin_dir_path(__FILE__) . 'includes/Core/Uninstaller.php';
HerbalifePopup\Core\Uninstaller::uninstall();