<?php
/**
 * Uninstall LLMS.txt Generator
 *
 * @package LLMS_Txt_Generator
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Get plugin settings
$settings = get_option('llms_txt_generator_settings', []);

// Delete files if setting is enabled
if (isset($settings['delete_on_uninstall']) && $settings['delete_on_uninstall']) {
    // Initialize WordPress filesystem
    require_once ABSPATH . 'wp-admin/includes/file.php';
    WP_Filesystem();
    global $wp_filesystem;
    
    // Delete llms.txt file
    $llms_txt_path = trailingslashit(ABSPATH) . 'llms.txt';
    if ($wp_filesystem->exists($llms_txt_path)) {
        $wp_filesystem->delete($llms_txt_path);
    }
    
    // Delete llms-docs directory
    $docs_dir = trailingslashit(ABSPATH) . 'llms-docs';
    if ($wp_filesystem->exists($docs_dir)) {
        $wp_filesystem->delete($docs_dir, true);
    }
}

// Delete plugin options
delete_option('llms_txt_generator_settings');
delete_transient('llms_txt_generator_last_run'); 