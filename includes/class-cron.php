<?php
/**
 * Cron class
 *
 * @package LLMS_Txt_Generator
 */

namespace LLMS_Txt_Generator;

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
        add_action('llms_txt_daily_event', [$this->generator, 'generate_all']);
    }

    /**
     * Schedule cron event
     *
     * @return void
     */
    public static function schedule_event() {
        if (!wp_next_scheduled('llms_txt_daily_event')) {
            wp_schedule_event(time(), 'daily', 'llms_txt_daily_event');
        }
    }

    /**
     * Clear scheduled event
     *
     * @return void
     */
    public static function clear_scheduled_event() {
        wp_clear_scheduled_hook('llms_txt_daily_event');
    }
} 