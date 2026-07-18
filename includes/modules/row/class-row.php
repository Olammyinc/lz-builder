<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;
use LzBuilder\LZ_Page_Data;
use LzBuilder\LZ_Module_Registry;

class Row extends LZ_Module_Base {
    protected string $slug = 'row';
    protected string $name = 'Row';
    protected string $category = 'layout';
    protected string $description = 'A container row for organizing columns and modules.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Layout', 'lz-builder'),
                        'fields' => [
                            'width' => [
                                'type' => 'button-group',
                                'label' => __('Width', 'lz-builder'),
                                'default' => 'full',
                                'options' => [
                                    'full' => __('Full', 'lz-builder'),
                                    'fixed' => __('Fixed', 'lz-builder'),
                                ],
                            ],
                            'max_content_width' => [
                                'type' => 'unit',
                                'label' => __('Max Content Width', 'lz-builder'),
                                'default' => 1170,
                                'units' => ['px'],
                                'default_unit' => 'px',
                            ],
                            'min_height' => [
                                'type' => 'unit',
                                'label' => __('Min Height', 'lz-builder'),
                                'units' => ['px', 'vh'],
                            ],
                        ],
                    ],
                    [
                        'title' => __('Background', 'lz-builder'),
                        'fields' => [
                            'background_color' => [
                                'type' => 'color',
                                'label' => __('Background Color', 'lz-builder'),
                            ],
                            'background_image' => [
                                'type' => 'photo',
                                'label' => __('Background Image', 'lz-builder'),
                            ],
                            'background_repeat' => [
                                'type' => 'select',
                                'label' => __('Background Repeat', 'lz-builder'),
                                'default' => 'no-repeat',
                                'options' => [
                                    'no-repeat' => __('No Repeat', 'lz-builder'),
                                    'repeat' => __('Repeat', 'lz-builder'),
                                    'repeat-x' => __('Repeat X', 'lz-builder'),
                                    'repeat-y' => __('Repeat Y', 'lz-builder'),
                                ],
                            ],
                            'background_size' => [
                                'type' => 'select',
                                'label' => __('Background Size', 'lz-builder'),
                                'default' => 'cover',
                                'options' => [
                                    'cover' => __('Cover', 'lz-builder'),
                                    'contain' => __('Contain', 'lz-builder'),
                                    'auto' => __('Auto', 'lz-builder'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'title' => __('Border & Spacing', 'lz-builder'),
                        'fields' => [
                            'border' => [
                                'type' => 'border',
                                'label' => __('Border', 'lz-builder'),
                            ],
                            'padding' => [
                                'type' => 'dimension',
                                'label' => __('Padding', 'lz-builder'),
                                'default' => 20,
                            ],
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
        $selector = '.lz-node-' . $node->node_id;

        if (!empty($settings->width) && $settings->width === 'fixed') {
            $mw = isset($settings->max_content_width) && $settings->max_content_width !== '' ? $settings->max_content_width : 1170;
            $unit = isset($settings->max_content_width_unit) ? $settings->max_content_width_unit : 'px';
            LZ_CSS_Accumulator::add_rule($selector, 'max-width', $mw . $unit);
            LZ_CSS_Accumulator::add_rule($selector, 'margin-left', 'auto');
            LZ_CSS_Accumulator::add_rule($selector, 'margin-right', 'auto');
        }

        if (isset($settings->min_height) && $settings->min_height !== '') {
            $unit = isset($settings->min_height_unit) ? $settings->min_height_unit : 'px';
            LZ_CSS_Accumulator::add_rule($selector, 'min-height', $settings->min_height . $unit);
        }

        if (!empty($settings->background_color)) {
            LZ_CSS_Accumulator::add_rule($selector, 'background-color', $settings->background_color);
        }

        if (!empty($settings->background_image)) {
            $img_url = wp_get_attachment_image_url(intval($settings->background_image), 'full');
            if ($img_url) {
                LZ_CSS_Accumulator::add_rule($selector, 'background-image', 'url(' . $img_url . ')');
            }
        }

        if (!empty($settings->background_repeat)) {
            LZ_CSS_Accumulator::add_rule($selector, 'background-repeat', $settings->background_repeat);
        }

        if (!empty($settings->background_size)) {
            LZ_CSS_Accumulator::add_rule($selector, 'background-size', $settings->background_size);
        }

        LZ_CSS_Accumulator::border($selector, $settings, 'border');
        LZ_CSS_Accumulator::dimension($selector, $settings, 'padding');
        LZ_CSS_Accumulator::dimension($selector, $settings, 'margin');

        return [];
    }
}
