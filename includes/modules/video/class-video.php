<?php
namespace LzBuilder\Modules;

use LzBuilder\LZ_Module_Base;
use LzBuilder\LZ_CSS_Accumulator;

class Video extends LZ_Module_Base {
    protected string $slug = 'video';
    protected string $name = 'Video';
    protected string $category = 'media';
    protected string $description = 'Display embedded or self-hosted videos.';

    public function get_settings_form(): array {
        return [
            [
                'title' => __('General', 'lz-builder'),
                'sections' => [
                    [
                        'title' => __('Source', 'lz-builder'),
                        'fields' => [
                            'video_type' => [
                                'type' => 'select',
                                'label' => __('Video Type', 'lz-builder'),
                                'default' => 'embed',
                                'options' => [
                                    'embed' => __('Embed (YouTube/Vimeo)', 'lz-builder'),
                                    'file' => __('Self-Hosted', 'lz-builder'),
                                ],
                            ],
                            'embed_url' => [
                                'type' => 'text',
                                'label' => __('Embed URL', 'lz-builder'),
                                'default' => '',
                                'placeholder' => 'https://www.youtube.com/watch?v=...',
                            ],
                            'video_file' => [
                                'type' => 'text',
                                'label' => __('Video File URL', 'lz-builder'),
                                'default' => '',
                                'placeholder' => __('Enter video file URL', 'lz-builder'),
                            ],
                            'poster' => [
                                'type' => 'photo',
                                'label' => __('Poster Image', 'lz-builder'),
                            ],
                        ],
                    ],
                    [
                        'title' => __('Settings', 'lz-builder'),
                        'fields' => [
                            'autoplay' => [
                                'type' => 'checkbox',
                                'label' => __('Autoplay', 'lz-builder'),
                                'default' => false,
                            ],
                            'loop' => [
                                'type' => 'checkbox',
                                'label' => __('Loop', 'lz-builder'),
                                'default' => false,
                            ],
                            'controls' => [
                                'type' => 'checkbox',
                                'label' => __('Show Controls', 'lz-builder'),
                                'default' => true,
                            ],
                            'aspect_ratio' => [
                                'type' => 'select',
                                'label' => __('Aspect Ratio', 'lz-builder'),
                                'default' => '16:9',
                                'options' => [
                                    '16:9' => '16:9',
                                    '4:3' => '4:3',
                                    '1:1' => '1:1',
                                ],
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
        $container = '.lz-node-' . $node->node_id . ' .lz-video-wrap';

        $ratio = !empty($settings->aspect_ratio) ? $settings->aspect_ratio : '16:9';
        $padding_map = [
            '16:9' => '56.25%',
            '4:3' => '75%',
            '1:1' => '100%',
        ];
        $pb = $padding_map[$ratio] ?? '56.25%';

        LZ_CSS_Accumulator::add_rule($container, 'position', 'relative');
        LZ_CSS_Accumulator::add_rule($container, 'padding-bottom', $pb);
        LZ_CSS_Accumulator::add_rule($container, 'height', '0');
        LZ_CSS_Accumulator::add_rule($container, 'overflow', 'hidden');
        LZ_CSS_Accumulator::add_rule($container, 'max-width', '100%');

        LZ_CSS_Accumulator::add_rule($container . ' iframe', 'position', 'absolute');
        LZ_CSS_Accumulator::add_rule($container . ' iframe', 'top', '0');
        LZ_CSS_Accumulator::add_rule($container . ' iframe', 'left', '0');
        LZ_CSS_Accumulator::add_rule($container . ' iframe', 'width', '100%');
        LZ_CSS_Accumulator::add_rule($container . ' iframe', 'height', '100%');

        LZ_CSS_Accumulator::add_rule($container . ' video', 'position', 'absolute');
        LZ_CSS_Accumulator::add_rule($container . ' video', 'top', '0');
        LZ_CSS_Accumulator::add_rule($container . ' video', 'left', '0');
        LZ_CSS_Accumulator::add_rule($container . ' video', 'width', '100%');
        LZ_CSS_Accumulator::add_rule($container . ' video', 'height', '100%');

        return [];
    }
}
