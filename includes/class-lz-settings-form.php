<?php
namespace LzBuilder;

final class LZ_Settings_Form {
    static private array $forms = [];
    static private array $defaults_cache = [];

    static public function register(string $form_id, array $form): void {
        self::$forms[$form_id] = $form;
    }

    static public function get_form(string $form_id): ?array {
        return self::$forms[$form_id] ?? null;
    }

    static public function get_fields(string $form_id): array {
        $form = self::get_form($form_id);
        if (!$form) return [];
        $fields = [];
        foreach ($form as $tab) {
            foreach ($tab['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $key => $field) {
                    $fields[$key] = $field;
                }
            }
        }
        return $fields;
    }

    static public function get_defaults(string $form_id): array {
        if (isset(self::$defaults_cache[$form_id])) {
            return self::$defaults_cache[$form_id];
        }
        $form = self::get_form($form_id);
        if (!$form) return [];
        $defaults = [];
        foreach ($form as $tab) {
            foreach ($tab['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $key => $field) {
                    if (array_key_exists('default', $field)) {
                        $defaults[$key] = $field['default'];
                    }
                }
            }
        }
        self::$defaults_cache[$form_id] = $defaults;
        return $defaults;
    }

    static public function render_field(string $field_type, string $field_key, $value, array $field_config): string {
        $template = LZ_BUILDER_DIR . 'includes/settings/fields/field-' . $field_type . '.php';
        if (!file_exists($template)) {
            return '';
        }
        ob_start();
        $field_value = $value;
        $field = $field_config;
        include $template;
        return ob_get_clean();
    }

    static public function sanitize_settings(array $settings, string $form_id): array {
        $fields = self::get_fields($form_id);
        $sanitized = [];
        foreach ($settings as $key => $value) {
            $type = $fields[$key]['type'] ?? 'text';
            $sanitized[$key] = self::sanitize_value($value, $type);
        }
        return $sanitized;
    }

	static private function sanitize_value($value, string $type) {
		switch ($type) {
			case 'text':
			case 'textarea':
			case 'editor':
			case 'raw':
				return wp_kses_post($value);
			case 'color':
				return sanitize_hex_color($value);
			case 'number':
			case 'unit':
				return floatval($value);
			case 'select':
			case 'button-group':
				return sanitize_text_field($value);
			case 'photo':
				return intval($value);
			case 'code':
				return wp_kses_post($value);
			case 'checkbox':
				return (bool) $value;
			case 'multiple-photos':
			case 'ordering':
				return array_map('intval', (array) $value);
		case 'spacing':
		case 'dimension':
		case 'border':
		case 'typography':
		case 'link':
		case 'gradient':
		case 'shadow':
		case 'form':
		case 'video':
			if (!is_array($value)) return (array) $value;
			return array_map(function ($v) {
				return is_array($v) ? array_map('sanitize_text_field', $v) : sanitize_text_field((string) $v);
			}, $value);
			default:
				return sanitize_text_field($value);
		}
	}
}
