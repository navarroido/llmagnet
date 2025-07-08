<?php
/**
 * Test file to diagnose plugin activation issues
 */

// Output plugin directory and file information
echo "Plugin Directory: " . dirname(__FILE__) . "\n";
echo "Plugin File: " . __FILE__ . "\n";

// Check if all required files exist
$required_files = [
    dirname(__FILE__) . '/includes/class-main.php',
    dirname(__FILE__) . '/includes/class-generator.php',
    dirname(__FILE__) . '/includes/class-admin.php',
    dirname(__FILE__) . '/includes/class-cron.php',
    dirname(__FILE__) . '/includes/class-cli.php',
];

foreach ($required_files as $file) {
    echo "File " . basename($file) . ": " . (file_exists($file) ? "Exists" : "Missing") . "\n";
}

// Try to include each file and catch any errors
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "Including " . basename($file) . "... ";
        try {
            include_once $file;
            echo "Success\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

// Check if classes exist
$required_classes = [
    'LLMS_Txt_Generator\Main',
    'LLMS_Txt_Generator\Generator',
    'LLMS_Txt_Generator\Admin',
    'LLMS_Txt_Generator\Cron',
    'LLMS_Txt_Generator\CLI',
];

foreach ($required_classes as $class) {
    echo "Class " . $class . ": " . (class_exists($class) ? "Exists" : "Not found") . "\n";
} 