<?php
/**
 * Admin class
 *
 * @package LLMS_Txt_Generator
 */

namespace LLMS_Txt_Generator;

/**
 * Admin class for settings page and admin functionality
 */
class Admin {
    /**
     * Generator instance
     *
     * @var Generator
     */
    private $generator;

    /**
     * Constructor
     *
     * @param Generator $generator Generator instance
     */
    public function __construct(Generator $generator) {
        $this->generator = $generator;
        $this->init();
    }

    /**
     * Initialize admin functionality
     *
     * @return void
     */
    public function init() {
        // Add settings page
        add_action('admin_menu', [$this, 'add_settings_page']);
        
        // Register settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Add AJAX handler for manual generation
        add_action('wp_ajax_llms_txt_generate_now', [$this, 'ajax_generate_now']);
        
        // Add AJAX handler for saving settings
        add_action('wp_ajax_llms_txt_save_settings', [$this, 'ajax_save_settings']);
        
        // Add admin notices
        add_action('admin_notices', [$this, 'admin_notices']);
    }

    /**
     * Add settings page
     *
     * @return void
     */
    public function add_settings_page() {
        add_options_page(
            esc_html__('LLMS.txt Settings', 'llmagnet-generate-llm-txt-for-wp'),
            esc_html__('LLMS.txt', 'llmagnet-generate-llm-txt-for-wp'),
            'manage_options',
            'llms-txt-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function register_settings() {
        register_setting(
            'llms_txt_settings',
            Generator::OPTION_NAME,
            [$this, 'sanitize_settings']
        );
    }

    /**
     * Sanitize settings
     *
     * @param array $input Settings input
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        // Sanitize post types
        $sanitized['post_types'] = isset($input['post_types']) && is_array($input['post_types']) 
            ? array_map('sanitize_text_field', $input['post_types']) 
            : ['post', 'page'];
        
        // Sanitize full content checkbox
        $sanitized['full_content'] = isset($input['full_content']) ? (bool) $input['full_content'] : true;
        
        // Sanitize days to include
        $sanitized['days_to_include'] = isset($input['days_to_include']) 
            ? absint($input['days_to_include']) 
            : 365;
        
        // Sanitize delete on uninstall
        $sanitized['delete_on_uninstall'] = isset($input['delete_on_uninstall']) 
            ? (bool) $input['delete_on_uninstall'] 
            : false;
        
        return $sanitized;
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Add a more specific container with a wrapper and fallback content
        echo '<div class="wrap">';
        echo '<div id="llms-txt-app" style="min-height: 500px;">';
        
        // Fallback content that will be replaced by React if it loads successfully
        echo '<h1>' . esc_html__('LLMS.txt Settings', 'llmagnet-generate-llm-txt-for-wp') . '</h1>';
        echo '<p>' . esc_html__('Loading application...', 'llmagnet-generate-llm-txt-for-wp') . '</p>';
        echo '<p>' . esc_html__('If this message persists, there may be an issue with the JavaScript application. Please check your browser console for errors.', 'llmagnet-generate-llm-txt-for-wp') . '</p>';
        
        // Add a simple status box as fallback
        $is_writable = $this->generator->is_root_writable();
        $last_generated = $this->generator->get_last_generated_time();
        
        echo '<div style="background: white; border: 1px solid #ccd0d4; padding: 15px; margin: 20px 0;">';
        echo '<h2>' . esc_html__('LLMS.txt Status', 'llmagnet-generate-llm-txt-for-wp') . '</h2>';
        echo '<p><strong>' . esc_html__('Root Directory:', 'llmagnet-generate-llm-txt-for-wp') . '</strong> ' . esc_html($this->generator->get_root_path());
        if ($is_writable) {
            echo ' <span style="color: green; font-weight: bold;">' . esc_html__('(Writable)', 'llmagnet-generate-llm-txt-for-wp') . '</span>';
        } else {
            echo ' <span style="color: red; font-weight: bold;">' . esc_html__('(Not Writable)', 'llmagnet-generate-llm-txt-for-wp') . '</span>';
        }
        echo '</p>';
        echo '<p><strong>' . esc_html__('Last Generated:', 'llmagnet-generate-llm-txt-for-wp') . '</strong> ';
        if ($last_generated) {
            echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_generated));
        } else {
            echo esc_html__('Never', 'llmagnet-generate-llm-txt-for-wp');
        }
        echo '</p>';
        echo '</div>';
        
        echo '</div>'; // Close app container
        echo '</div>'; // Close wrap
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     * @return void
     */
    public function enqueue_assets($hook) {
        if ('settings_page_llms-txt-settings' !== $hook) {
            return;
        }
        
        // Check if we're in development mode (using Vite server)
        $dev_mode = defined('LLMS_TXT_DEV_MODE') && LLMS_TXT_DEV_MODE;
        
        if ($dev_mode) {
            // Development mode - load from Vite dev server
            wp_enqueue_script(
                'llms-txt-admin-react',
                'http://localhost:5173/src/main.tsx',
                [],
                null,
                true
            );
        } else {
            // Production mode - directly load built assets without relying on manifest.json
            
            // Enqueue CSS
            wp_enqueue_style(
                'llms-txt-admin-react',
                LLMS_TXT_GENERATOR_PLUGIN_URL . 'assets/react-build/css/index.css',
                [],
                LLMS_TXT_GENERATOR_VERSION
            );
            
            // Enqueue JS
            wp_enqueue_script(
                'llms-txt-admin-react',
                LLMS_TXT_GENERATOR_PLUGIN_URL . 'assets/react-build/js/index.js',
                [],
                LLMS_TXT_GENERATOR_VERSION,
                true
            );
        }
        
        // Get all public post types except attachments
        $public_post_types = get_post_types(['public' => true], 'objects');
        unset($public_post_types['attachment']);
        
        $post_types_for_js = [];
        foreach ($public_post_types as $post_type) {
            $post_types_for_js[] = [
                'name' => $post_type->name,
                'label' => $post_type->labels->name,
            ];
        }
        
        // Pass data to JavaScript
        wp_localize_script('llms-txt-admin-react', 'llmsTxtAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('llms_txt_nonce'),
            'rootPath' => esc_html($this->generator->get_root_path()),
            'isWritable' => $this->generator->is_root_writable(),
            'lastGenerated' => $this->generator->get_last_generated_time() ? 
                date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $this->generator->get_last_generated_time()) : 
                null,
            'settings' => $this->generator->get_settings(),
            'postTypes' => $post_types_for_js,
            'pluginUrl' => LLMS_TXT_GENERATOR_PLUGIN_URL,
        ]);
    }

    /**
     * AJAX handler for manual generation
     *
     * @return void
     */
    public function ajax_generate_now() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'llms_txt_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed.', 'llmagnet-generate-llm-txt-for-wp')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have permission to perform this action.', 'llmagnet-generate-llm-txt-for-wp')]);
        }
        
        // Generate files
        $result = $this->generator->generate_all();
        
        if ($result) {
            wp_send_json_success([
                'message' => esc_html__('LLMS.txt generated successfully!', 'llmagnet-generate-llm-txt-for-wp'),
                'timestamp' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), time()),
            ]);
        } else {
            wp_send_json_error([
                'message' => esc_html__('Error generating LLMS.txt. Please check server permissions.', 'llmagnet-generate-llm-txt-for-wp'),
            ]);
        }
    }
    
    /**
     * AJAX handler for saving settings
     *
     * @return void
     */
    public function ajax_save_settings() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'llms_txt_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed.', 'llmagnet-generate-llm-txt-for-wp')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have permission to perform this action.', 'llmagnet-generate-llm-txt-for-wp')]);
        }
        
        // Get settings from POST data
        $settings = isset($_POST['settings']) ? json_decode(sanitize_textarea_field(wp_unslash($_POST['settings'])), true) : [];
        
        if (empty($settings) || !is_array($settings)) {
            wp_send_json_error(['message' => esc_html__('Invalid settings data.', 'llmagnet-generate-llm-txt-for-wp')]);
        }
        
        // Sanitize and save settings
        $sanitized_settings = $this->sanitize_settings($settings);
        $result = $this->generator->update_settings($sanitized_settings);
        
        if ($result) {
            wp_send_json_success([
                'message' => esc_html__('Settings saved successfully.', 'llmagnet-generate-llm-txt-for-wp'),
            ]);
        } else {
            wp_send_json_error([
                'message' => esc_html__('Error saving settings.', 'llmagnet-generate-llm-txt-for-wp'),
            ]);
        }
    }

    /**
     * Display admin notices
     *
     * @return void
     */
    public function admin_notices() {
        // Check if root directory is writable and we're on the settings page
        $current_screen = get_current_screen();
        if (!$this->generator->is_root_writable() && $current_screen && 'settings_page_llms-txt-settings' === $current_screen->id) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php 
                    echo wp_kses(
                        sprintf(
                            /* translators: %s: WordPress root directory path */
                            __('LLMS.txt Generator cannot write to your WordPress root directory (%s). Please check file permissions.', 'llmagnet-generate-llm-txt-for-wp'),
                            '<code>' . esc_html($this->generator->get_root_path()) . '</code>'
                        ),
                        [
                            'code' => [],
                        ]
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
} 