<?php
namespace LzBuilder;

final class LZ_Loader {
    static public function init(): void {
        self::register_autoloader();
        self::load_files();
        LZ_Builder::init();
    }

    static private function register_autoloader(): void {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    static private function autoload(string $class): void {
        $prefix = 'LzBuilder\\';
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            return;
        }
        $relative_class = substr($class, strlen($prefix));
        $map = [
            'Settings\\'    => 'includes/settings/',
            'Limitations\\' => 'includes/limitations/',
        ];
        foreach ($map as $ns => $dir) {
            if (strncmp($relative_class, $ns, strlen($ns)) === 0) {
                $file = LZ_BUILDER_DIR . $dir . 'class-' . strtolower(str_replace(['\\', '_'], ['/', '-'], substr($relative_class, strlen($ns)))) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
        // Modules: look up in includes/modules/{slug}/class-{slug}.php
        if (strncmp($relative_class, 'Modules\\', 8) === 0) {
            $module_class = substr($relative_class, 8);
            $parts = explode('\\', $module_class);
            $class_name = end($parts);
            $slug = strtolower(str_replace('_', '-', $class_name));
            $file = LZ_BUILDER_DIR . 'includes/modules/' . $slug . '/class-' . $slug . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
        $parts = explode('\\', $relative_class);
        $class_name = end($parts);
        $file = LZ_BUILDER_DIR . 'includes/class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    static private function load_files(): void {
        require_once LZ_BUILDER_DIR . 'includes/class-lz-module-base.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-module-registry.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-page-data.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-css-accumulator.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-subscription-gate.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-ajax-handlers.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-settings-form.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-template-cpt.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-admin.php';
        require_once LZ_BUILDER_DIR . 'includes/class-lz-activation.php';
        require_once LZ_BUILDER_DIR . 'includes/compatibility.php';
    }
}
