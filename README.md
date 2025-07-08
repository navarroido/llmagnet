# LLMS.txt Generator WordPress Plugin with React UI

This WordPress plugin generates a `llms.txt` file and associated Markdown files for LLM crawlers. The admin interface has been built with React and Shadcn UI for a modern user experience.

## Features

- Generate `llms.txt` file in WordPress root directory
- Create Markdown exports of your content
- Modern React-based admin interface
- Configure which post types to include
- Set time period for content inclusion
- Option to include full content or excerpts only
- Automatic regeneration when content is updated

## Development

This plugin uses a modern React frontend with Vite, TypeScript, and Shadcn UI.

### Requirements

- Node.js 16+
- npm or yarn
- WordPress 5.8+
- PHP 7.4+

### Development Setup

1. Clone the repository to your WordPress plugins directory:
   ```
   cd wp-content/plugins/
   git clone https://github.com/yourusername/llms-txt-generator.git
   ```

2. Install dependencies:
   ```
   cd llms-txt-generator
   npm install
   ```

3. Start the development server:
   ```
   npm run dev
   ```

4. Set the development mode constant in the main plugin file:
   ```php
   define('LLMS_TXT_DEV_MODE', true);
   ```

5. Activate the plugin in WordPress admin

### Building for Production

1. Build the React app:
   ```
   npm run build
   ```

2. Make sure to set the development mode constant to false:
   ```php
   define('LLMS_TXT_DEV_MODE', false);
   ```

## Structure

- `includes/` - PHP classes for the plugin functionality
- `assets/` - CSS, JS, and built React files
- `src/` - React source code
  - `components/` - React components
  - `lib/` - Utility functions

## License

GPL v2 or later 