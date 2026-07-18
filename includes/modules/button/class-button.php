<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Button extends LZ_Module_Base {
    protected string $slug = 'button';
    protected string $name = 'Button';
    protected string $category = 'content';
    protected string $description = 'Add a customizable button.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Content', 'lz-builder'),
                        'fields' => [
                            'text' => [
                                'type' => 'text',
                                'label' => __('Button Text', 'lz-builder'),
                                'default' => 'Click Here',
                            ],
                            'link' => [
                                'type' => 'link',
                                'label' => __('Link', 'lz-builder'),
                            ],
                            'style' => [
                                'type' => 'select',
                                'label' => __('Style', 'lz-builder'),
                                'default' => 'flat',
                                'options' => [
                                    'flat' => __('Flat', 'lz-builder'),
                                    'gradient' => __('Gradient', 'lz-builder'),
                                    'outlined' => __('Outlined', 'lz-builder'),
                                ],
                            ],
                            'size' => [
                                'type' => 'button-group',
                                'label' => __('Size', 'lz-builder'),
                                'default' => 'medium',
                                'options' => [
                                    'small' => __('Small', 'lz-builder'),
                                    'medium' => __('Medium', 'lz-builder'),
                                    'large' => __('Large', 'lz-builder'),
                                ],
                            ],
                            'width' => [
                                'type' => 'select',
                                'label' => __('Width', 'lz-builder'),
                                'default' => 'auto',
                                'options' => [
                                    'auto' => __('Auto', 'lz-builder'),
                                    'full' => __('Full Width', 'lz-builder'),
                                    'custom' => __('Custom', 'lz-builder'),
                                ],
                            ],
                            'width_custom' => [
                                'type' => 'unit',
                                'label' => __('Custom Width', 'lz-builder'),
                                'default' => 200,
                                'units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
                                'default_unit' => 'px',
                            ],
                            'alignment' => [
                                'type' => 'button-group',
                                'label' => __('Alignment', 'lz-builder'),
                                'default' => 'center',
                                'options' => [
                                    'left' => __('Left', 'lz-builder'),
                                    'center' => __('Center', 'lz-builder'),
                                    'right' => __('Right', 'lz-builder'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => __('Style', 'lz-builder'),
                        'fields' => [
                            'background_color' => [
                                'type' => 'color',
                                'label' => __('Background Color', 'lz-builder'),
                                'default' => '#007cba',
                            ],
                            'text_color' => [
                                'type' => 'color',
                                'label' => __('Text Color', 'lz-builder'),
                                'default' => '#ffffff',
                            ],
                            'border' => [
                                'type' => 'border',
                                'label' => __('Border', 'lz-builder'),
                            ],
                            'border_radius' => [
                                'type' => 'unit',
                                'label' => __('Border Radius', 'lz-builder'),
                                'default' => 4,
                                'units' => ['px', 'em'],
                                'default_unit' => 'px',
                            ],
                            'padding_vertical' => [
                                'type' => 'unit',
                                'label' => __('Padding Vertical', 'lz-builder'),
                                'default' => 10,
                                'units' => ['px', 'em'],
                                'default_unit' => 'px',
                            ],
                            'padding_horizontal' => [
                                'type' => 'unit',
                                'label' => __('Padding Horizontal', 'lz-builder'),
                                'default' => 20,
                                'units' => ['px', 'em'],
                                'default_unit' => 'px',
                            ],
                        ],
                    ],
                    [
                        'title' => __('Spacing', 'lz-builder'),
                        'fields' => [
                            'margin_top' => [
                                'type' => 'unit',
                                'label' => __('Margin Top', 'lz-builder'),
                                'units' => ['px', 'em', '%'],
                            ],
                            'margin_bottom' => [
                                'type' => 'unit',
                                'label' => __('Margin Bottom', 'lz-builder'),
                                'units' => ['px', 'em', '%'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function render(\stdClass $node, \stdClass $settings): string {
        ob_start();
        $template = $this->path('includes/frontend.php');
        if (file_exists($template)) {
            include $template;
        }
        return ob_get_clean();
    }

    public function get_css(\stdClass $node, \stdClass $settings): array {
        $wrap_selector = '.lz-node-' . $node->node_id . '.lz-button-wrap';
        $btn_selector = '.lz-node-' . $node->node_id . '.lz-button';
        $btn_sel2 = '.lz-button.lz-node-' . $node->node_id;

        if (!empty($settings->alignment)) {
            LZ_CSS_Accumulator::add_rule($wrap_selector, 'text-align', $settings->alignment);
        }

        $bg = !empty($settings->background_color) ? $settings->background_color : '#007cba';
        $tc = !empty($settings->text_color) ? $settings->text_color : '#ffffff';

        if ($settings->style === 'flat' || empty($settings->style)) {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'background', $bg);
        } elseif ($settings->style === 'outlined') {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'background', 'transparent');
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'border', '2px solid ' . $bg);
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'color', $bg);
        }

        if ($settings->style !== 'outlined') {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'color', $tc);
        }

        if (isset($settings->border_radius) && $settings->border_radius !== '' && $settings->border_radius !== null) {
            $unit = isset($settings->border_radius_unit) ? $settings->border_radius_unit : 'px';
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'border-radius', $settings->border_radius . $unit);
        }

        if (isset($settings->padding_vertical) && $settings->padding_vertical !== '') {
            $unit = isset($settings->padding_vertical_unit) ? $settings->padding_vertical_unit : 'px';
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'padding-top', $settings->padding_vertical . $unit);
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'padding-bottom', $settings->padding_vertical . $unit);
        }

        if (isset($settings->padding_horizontal) && $settings->padding_horizontal !== '') {
            $unit = isset($settings->padding_horizontal_unit) ? $settings->padding_horizontal_unit : 'px';
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'padding-left', $settings->padding_horizontal . $unit);
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'padding-right', $settings->padding_horizontal . $unit);
        }

        if (!empty($settings->width)) {
            if ($settings->width === 'full') {
                LZ_CSS_Accumulator::add_rule($btn_sel2, 'width', '100%');
                LZ_CSS_Accumulator::add_rule($btn_sel2, 'display', 'block');
                LZ_CSS_Accumulator::add_rule($btn_sel2, 'text-align', 'center');
            } elseif ($settings->width === 'custom' && isset($settings->width_custom) && $settings->width_custom !== '') {
                $wu = isset($settings->width_custom_unit) ? $settings->width_custom_unit : 'px';
                LZ_CSS_Accumulator::add_rule($btn_sel2, 'width', $settings->width_custom . $wu);
                LZ_CSS_Accumulator::add_rule($btn_sel2, 'display', 'inline-block');
            }
        }

        if (isset($settings->margin_top) && $settings->margin_top !== '') {
            $unit = isset($settings->margin_top_unit) ? $settings->margin_top_unit : 'px';
            LZ_CSS_Accumulator::add_rule($wrap_selector, 'margin-top', $settings->margin_top . $unit);
        }

        if (isset($settings->margin_bottom) && $settings->margin_bottom !== '') {
            $unit = isset($settings->margin_bottom_unit) ? $settings->margin_bottom_unit : 'px';
            LZ_CSS_Accumulator::add_rule($wrap_selector, 'margin-bottom', $settings->margin_bottom . $unit);
        }

        return [];
    }
}
