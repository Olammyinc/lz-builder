<?php
namespace LzBuilder;

final class LZ_Module_Registry {
    private static ?self $instance = null;
    private static array $modules = [];
    private static array $categories = [];

    private function __construct() {}

    private function __clone() {}

    public static function get_instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        $modules_dir = trailingslashit(dirname(__DIR__)) . 'includes/modules/';
        if (!is_dir($modules_dir)) {
            return;
        }

        $directories = glob(trailingslashit($modules_dir) . '*', GLOB_ONLYDIR);
        if (empty($directories)) {
            return;
        }

        foreach ($directories as $dir) {
            $slug = basename($dir);
            $file = trailingslashit($dir) . "class-{$slug}.php";

            if (!file_exists($file)) {
                continue;
            }

            require_once $file;

            $class_name = $this->resolve_class_name($slug);
            if (!class_exists($class_name) || !is_subclass_of($class_name, LZ_Module_Base::class)) {
                continue;
            }

            $module = new $class_name();
            $module->set_dir($dir);
            $this->register($module);
        }
    }

    public function register(LZ_Module_Base $module): void {
        $slug = $module->get_slug();
        self::$modules[$slug] = $module;

        $category = $module->get_category();
        if (!in_array($category, self::$categories, true)) {
            self::$categories[] = $category;
        }
    }

    public function get_module(string $slug): ?LZ_Module_Base {
        return self::$modules[$slug] ?? null;
    }

    public function get_all_modules(): array {
        return self::$modules;
    }

    public function get_available_modules(): array {
        $available = [];

        foreach (self::$modules as $slug => $module) {
            $required_plan = $module->get_required_plan();

            if (null === $required_plan) {
                $available[$slug] = $module;
                continue;
            }

            if (function_exists('wu_has_product')) {
                foreach ($required_plan as $plan_slug) {
                    if (wu_has_product($plan_slug)) {
                        $available[$slug] = $module;
                        break;
                    }
                }
            }
        }

        return $available;
    }

    public function get_modules_by_category(string $category): array {
        return array_filter(self::$modules, function (LZ_Module_Base $module) use ($category) {
            return $module->get_category() === $category;
        });
    }

    public function get_categories(): array {
        return self::$categories;
    }

    public function get_modules_for_panel(): array {
        $grouped = [];

        foreach ($this->get_available_modules() as $slug => $module) {
            $category = $module->get_category();

            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'name' => $this->get_category_label($category),
                    'slug' => $category,
                    'modules' => [],
                ];
            }

            $grouped[$category]['modules'][] = [
                'slug' => $module->get_slug(),
                'name' => $module->get_name(),
                'description' => $module->get_description(),
                'icon' => $module->get_icon(),
                'category' => $module->get_category(),
            ];
        }

        return array_values($grouped);
    }

    private function resolve_class_name(string $slug): string {
        $parts = explode('-', $slug);
        $parts = array_map('ucfirst', $parts);
        return '\\LzBuilder\\Modules\\' . implode('_', $parts);
    }

    private function get_category_label(string $category): string {
        $labels = [
            'content' => __('Content', 'lz-builder'),
            'layout' => __('Layout', 'lz-builder'),
            'media' => __('Media', 'lz-builder'),
            'advanced' => __('Advanced', 'lz-builder'),
            'interactive' => __('Interactive', 'lz-builder'),
        ];

        return $labels[$category] ?? ucfirst($category);
    }
}
