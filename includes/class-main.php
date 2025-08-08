<?php
/**
 * Main plugin class
 *
 * @package LLMagnet_AI_SEO_Optimizer
 */

namespace LLMagnet_AI_SEO_Optimizer;

/**
 * Main plugin class
 */
class Main {
    /**
     * Admin instance
     *
     * @var Admin
     */
    private $admin;

    /**
     * Generator instance
     *
     * @var Generator
     */
    private $generator;

    /**
     * Cron instance
     *
     * @var Cron
     */
    private $cron;

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init() {
        // Initialize components
        $this->init_components();

        // Setup hooks
        $this->setup_hooks();
    }



    /**
     * Initialize plugin components
     *
     * @return void
     */
    private function init_components() {
        // Initialize generator
        $this->generator = new Generator();
        
        // Initialize admin
        if (is_admin()) {
            $this->admin = new Admin($this->generator);
        }

        // Initialize cron
        $this->cron = new Cron($this->generator);
    }

    /**
     * Setup plugin hooks
     *
     * @return void
     */
    private function setup_hooks() {
        // Post save hook to regenerate files
        add_action('save_post', [$this->generator, 'maybe_regenerate'], 10, 2);
        
        // Post update hook to regenerate files
        add_action('post_updated', [$this->generator, 'maybe_regenerate'], 10, 2);
        
        // Register WP-CLI commands if available
        if (defined('WP_CLI') && WP_CLI) {
            $this->register_cli_commands();
        }
    }

    /**
     * Register WP-CLI commands
     *
     * @return void
     */
    private function register_cli_commands() {
        \WP_CLI::add_command('llmagnet-ai-seo', new CLI($this->generator));
    }
} 