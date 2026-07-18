<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;
use LzBuilder\LZ_Page_Data;
use LzBuilder\LZ_Module_Registry;

class Column extends LZ_Module_Base {
    protected string $slug = 'column';
    protected string $name = 'Column';
    protected string $category = 'layout';
    protected string $description = 'A column container for organizing modules.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Layout', 'lz-builder'),
                        'fields' => [
                            'size' => [
                                'type' => 'hidden',
                                'label' => __('Column Width', 'lz-builder'),
                                'default' => 100,
                            ],
                            'vertical_alignment' => [
                                'type' => 'button-group',
                                'label' => __('Vertical Alignment', 'lz-builder'),
                                'default' => 'top',
                                'options' => [
                                    'top' => __('Top', 'lz-builder'),
                                    'center' => __('Center', 'lz-builder'),
                                    'bottom' => __('Bottom', 'lz-builder'),
                                ],
                            ],
                            'responsive_order' => [
                                'type' => 'select',
                                'label' => __('Responsive Order', 'lz-builder'),
                                'default' => 'desktop',
                                'options' => [
                                    'desktop' => __('Desktop', 'lz-builder'),
                                    'tablet' => __('Tablet', 'lz-builder'),
                                    'phone' => __('Phone', 'lz-builder'),
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
                            ],
                            'padding' => [
                                'type' => 'dimension',
                                'label' => __('Padding', 'lz-builder'),
                            ],
                            'border' => [
                                'type' => 'border',
                                'label' => __('Border', 'lz-builder'),
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

        if (isset($settings->size) && $settings->size !== '') {
            LZ_CSS_Accumulator::add_rule($selector, 'width', $settings->size . '%');
        }

        if (!empty($settings->background_color)) {
            LZ_CSS_Accumulator::add_rule($selector, 'background-color', $settings->background_color);
        }

        LZ_CSS_Accumulator::dimension($selector, $settings, 'padding');
        LZ_CSS_Accumulator::border($selector, $settings, 'border');

        if (!empty($settings->vertical_alignment)) {
            $align_map = [
                'top' => 'flex-start',
                'center' => 'center',
                'bottom' => 'flex-end',
            ];
            $align = $align_map[$settings->vertical_alignment] ?? 'flex-start';
            LZ_CSS_Accumulator::add_rule($selector, 'display', 'flex');
            LZ_CSS_Accumulator::add_rule($selector, 'flex-direction', 'column');
            LZ_CSS_Accumulator::add_rule($selector, 'justify-content', $align);
        }

        return [];
    }
}
