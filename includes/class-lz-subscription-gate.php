<?php
namespace LzBuilder;

final class LZ_Subscription_Gate {

    static public function is_um_active(): bool {
        static $cached = null;
        if ($cached === null) {
            $cached = function_exists('wu_has_product')
                   && function_exists('wu_get_current_site')
                   && function_exists('wu_register_limit_module')
                   && function_exists('wu_generate_upgrade_to_unlock_url')
                   && function_exists('wu_generate_upgrade_to_unlock_button');
        }
        return $cached;
    }

    static public function is_module_accessible(string $module_slug): bool {
        $module = LZ_Module_Registry::get_instance()->get_module($module_slug);
        if (!$module) {
            return false;
        }
        $required = $module->get_required_plan();
        return self::check_plan_access($required);
    }

    static public function is_template_accessible(int $template_post_id): bool {
        $required = get_post_meta($template_post_id, '_lz_required_plan', true);
        if (empty($required)) {
            return true;
        }
        return self::check_plan_access((array) $required);
    }

    static public function check_plan_access(?array $required_plans): bool {
        if (!self::is_um_active()) {
            return true;
        }
        if (empty($required_plans)) {
            return true;
        }
        foreach ($required_plans as $plan) {
            if (wu_has_product($plan, true)) {
                return true;
            }
        }
        return false;
    }

    static public function get_locked_modules_data(): array {
        $locked = [];
        $all_modules = LZ_Module_Registry::get_instance()->get_all_modules();

        foreach ($all_modules as $slug => $module) {
            $required = $module->get_required_plan();
            if (empty($required)) {
                continue;
            }
            if (!self::check_plan_access($required)) {
                $locked[] = [
                    'slug'           => $slug,
                    'name'           => $module->get_name(),
                    'description'    => $module->get_description(),
                    'upgrade_url'    => self::get_upgrade_url($required),
                    'upgrade_button' => self::get_upgrade_button_html($module->get_name()),
                ];
            }
        }
        return $locked;
    }

    static public function get_upgrade_url(array $required_plans): string {
        if (!self::is_um_active()) {
            return '';
        }
        return wu_generate_upgrade_to_unlock_url([
            'module' => 'lz_builder_modules',
            'type'   => implode(',', $required_plans),
        ]);
    }

    static public function get_upgrade_button_html(string $module_name): string {
        if (!self::is_um_active()) {
            return '';
        }
        return wu_generate_upgrade_to_unlock_button(
            sprintf(__('Unlock %s', 'lz-builder'), $module_name),
            ['module' => 'lz_builder_modules', 'type' => $module_name, 'classes' => 'lz-upgrade-btn']
        );
    }

    static public function register_limitation_modules(): void {
        if (!function_exists('wu_register_limit_module')) {
            return;
        }
        wu_register_limit_module('lz_builder_modules', '\\LzBuilder\\Limitations\\Limit_Builder_Modules');
        wu_register_limit_module('lz_builder_templates', '\\LzBuilder\\Limitations\\Limit_Builder_Templates');
    }

    static public function filter_available_modules(array $modules): array {
        return array_values(array_filter($modules, function ($node) {
            if (!is_array($node) || !isset($node['module'])) {
                return true;
            }
            return self::is_module_accessible($node['module']);
        }));
    }
}
