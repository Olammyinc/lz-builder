<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Heading extends LZ_Module_Base {
    protected string $slug = 'heading';
    protected string $name = 'Heading';
    protected string $category = 'content';
    protected string $description = 'Add a heading to your page.';

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
                                'default' => 'Hello World',
                                'placeholder' => __('Enter heading text', 'lz-builder'),
                            ],
                            'tag' => [
                                'type' => 'select',
                                'label' => __('HTML Tag', 'lz-builder'),
                                'default' => 'h2',
                                'options' => [
                                    'h1' => 'H1',
                                    'h2' => 'H2',
                                    'h3' => 'H3',
                                    'h4' => 'H4',
                                    'h5' => 'H5',
                                    'h6' => 'H6',
                                ],
                            ],
                            'alignment' => [
                                'type' => 'button-group',
                                'label' => __('Alignment', 'lz-builder'),
                                'default' => 'left',
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
                            'color' => [
                                'type' => 'color',
                                'label' => __('Color', 'lz-builder'),
                            ],
                            'typography' => [
                                'type' => 'typography',
                                'label' => __('Typography', 'lz-builder'),
                            ],
                            'border' => [
                                'type' => 'border',
                                'label' => __('Border', 'lz-builder'),
                            ],
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
        $selector = '.lz-node-' . $node->node_id;

        if (!empty($settings->color)) {
            LZ_CSS_Accumulator::add_rule($selector, 'color', $settings->color);
        }

        if (!empty($settings->alignment)) {
            LZ_CSS_Accumulator::add_rule($selector, 'text-align', $settings->alignment);
        }

        LZ_CSS_Accumulator::typography($selector, $settings, 'typography');

        if (isset($settings->margin_top) && $settings->margin_top !== '') {
            $unit = isset($settings->margin_top_unit) ? $settings->margin_top_unit : 'px';
            LZ_CSS_Accumulator::add_rule($selector, 'margin-top', $settings->margin_top . $unit);
        }

        if (isset($settings->margin_bottom) && $settings->margin_bottom !== '') {
            $unit = isset($settings->margin_bottom_unit) ? $settings->margin_bottom_unit : 'px';
            LZ_CSS_Accumulator::add_rule($selector, 'margin-bottom', $settings->margin_bottom . $unit);
        }

        return [];
    }
}
