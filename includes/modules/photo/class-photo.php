<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Photo extends LZ_Module_Base {
    protected string $slug = 'photo';
    protected string $name = 'Image';
    protected string $category = 'media';
    protected string $description = 'Display an image with optional link.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Content', 'lz-builder'),
                        'fields' => [
                            'photo' => [
                                'type' => 'photo',
                                'label' => __('Image', 'lz-builder'),
                            ],
                            'alt' => [
                                'type' => 'text',
                                'label' => __('Alt Text', 'lz-builder'),
                                'default' => '',
                            ],
                            'link' => [
                                'type' => 'link',
                                'label' => __('Link', 'lz-builder'),
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
                            'size' => [
                                'type' => 'select',
                                'label' => __('Image Size', 'lz-builder'),
                                'default' => 'full',
                                'options' => [
                                    'thumbnail' => __('Thumbnail', 'lz-builder'),
                                    'medium' => __('Medium', 'lz-builder'),
                                    'large' => __('Large', 'lz-builder'),
                                    'full' => __('Full', 'lz-builder'),
                                ],
                            ],
                            'border_radius' => [
                                'type' => 'unit',
                                'label' => __('Border Radius', 'lz-builder'),
                                'units' => ['px', '%'],
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
        $selector = '.lz-node-' . $node->node_id;
        $img_selector = '.lz-node-' . $node->node_id . ' .lz-photo-img';

        if (!empty($settings->alignment)) {
            LZ_CSS_Accumulator::add_rule($selector, 'text-align', $settings->alignment);
        }

        if (isset($settings->border_radius) && $settings->border_radius !== '') {
            $unit = isset($settings->border_radius_unit) ? $settings->border_radius_unit : 'px';
            LZ_CSS_Accumulator::add_rule($img_selector, 'border-radius', $settings->border_radius . $unit);
        }

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
