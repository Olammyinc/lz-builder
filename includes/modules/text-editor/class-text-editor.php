<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Text_Editor extends LZ_Module_Base {
    protected string $slug = 'text-editor';
    protected string $name = 'Text Editor';
    protected string $category = 'content';
    protected string $description = 'A WYSIWYG text editor for rich content.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Content', 'lz-builder'),
                        'fields' => [
                            'text' => [
                                'type' => 'editor',
                                'label' => __('Text', 'lz-builder'),
                                'default' => '',
                                'rows' => 12,
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

        LZ_CSS_Accumulator::typography($selector, $settings, 'typography');

        return [];
    }
}
