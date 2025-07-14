<?php
/**
 * LLMS.txt Generator
 *
 * @package           LLMS_Txt_Generator
 * @author            LLLMagnet
 * @copyright         2023
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       LLMagnet LLM.txt Generator
 * Plugin URI:        https://lllmagnet.com
 * Description:       Automatically creates and maintains an llms.txt file and associated Markdown files for LLM crawlers.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            LLLMagnet
 * Author URI:        https://lllmagnet.com
 * Text Domain:       llmagnet-generate-llm-txt-for-wp
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('LLMS_TXT_GENERATOR_VERSION', '1.0.0');
define('LLMS_TXT_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LLMS_TXT_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLMS_TXT_GENERATOR_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Development mode - set to true when developing with Vite
define('LLMS_TXT_DEV_MODE', false);

// Custom error handling removed for production compliance

/**
 * Simple autoloader for plugin classes
 */
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'LLMS_Txt_Generator\\';

    // Base directory for the namespace prefix
    $base_dir = LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators, append with .php
    // Add 'class-' prefix and convert to lowercase for WordPress naming convention
    $file = $base_dir . 'class-' . strtolower(str_replace('\\', '/', $relative_class)) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    } else {
        // Try without lowercase conversion as a fallback
        $file_alt = $base_dir . 'class-' . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file_alt)) {
            require $file_alt;
        } else {
            // File not found - handled silently in production
        }
    }
});

// Check if all required files exist
$required_files = [
    LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-main.php',
    LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-generator.php',
    LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-admin.php',
    LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-cron.php',
    LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-cli.php',
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    if (!function_exists('deactivate_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    deactivate_plugins(plugin_basename(__FILE__));
    
    add_action('admin_notices', function() use ($missing_files) {
        echo '<div class="error"><p><strong>LLMS.txt Generator Error:</strong> The following required files are missing:<br>';
        foreach ($missing_files as $file) {
            echo esc_html($file) . '<br>';
        }
        echo 'Please reinstall the plugin.</p></div>';
    });
    
    return; // Stop execution
}

// Include the main plugin class
require_once LLMS_TXT_GENERATOR_PLUGIN_DIR . 'includes/class-main.php';

// Initialize the plugin
function llms_txt_generator_init() {
    try {
        $plugin = new LLMS_Txt_Generator\Main();
        $plugin->init();
    } catch (Exception $e) {
        // Error handled via admin notices and plugin deactivation
        
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        deactivate_plugins(plugin_basename(__FILE__));
        
        add_action('admin_notices', function() use ($e) {
            echo '<div class="error"><p><strong>LLMS.txt Generator Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        });
    }
}
add_action('plugins_loaded', 'llms_txt_generator_init');

// Register activation hook
register_activation_hook(__FILE__, function() {
    try {
        LLMS_Txt_Generator\Generator::activate();
    } catch (Exception $e) {
        wp_die('Error activating LLMS.txt Generator: ' . esc_html($e->getMessage()));
    }
});

// Register deactivation hook
register_deactivation_hook(__FILE__, ['LLMS_Txt_Generator\Generator', 'deactivate']); 