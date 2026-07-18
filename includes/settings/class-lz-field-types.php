<?php
namespace LzBuilder\Settings;

final class LZ_Field_Types {
    static private array $types = [];

    static public function register(string $type, array $config): void {
        self::$types[$type] = $config;
    }

    static public function get(string $type): ?array {
        return self::$types[$type] ?? null;
    }

    static public function get_all(): array {
        return self::$types;
    }

    static public function init(): void {
        $types = [
            'text' => ['label' => 'Text', 'category' => 'basic'],
            'textarea' => ['label' => 'Textarea', 'category' => 'basic'],
            'color' => ['label' => 'Color Picker', 'category' => 'basic'],
            'select' => ['label' => 'Select', 'category' => 'basic'],
            'photo' => ['label' => 'Photo', 'category' => 'media'],
            'multiple-photos' => ['label' => 'Multiple Photos', 'category' => 'media'],
            'video' => ['label' => 'Video', 'category' => 'media'],
            'icon' => ['label' => 'Icon', 'category' => 'media'],
            'link' => ['label' => 'Link', 'category' => 'basic'],
            'editor' => ['label' => 'TinyMCE Editor', 'category' => 'basic'],
            'code' => ['label' => 'Code Editor', 'category' => 'advanced'],
            'unit' => ['label' => 'Unit (px/em/%)', 'category' => 'style'],
            'dimension' => ['label' => 'Dimension (4 sides)', 'category' => 'style'],
            'border' => ['label' => 'Border', 'category' => 'style'],
            'typography' => ['label' => 'Typography', 'category' => 'style'],
            'button-group' => ['label' => 'Button Group', 'category' => 'basic'],
            'checkbox' => ['label' => 'Checkbox', 'category' => 'basic'],
            'hidden' => ['label' => 'Hidden', 'category' => 'advanced'],
            'spacing' => ['label' => 'Spacing', 'category' => 'style'],
            'align' => ['label' => 'Alignment', 'category' => 'style'],
            'font' => ['label' => 'Font', 'category' => 'style'],
            'gradient' => ['label' => 'Gradient', 'category' => 'style'],
            'shadow' => ['label' => 'Shadow', 'category' => 'style'],
            'animation' => ['label' => 'Animation', 'category' => 'advanced'],
            'form' => ['label' => 'Nested Form', 'category' => 'advanced'],
            'raw' => ['label' => 'Raw HTML', 'category' => 'advanced'],
            'ordering' => ['label' => 'Reorder', 'category' => 'advanced'],
            'suggest' => ['label' => 'Autosuggest', 'category' => 'advanced'],
        ];
        foreach ($types as $slug => $config) {
            self::register($slug, $config);
        }
    }
}
