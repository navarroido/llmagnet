<?php
/**
 * CLI class
 *
 * @package LLMS_Txt_Generator
 */

namespace LLMS_Txt_Generator;

/**
 * CLI class for WP-CLI commands
 */
class CLI {
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
    }

    /**
     * Regenerate llms.txt and Markdown files
     *
     * ## OPTIONS
     *
     * [--force]
     * : Force regeneration even if recently generated
     *
     * ## EXAMPLES
     *
     *     wp llms-txt regenerate
     *     wp llms-txt regenerate --force
     *
     * @param array $args Command arguments
     * @param array $assoc_args Command associative arguments
     * @return void
     */
    public function regenerate($args, $assoc_args) {
        // Check if root is writable
        if (!$this->generator->is_root_writable()) {
            \WP_CLI::error('WordPress root directory is not writable. Cannot generate LLMS.txt.');
            return;
        }
        
        \WP_CLI::log('Generating LLMS.txt and Markdown files...');
        
        $result = $this->generator->generate_all();
        
        if ($result) {
            \WP_CLI::success('LLMS.txt and Markdown files generated successfully.');
        } else {
            \WP_CLI::error('Failed to generate LLMS.txt and Markdown files.');
        }
    }

    /**
     * Show current settings
     *
     * ## EXAMPLES
     *
     *     wp llms-txt settings
     *
     * @param array $args Command arguments
     * @param array $assoc_args Command associative arguments
     * @return void
     */
    public function settings($args, $assoc_args) {
        $settings = $this->generator->get_settings();
        
        \WP_CLI::log('LLMS.txt Generator Settings:');
        \WP_CLI::log('');
        
        // Post types
        \WP_CLI::log('Content Types:');
        foreach ($settings['post_types'] as $post_type) {
            \WP_CLI::log(' - ' . $post_type);
        }
        \WP_CLI::log('');
        
        // Full content
        \WP_CLI::log('Full Content: ' . ($settings['full_content'] ? 'Yes' : 'No (excerpt only)'));
        
        // Days to include
        \WP_CLI::log('Days to Include: ' . ($settings['days_to_include'] > 0 ? $settings['days_to_include'] : 'All'));
        
        // Delete on uninstall
        \WP_CLI::log('Delete on Uninstall: ' . ($settings['delete_on_uninstall'] ? 'Yes' : 'No'));
        
        // Last generated
        $last_generated = $this->generator->get_last_generated_time();
        \WP_CLI::log('Last Generated: ' . ($last_generated ? date('Y-m-d H:i:s', $last_generated) : 'Never'));
    }
} 