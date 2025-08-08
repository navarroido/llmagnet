<?php
/**
 * Cron class
 *
 * @package LLMagnet_AI_SEO_Optimizer
 */

namespace LLMagnet_AI_SEO_Optimizer;

/**
 * Cron class for scheduled tasks
 */
class Cron {
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
     * Initialize cron functionality
     *
     * @return void
     */
    public function init() {
        // Add cron hook for daily generation
        add_action('llmagnet_ai_seo_daily_event', [$this->generator, 'generate_all']);
    }

    /**
     * Schedule cron event
     *
     * @return void
     */
    public static function schedule_event() {
        if (!wp_next_scheduled('llmagnet_ai_seo_daily_event')) {
            wp_schedule_event(time(), 'daily', 'llmagnet_ai_seo_daily_event');
        }
    }

    /**
     * Clear scheduled event
     *
     * @return void
     */
    public static function clear_scheduled_event() {
        wp_clear_scheduled_hook('llmagnet_ai_seo_daily_event');
    }
} 