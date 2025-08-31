<?php
/**
 * LLMagnet AI SEO Optimizer
 *
 * @package           LLMagnet_AI_SEO_Optimizer
 * @author            Ido Navarro
 * @copyright         2025
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       LLMagnet AI SEO Optimizer
 * Plugin URI:        https://llmagnet.com
 * Description:       Automatically creates and maintains an llms.txt file and associated Markdown files for LLM crawlers.
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Ido Navarro
 * Author URI:        https://spank.co.il
 * Text Domain:       llmagnet-llm-txt-generator
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('LLMAGNET_AISEO_VERSION', '1.0.1');
define('LLMAGNET_AISEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LLMAGNET_AISEO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LLMAGNET_AISEO_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Development mode - set to true when developing with Vite
define('LLMAGNET_AISEO_DEV_MODE', false);

// Custom error handling removed for production compliance

/**
 * Simple autoloader for plugin classes to avoid using composer
 */
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'LLMagnet_AI_SEO_Optimizer\\';

    // Base directory for the namespace prefix
    $base_dir = LLMAGNET_AISEO_PLUGIN_DIR . 'includes/';

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
    LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-main.php',
    LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-generator.php',
    LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-admin.php',
    LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-cron.php',
    LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-cli.php',
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
        echo '<div class="error"><p><strong>LLMagnet AI SEO Optimizer Error:</strong> The following required files are missing:<br>';
        foreach ($missing_files as $file) {
            echo esc_html($file) . '<br>';
        }
        echo 'Please reinstall the plugin.</p></div>';
    });
    
    return; // Stop execution
}

// Include the main plugin class
require_once LLMAGNET_AISEO_PLUGIN_DIR . 'includes/class-main.php';

// Initialize the plugin
function llmagnet_ai_seo_init() {
    try {
        $plugin = new LLMagnet_AI_SEO_Optimizer\Main();
        $plugin->init();
    } catch (Exception $e) {
        // Error handled via admin notices and plugin deactivation
        
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        deactivate_plugins(plugin_basename(__FILE__));
        
        add_action('admin_notices', function() use ($e) {
            echo '<div class="error"><p><strong>LLMagnet AI SEO Optimizer Error:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        });
    }
}
add_action('plugins_loaded', 'llmagnet_ai_seo_init');

// Removed explicit textdomain loading per WP.org recommendation (WP 4.6+ auto-loads language packs)

// Register activation hook
register_activation_hook(__FILE__, function() {
    try {
        LLMagnet_AI_SEO_Optimizer\Generator::activate();
    } catch (Exception $e) {
        wp_die('Error activating LLMagnet AI SEO Optimizer: ' . esc_html($e->getMessage()));
    }
});

// Register deactivation hook
register_deactivation_hook(__FILE__, ['LLMagnet_AI_SEO_Optimizer\Generator', 'deactivate']); 