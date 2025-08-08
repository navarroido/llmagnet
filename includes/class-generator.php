<?php
/**
 * Generator class
 *
 * @package LLMagnet_AI_SEO_Optimizer
 */

namespace LLMagnet_AI_SEO_Optimizer;

/**
 * Generator class for creating and updating llms.txt and Markdown files
 */
class Generator {
    /**
     * Option name for storing settings
     *
     * @var string
     */
    const OPTION_NAME = 'llmagnet_ai_seo_optimizer_settings';

    /**
     * Transient name for tracking last generation time
     *
     * @var string
     */
    const TRANSIENT_NAME = 'llmagnet_ai_seo_optimizer_last_run';

    /**
     * Default settings
     *
     * @var array
     */
    private $default_settings = [
        'post_types' => ['post', 'page'],
        'full_content' => true,
        'days_to_include' => 365,
        'delete_on_uninstall' => false,
    ];

    /**
     * Plugin activation
     *
     * @return void
     */
    public static function activate() {
        // Schedule cron job
        if (!wp_next_scheduled('llmagnet_ai_seo_daily_event')) {
            wp_schedule_event(time(), 'daily', 'llmagnet_ai_seo_daily_event');
        }

        // Create initial files
        $generator = new self();
        $generator->generate_all();

        // Store default settings if not already set
        if (!get_option(self::OPTION_NAME)) {
            update_option(self::OPTION_NAME, $generator->default_settings);
        }
    }

    /**
     * Plugin deactivation
     *
     * @return void
     */
    public static function deactivate() {
        // Clear scheduled hook
        wp_clear_scheduled_hook('llmagnet_ai_seo_daily_event');
    }

    /**
     * Get plugin settings
     *
     * @return array
     */
    public function get_settings() {
        return get_option(self::OPTION_NAME, $this->default_settings);
    }

    /**
     * Update plugin settings
     *
     * @param array $settings New settings
     * @return bool
     */
    public function update_settings($settings) {
        return update_option(self::OPTION_NAME, $settings);
    }

    /**
     * Check if we should regenerate files
     *
     * @param int     $post_id Post ID
     * @param WP_Post $post    Post object
     * @return void
     */
    public function maybe_regenerate($post_id, $post) {
        // Skip if this is an autosave or revision
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        // Skip if post is not published
        if ('publish' !== $post->post_status) {
            return;
        }

        // Check if post type is included in settings
        $settings = $this->get_settings();
        if (!in_array($post->post_type, $settings['post_types'], true)) {
            return;
        }

        // Regenerating files for post
        
        // Regenerate files immediately without checking the cooldown period
        $this->generate_all();
    }

    /**
     * Generate all files
     *
     * @return bool
     */
    public function generate_all() {
        // Starting file generation
        
        // Set transient to track last generation time (for informational purposes only)
        set_transient(self::TRANSIENT_NAME, time(), DAY_IN_SECONDS);

        // Initialize WordPress filesystem
        global $wp_filesystem;
        if (!$this->init_filesystem()) {
            return false;
        }

        // Create llms-docs directory if it doesn't exist
        $this->create_llms_docs_directory();

        // Generate llms.txt file
        $llms_txt_content = $this->generate_llms_txt_content();
        $llms_txt_path = $this->get_root_path() . 'llms.txt';
        $result = $wp_filesystem->put_contents($llms_txt_path, $llms_txt_content, FS_CHMOD_FILE);
        
        if (false === $result) {
            return false;
        }

        // Generate Markdown files
        $this->generate_markdown_files();
        
        return true;
    }

    /**
     * Initialize WordPress filesystem
     *
     * @return bool
     */
    private function init_filesystem() {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Initialize the WP filesystem
        if (false === ($credentials = request_filesystem_credentials('', '', false, false, null))) {
            return false;
        }

        if (!WP_Filesystem($credentials)) {
            request_filesystem_credentials('', '', true, false, null);
            return false;
        }

        return true;
    }

    /**
     * Get WordPress root path
     *
     * @return string
     */
    public function get_root_path() {
        return trailingslashit(ABSPATH);
    }

    /**
     * Create llms-docs directory and .htaccess file
     *
     * @return bool
     */
    private function create_llms_docs_directory() {
        global $wp_filesystem;

        $docs_dir = $this->get_root_path() . 'llms-docs';
        
        // Create directory if it doesn't exist
        if (!$wp_filesystem->exists($docs_dir)) {
            $wp_filesystem->mkdir($docs_dir, FS_CHMOD_DIR);
        }

        // Create .htaccess file to allow crawling
        $htaccess_content = "# Allow LLM crawlers to access this directory\n";
        $htaccess_content .= "<IfModule mod_rewrite.c>\n";
        $htaccess_content .= "RewriteEngine On\n";
        $htaccess_content .= "RewriteRule .* - [L]\n";
        $htaccess_content .= "</IfModule>\n";

        $wp_filesystem->put_contents($docs_dir . '/.htaccess', $htaccess_content, FS_CHMOD_FILE);

        return true;
    }

    /**
     * Generate llms.txt content
     *
     * @return string
     */
    private function generate_llms_txt_content() {
        $site_title = get_bloginfo('name');
        $site_tagline = get_bloginfo('description');
        
        $content = "# What this site is about\n";
        $content .= "*Title:* {$site_title}\n";
        $content .= "*Tagline:* {$site_tagline}\n\n";
        
        // Add key sections
        $content .= "# Key sections\n";
        
        // Add home page
        $content .= "- /   — " . get_bloginfo('name') . "\n";
        
        // Add blog page if exists
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            $blog_page = get_post($blog_page_id);
            $content .= "- " . str_replace(home_url(), '', get_permalink($blog_page_id)) . "   — " . $blog_page->post_title . "\n";
        }
        
        // Get settings to determine which post types to include
        $settings = $this->get_settings();
        $post_types = $settings['post_types'];
        
        // Add important pages and posts from all selected post types
        $added_items = 0;
        $max_items = 15; // Increase the limit to accommodate more content types
        
        // First add top-level pages (for backward compatibility)
        $pages = get_pages([
            'sort_column' => 'menu_order',
            'parent' => 0,
        ]);
        
        foreach ($pages as $page) {
            if ($added_items >= $max_items) {
                break;
            }
            
            if ($page->ID != $blog_page_id && $page->post_parent == 0) {
                $content .= "- " . str_replace(home_url(), '', get_permalink($page->ID)) . "   — " . $page->post_title . "\n";
                $added_items++;
            }
        }
        
        // Now add posts from each selected post type (except 'page' which we already handled)
        foreach ($post_types as $post_type) {
            if ($post_type === 'page') {
                continue; // Skip pages as we've already added them
            }
            
            // Get post type object to display its label
            $post_type_obj = get_post_type_object($post_type);
            if (!$post_type_obj) {
                continue;
            }
            
            // Add post type header
            $content .= "\n## " . $post_type_obj->labels->name . "\n";
            
            // Get recent posts of this type
            $posts = get_posts([
                'post_type' => $post_type,
                'posts_per_page' => 5,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            
            if (empty($posts)) {
                $content .= "- No published content\n";
                continue;
            }
            
            foreach ($posts as $post) {
                if ($added_items >= $max_items) {
                    break 2; // Break out of both loops if we've reached the limit
                }
                
                $content .= "- " . str_replace(home_url(), '', get_permalink($post->ID)) . "   — " . $post->post_title . "\n";
                $added_items++;
            }
        }
        
        $content .= "\n# Markdown exports (clean text)\n";
        
        // Get exported Markdown files
        $markdown_files = $this->get_markdown_files();
        foreach ($markdown_files as $file) {
            $content .= "- " . $file . "\n";
        }
        
        return $content;
    }

    /**
     * Get list of Markdown files
     *
     * @return array
     */
    private function get_markdown_files() {
        $files = [];
        $site_url = trailingslashit(get_site_url());
        $docs_url = $site_url . 'llms-docs/';
        
        // Get posts to export
        $posts = $this->get_posts_to_export();
        
        foreach ($posts as $post) {
            $slug = sanitize_title($post->post_name);
            $files[] = $docs_url . $slug . '.md';
        }
        
        return $files;
    }

    /**
     * Get posts to export as Markdown
     *
     * @return array
     */
    public function get_posts_to_export() {
        $settings = $this->get_settings();
        
        // Ensure attachments are not included
        $post_types = $settings['post_types'];
        if (is_array($post_types)) {
            $post_types = array_diff($post_types, ['attachment']);
        }
        
        $args = [
            'post_type' => $post_types,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ];
        
        // Add date filter if set
        if (!empty($settings['days_to_include']) && $settings['days_to_include'] > 0) {
            $args['date_query'] = [
                'after' => gmdate('Y-m-d', strtotime('-' . $settings['days_to_include'] . ' days')),
            ];
        }
        
        return get_posts($args);
    }

    /**
     * Generate Markdown files for posts
     *
     * @return bool
     */
    private function generate_markdown_files() {
        global $wp_filesystem;
        
        // Get posts to export
        $posts = $this->get_posts_to_export();
        $settings = $this->get_settings();
        $docs_dir = $this->get_root_path() . 'llms-docs/';
        
        // Clear existing files
        $existing_files = glob($docs_dir . '*.md');
        foreach ($existing_files as $file) {
            $wp_filesystem->delete($file);
        }
        
        // Generate new files
        foreach ($posts as $post) {
            $slug = sanitize_title($post->post_name);
            $filename = $docs_dir . $slug . '.md';
            
            // Generate Markdown content
            $content = "# {$post->post_title}\n\n";
            
            // Add post meta
            $content .= "*Published:* " . get_the_date('F j, Y', $post->ID) . "\n";
            $content .= "*URL:* " . get_permalink($post->ID) . "\n\n";
            
            // Add content
            if ($settings['full_content']) {
                $post_content = $post->post_content;
            } else {
                $post_content = $post->post_excerpt ?: wp_trim_words($post->post_content, 55, '...');
            }
            
            // Convert to Markdown
            $content .= $this->html_to_markdown($post_content);
            
            // Write to file
            $wp_filesystem->put_contents($filename, $content, FS_CHMOD_FILE);
        }
        
        return true;
    }

    /**
     * Convert HTML to Markdown
     *
     * @param string $content HTML content
     * @return string
     */
    private function html_to_markdown($content) {
        // Check if Parsedown exists
        if (class_exists('Parsedown')) {
            // We can't use Parsedown directly for HTML to Markdown conversion
            // This is a simplified conversion
        }
        
        // Simple HTML to Markdown conversion
        $content = $this->simple_html_to_markdown($content);
        
        return $content;
    }

    /**
     * Simple HTML to Markdown conversion
     *
     * @param string $content HTML content
     * @return string
     */
    private function simple_html_to_markdown($content) {
        // Process shortcodes
        $content = do_shortcode($content);
        
        // Strip HTML comments
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);
        
        // Convert headings
        $content = preg_replace('/<h1[^>]*>(.*?)<\/h1>/i', '# $1', $content);
        $content = preg_replace('/<h2[^>]*>(.*?)<\/h2>/i', '## $1', $content);
        $content = preg_replace('/<h3[^>]*>(.*?)<\/h3>/i', '### $1', $content);
        $content = preg_replace('/<h4[^>]*>(.*?)<\/h4>/i', '#### $1', $content);
        $content = preg_replace('/<h5[^>]*>(.*?)<\/h5>/i', '##### $1', $content);
        $content = preg_replace('/<h6[^>]*>(.*?)<\/h6>/i', '###### $1', $content);
        
        // Convert bold and italic
        $content = preg_replace('/<strong[^>]*>(.*?)<\/strong>/i', '**$1**', $content);
        $content = preg_replace('/<b[^>]*>(.*?)<\/b>/i', '**$1**', $content);
        $content = preg_replace('/<em[^>]*>(.*?)<\/em>/i', '*$1*', $content);
        $content = preg_replace('/<i[^>]*>(.*?)<\/i>/i', '*$1*', $content);
        
        // Convert links
        $content = preg_replace('/<a[^>]*href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/i', '[$2]($1)', $content);
        
        // Convert lists
        $content = preg_replace('/<ul[^>]*>(.*?)<\/ul>/is', '$1', $content);
        $content = preg_replace('/<ol[^>]*>(.*?)<\/ol>/is', '$1', $content);
        $content = preg_replace('/<li[^>]*>(.*?)<\/li>/i', '- $1', $content);
        
        // Convert paragraphs and line breaks
        $content = preg_replace('/<p[^>]*>(.*?)<\/p>/i', "$1\n\n", $content);
        $content = preg_replace('/<br[^>]*>/i', "\n", $content);
        
        // Strip remaining HTML tags
        $content = wp_strip_all_tags($content);
        
        // Clean up whitespace
        $content = preg_replace('/\n\s+\n/', "\n\n", $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        return trim($content);
    }

    /**
     * Check if WordPress root directory is writable
     *
     * @return bool
     */
    public function is_root_writable() {
        global $wp_filesystem;
        
        if (!$this->init_filesystem()) {
            return false;
        }
        
        return $wp_filesystem->is_writable($this->get_root_path());
    }

    /**
     * Get last generation timestamp
     *
     * @return int|false
     */
    public function get_last_generated_time() {
        return get_transient(self::TRANSIENT_NAME);
    }
} 