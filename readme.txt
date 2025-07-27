=== LLMS.txt Generators ===
Contributors: navarroido, benwpco
Tags: llm, ai, crawling, markdown, seo
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically creates and maintains an llms.txt file and associated Markdown files for large language model (LLM) crawlers.

== Description ==

LLMS.txt Generators automatically creates and keeps up-to-date an `llms.txt` file in your site's root directory, along with clean Markdown exports of your content. This helps large language models (LLMs) better understand your website's content structure.

= Key Features =

* **Automatic File Creation**: Creates `/llms.txt` in your WordPress root (same level as robots.txt)
* **Markdown Exports**: Generates clean Markdown files of your content in `/llms-docs/`
* **Configurable Content**: Choose which post types to include and whether to use full content or excerpts
* **Automatic Updates**: Files are regenerated daily and whenever content is updated
* **WP-CLI Support**: Manage generation via command line with `wp llms-txt regenerate`

= How It Works =

1. The plugin creates an `llms.txt` file in your site's root directory that provides an overview of your site structure
2. It exports selected content as clean Markdown files in a `/llms-docs/` directory
3. The `llms.txt` file links to these Markdown exports for LLM crawlers to access

= Why Use This Plugin? =

As large language models become more prevalent, having a standardized way for them to understand your site's content becomes increasingly important. This plugin implements the emerging `llms.txt` standard to help these models better represent your content.

== Installation ==

1. Upload the `llmagnet-generate-llm-txt-for-wp` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > LLMS.txt to configure which content should be included

== Frequently Asked Questions ==

= What is llms.txt? =

`llms.txt` is an emerging standard for helping large language models (LLMs) better understand website content. Similar to `robots.txt` for search engines, it provides guidance to LLM crawlers about your site's structure and content.

= Will this affect my site's performance? =

No, the plugin is designed to be lightweight. File generation happens in the background and is rate-limited to prevent excessive processing.

= Can I customize which content is included? =

Yes, you can select which post types to include, whether to use full content or excerpts, and how far back in time to include content.

= Do I need to update the files manually? =

No, the plugin automatically updates the files daily and whenever content is published or updated.

= Can I generate the files on demand? =

Yes, you can use the "Generate Now" button on the settings page or the WP-CLI command `wp llms-txt regenerate`.

== Screenshots ==

1. Settings page with configuration options
2. Example of generated llms.txt file
3. Example of Markdown export

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Hooks and Filters ==

The plugin provides several hooks for developers to extend its functionality:

= Actions =

* `llms_txt_before_generate` - Fires before generating llms.txt and Markdown files
* `llms_txt_after_generate` - Fires after generating llms.txt and Markdown files
* `llms_txt_before_markdown_export` - Fires before exporting a post as Markdown

= Filters =

* `llms_txt_content` - Filter the content of the llms.txt file
* `llms_txt_post_content` - Filter the content of a post before converting to Markdown
* `llms_txt_markdown_content` - Filter the final Markdown content for a post
* `llms_txt_included_post_types` - Filter which post types are included in exports 