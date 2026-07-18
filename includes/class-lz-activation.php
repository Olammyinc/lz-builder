<?php
namespace LzBuilder;

final class LZ_Activation {

    static public function activate(): void {
        try {
            error_log('[Lz Builder] Activation: step 1 cache dir');
            self::create_cache_dir();
            error_log('[Lz Builder] Activation: step 2 options');
            self::set_default_options();
            error_log('[Lz Builder] Activation: step 3 rewrite rules');
            self::flush_rewrite_rules();
            error_log('[Lz Builder] Activation: complete');
        } catch (\Throwable $e) {
            error_log('[Lz Builder] Activation FAILED: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    static public function deactivate(): void {
        self::clear_cache();
    }

    static public function get_cache_dir(): string {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . 'lz-builder/cache/';
    }

    static public function get_cache_url(): string {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['baseurl']) . 'lz-builder/cache/';
    }

    static private function create_cache_dir(): void {
        $cache_dir = self::get_cache_dir();
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
        $htaccess_file = $cache_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Deny from all\n");
        }
        $index_file = $cache_dir . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, "<?php // Silence is golden.\n");
        }
    }

    static private function set_default_options(): void {
        if (false === get_option('lz_builder_post_types')) {
            update_option('lz_builder_post_types', ['page', 'post']);
        }
        if (false === get_option('lz_tablet_breakpoint')) {
            update_option('lz_tablet_breakpoint', 768);
        }
        if (false === get_option('lz_phone_breakpoint')) {
            update_option('lz_phone_breakpoint', 480);
        }
        if (false === get_option('lz_builder_version')) {
            update_option('lz_builder_version', LZ_BUILDER_VERSION);
        }
    }

    static private function flush_rewrite_rules(): void {
        self::register_template_post_type();
        flush_rewrite_rules();
    }

    static private function register_template_post_type(): void {
        if (!post_type_exists('lz_template')) {
            LZ_Template_CPT::register_post_type();
        }
    }

    static private function clear_cache(): void {
        $cache_dir = self::get_cache_dir();
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            if ($files) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
    }
}
