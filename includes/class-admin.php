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
            esc_html__('LLMS.txt Settings', 'llms-txt-generator'),
            esc_html__('LLMS.txt', 'llms-txt-generator'),
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
        
        add_settings_section(
            'llms_txt_general_section',
            esc_html__('General Settings', 'llms-txt-generator'),
            [$this, 'render_general_section'],
            'llms-txt-settings'
        );
        
        add_settings_field(
            'llms_txt_post_types',
            esc_html__('Content Types to Include', 'llms-txt-generator'),
            [$this, 'render_post_types_field'],
            'llms-txt-settings',
            'llms_txt_general_section'
        );
        
        add_settings_field(
            'llms_txt_full_content',
            esc_html__('Content Export', 'llms-txt-generator'),
            [$this, 'render_full_content_field'],
            'llms-txt-settings',
            'llms_txt_general_section'
        );
        
        add_settings_field(
            'llms_txt_days_to_include',
            esc_html__('Time Period', 'llms-txt-generator'),
            [$this, 'render_days_field'],
            'llms-txt-settings',
            'llms_txt_general_section'
        );
        
        add_settings_field(
            'llms_txt_delete_on_uninstall',
            esc_html__('Cleanup on Uninstall', 'llms-txt-generator'),
            [$this, 'render_delete_field'],
            'llms-txt-settings',
            'llms_txt_general_section'
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
        
        $is_writable = $this->generator->is_root_writable();
        $last_generated = $this->generator->get_last_generated_time();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php if (!$is_writable) : ?>
                <div class="notice notice-error">
                    <p><?php esc_html_e('WordPress root directory is not writable. LLMS.txt cannot be generated.', 'llms-txt-generator'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="llms-txt-status-box">
                <h2><?php esc_html_e('LLMS.txt Status', 'llms-txt-generator'); ?></h2>
                <p>
                    <strong><?php esc_html_e('Root Directory:', 'llms-txt-generator'); ?></strong>
                    <?php echo esc_html($this->generator->get_root_path()); ?>
                    <?php if ($is_writable) : ?>
                        <span class="llms-txt-status-ok"><?php esc_html_e('(Writable)', 'llms-txt-generator'); ?></span>
                    <?php else : ?>
                        <span class="llms-txt-status-error"><?php esc_html_e('(Not Writable)', 'llms-txt-generator'); ?></span>
                    <?php endif; ?>
                </p>
                <p>
                    <strong><?php esc_html_e('Last Generated:', 'llms-txt-generator'); ?></strong>
                    <?php if ($last_generated) : ?>
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $last_generated)); ?>
                    <?php else : ?>
                        <?php esc_html_e('Never', 'llms-txt-generator'); ?>
                    <?php endif; ?>
                </p>
                <p>
                    <button id="llms-txt-generate-now" class="button button-primary" <?php disabled(!$is_writable); ?>>
                        <?php esc_html_e('Generate Now', 'llms-txt-generator'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('llms_txt_settings');
                do_settings_sections('llms-txt-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render general section
     *
     * @return void
     */
    public function render_general_section() {
        echo '<p>' . esc_html__('Configure which content should be included in the llms.txt file and associated Markdown exports.', 'llms-txt-generator') . '</p>';
    }

    /**
     * Render post types field
     *
     * @return void
     */
    public function render_post_types_field() {
        $settings = $this->generator->get_settings();
        $post_types = $settings['post_types'] ?? ['post', 'page'];
        
        // Get all public post types
        $public_post_types = get_post_types(['public' => true], 'objects');
        
        foreach ($public_post_types as $post_type) {
            $checked = in_array($post_type->name, $post_types, true) ? 'checked' : '';
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr(Generator::OPTION_NAME); ?>[post_types][]" 
                       value="<?php echo esc_attr($post_type->name); ?>" <?php echo $checked; ?>>
                <?php echo esc_html($post_type->labels->name); ?>
            </label><br>
            <?php
        }
    }

    /**
     * Render full content field
     *
     * @return void
     */
    public function render_full_content_field() {
        $settings = $this->generator->get_settings();
        $full_content = $settings['full_content'] ?? true;
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(Generator::OPTION_NAME); ?>[full_content]" 
                   value="1" <?php checked($full_content, true); ?>>
            <?php esc_html_e('Include full content (unchecked = excerpt only)', 'llms-txt-generator'); ?>
        </label>
        <?php
    }

    /**
     * Render days field
     *
     * @return void
     */
    public function render_days_field() {
        $settings = $this->generator->get_settings();
        $days = $settings['days_to_include'] ?? 365;
        ?>
        <input type="number" name="<?php echo esc_attr(Generator::OPTION_NAME); ?>[days_to_include]" 
               value="<?php echo esc_attr($days); ?>" min="0" step="1">
        <p class="description">
            <?php esc_html_e('Number of days of content to include (0 = all content)', 'llms-txt-generator'); ?>
        </p>
        <?php
    }

    /**
     * Render delete field
     *
     * @return void
     */
    public function render_delete_field() {
        $settings = $this->generator->get_settings();
        $delete = $settings['delete_on_uninstall'] ?? false;
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(Generator::OPTION_NAME); ?>[delete_on_uninstall]" 
                   value="1" <?php checked($delete, true); ?>>
            <?php esc_html_e('Delete llms.txt and llms-docs/ directory when plugin is uninstalled', 'llms-txt-generator'); ?>
        </label>
        <?php
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
        
        wp_enqueue_style(
            'llms-txt-admin',
            LLMS_TXT_GENERATOR_PLUGIN_URL . 'assets/admin.css',
            [],
            LLMS_TXT_GENERATOR_VERSION
        );
        
        wp_enqueue_script(
            'llms-txt-admin',
            LLMS_TXT_GENERATOR_PLUGIN_URL . 'assets/admin.js',
            ['jquery'],
            LLMS_TXT_GENERATOR_VERSION,
            true
        );
        
        wp_localize_script('llms-txt-admin', 'llmsTxtAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('llms_txt_generate_nonce'),
            'generating' => esc_html__('Generating...', 'llms-txt-generator'),
            'success' => esc_html__('LLMS.txt generated successfully!', 'llms-txt-generator'),
            'error' => esc_html__('Error generating LLMS.txt. Please check server permissions.', 'llms-txt-generator'),
        ]);
    }

    /**
     * AJAX handler for manual generation
     *
     * @return void
     */
    public function ajax_generate_now() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'llms_txt_generate_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed.', 'llms-txt-generator')]);
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have permission to perform this action.', 'llms-txt-generator')]);
        }
        
        // Generate files
        $result = $this->generator->generate_all();
        
        if ($result) {
            wp_send_json_success([
                'message' => esc_html__('LLMS.txt generated successfully!', 'llms-txt-generator'),
                'timestamp' => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), time()),
            ]);
        } else {
            wp_send_json_error([
                'message' => esc_html__('Error generating LLMS.txt. Please check server permissions.', 'llms-txt-generator'),
            ]);
        }
    }

    /**
     * Display admin notices
     *
     * @return void
     */
    public function admin_notices() {
        // Check if root directory is writable
        if (!$this->generator->is_root_writable() && isset($_GET['page']) && 'llms-txt-settings' === $_GET['page']) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php 
                    echo wp_kses(
                        sprintf(
                            /* translators: %s: WordPress root directory path */
                            __('LLMS.txt Generator cannot write to your WordPress root directory (%s). Please check file permissions.', 'llms-txt-generator'),
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