<?php
/**
 * Plugin Name: Lz Builder
 * Plugin URI: https://lzplugins.com
 * Description: A powerful frontend drag-and-drop page builder with subscription-based module gating.
 * Version: 1.8.3
 * Author: Ibrahim Olammy
 * Text Domain: lz-builder
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('LZ_BUILDER_VERSION', '1.8.3');
define('LZ_BUILDER_FILE', __FILE__);
define('LZ_BUILDER_DIR', plugin_dir_path(__FILE__));
define('LZ_BUILDER_URL', plugin_dir_url(__FILE__));

try {
    require_once LZ_BUILDER_DIR . 'includes/class-lz-loader.php';
    \LzBuilder\LZ_Loader::init();
    error_log('[Lz Builder] Plugin init complete');
} catch (\Throwable $e) {
    error_log('[Lz Builder] Init FAILED: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    throw $e;
}
