# LLMS.txt Generator for WordPress

A WordPress plugin that automatically creates and maintains an `llms.txt` file and associated Markdown files for large language model (LLM) crawlers.

## Description

LLMS.txt Generator helps your WordPress site communicate better with large language models by:

1. Creating an `llms.txt` file in your site root (similar to robots.txt)
2. Generating clean Markdown exports of your content
3. Keeping everything up-to-date automatically

This plugin implements the emerging `llms.txt` standard to help LLMs better understand and represent your website content.

## Features

- **Automatic File Creation**: Creates `/llms.txt` in your WordPress root
- **Markdown Exports**: Generates clean Markdown files of your content in `/llms-docs/`
- **Configurable Content**: Choose which post types to include and whether to use full content or excerpts
- **Automatic Updates**: Files are regenerated daily and whenever content is updated
- **WP-CLI Support**: Manage generation via command line with `wp llms-txt regenerate`

## Installation

1. Upload the `llms-txt-generator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > LLMS.txt to configure which content should be included

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Write access to WordPress root directory

## Configuration

After installation, go to **Settings > LLMS.txt** to configure:

- Which post types to include (Posts, Pages, custom post types)
- Whether to include full content or just excerpts
- How far back in time to include content (e.g., last 365 days)
- Whether to delete files on plugin uninstall

## WP-CLI Commands

This plugin supports WP-CLI for command-line management:

```bash
# Regenerate llms.txt and Markdown files
wp llms-txt regenerate

# Force regeneration even if recently generated
wp llms-txt regenerate --force

# Show current settings
wp llms-txt settings
```

## For Developers

### Hooks and Filters

The plugin provides several hooks for developers to extend its functionality:

#### Actions

- `llms_txt_before_generate` - Fires before generating llms.txt and Markdown files
- `llms_txt_after_generate` - Fires after generating llms.txt and Markdown files
- `llms_txt_before_markdown_export` - Fires before exporting a post as Markdown

#### Filters

- `llms_txt_content` - Filter the content of the llms.txt file
- `llms_txt_post_content` - Filter the content of a post before converting to Markdown
- `llms_txt_markdown_content` - Filter the final Markdown content for a post
- `llms_txt_included_post_types` - Filter which post types are included in exports

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by WordPress Developer 