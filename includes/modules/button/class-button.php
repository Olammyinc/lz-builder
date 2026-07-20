<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Button extends LZ_Module_Base {
    protected string $slug = 'button';
    protected string $name = 'Button';
    protected string $category = 'content';
    protected string $description = 'Add a customizable button with a link.';

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
                                'label' => __('Text', 'lz-builder'),
                                'default' => 'Click Here',
                            ],
                            'link' => [
                                'type' => 'link',
                                'label' => __('Link', 'lz-builder'),
                            ],
                        ],
                    ],
                    [
                        'title' => __('Appearance', 'lz-builder'),
                        'fields' => [
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
                                'type' => 'select',
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
                            'padding' => [
                                'type' => 'dimension',
                                'label' => __('Padding', 'lz-builder'),
                            ],
                        ],
                    ],
                    [
                        'title' => __('Spacing', 'lz-builder'),
                        'fields' => [
                            'margin' => [
                                'type' => 'dimension',
                                'label' => __('Margin', 'lz-builder'),
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
        $btn_sel2 = '.lz-button.lz-node-' . $node->node_id;

        if (!empty($settings->alignment)) {
            LZ_CSS_Accumulator::add_rule($wrap_selector, 'text-align', $settings->alignment);
        }

        $bg    = !empty($settings->background_color) ? $settings->background_color : '#007cba';
        $tc    = !empty($settings->text_color) ? $settings->text_color : '#ffffff';
        $style = $settings->style ?? 'flat';

        if ($style === 'flat' || empty($settings->style)) {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'background', $bg);
        } elseif ($style === 'gradient') {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'background', 'linear-gradient(135deg,' . $bg . ', ' . $bg . 'dd)');
        } elseif ($style === 'outlined') {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'background', 'transparent');
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'border', '2px solid ' . $bg);
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'color', $bg);
        }

        if ($style !== 'outlined') {
            LZ_CSS_Accumulator::add_rule($btn_sel2, 'color', $tc);
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

        LZ_CSS_Accumulator::border($btn_sel2, $settings, 'border');
        LZ_CSS_Accumulator::dimension($btn_sel2, $settings, 'padding');
        LZ_CSS_Accumulator::dimension($wrap_selector, $settings, 'margin');

        return [];
    }
}
