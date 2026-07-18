<?php
namespace LzBuilder;

final class LZ_Template_CPT {

    static public function init(): void {
        self::register_post_type();
        self::register_meta();
        add_action('save_post_lz_template', [__CLASS__, 'save_template_data'], 10, 2);
        add_action('before_delete_post', [__CLASS__, 'delete_template_data'], 10, 1);
    }

    static public function register_post_type(): void {
        register_post_type('lz_template', [
            'labels' => [
                'name'          => __('Lz Templates', 'lz-builder'),
                'singular_name' => __('Lz Template', 'lz-builder'),
                'menu_name'     => __('Templates', 'lz-builder'),
                'add_new'       => __('Add New Template', 'lz-builder'),
                'add_new_item'  => __('Add New Template', 'lz-builder'),
                'edit_item'     => __('Edit Template', 'lz-builder'),
                'view_item'     => __('View Template', 'lz-builder'),
                'search_items'  => __('Search Templates', 'lz-builder'),
                'not_found'     => __('No templates found', 'lz-builder'),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'lz-builder',
            'supports'     => ['title', 'thumbnail'],
            'map_meta_cap' => true,
            'show_in_rest' => true,
            'menu_icon'    => 'dashicons-layout',
            'capabilities' => [
                'edit_post'          => 'edit_lz_template',
                'read_post'          => 'read_lz_template',
                'delete_post'        => 'delete_lz_template',
                'edit_posts'         => 'edit_lz_templates',
                'edit_others_posts'  => 'edit_others_lz_templates',
                'publish_posts'      => 'publish_lz_templates',
                'read_private_posts' => 'read_private_lz_templates',
            ],
        ]);
    }

    static public function register_meta(): void {
        $meta_fields = [
            '_lz_template_data'     => ['type' => 'string', 'description' => 'JSON node tree for template', 'default' => ''],
            '_lz_required_plan'     => ['type' => 'string', 'description' => 'Required UM product slug', 'default' => ''],
            '_lz_template_category' => ['type' => 'string', 'description' => 'Template category', 'default' => 'general'],
        ];

        foreach ($meta_fields as $key => $config) {
            register_post_meta('lz_template', $key, [
                'show_in_rest'  => true,
                'single'        => true,
                'type'          => $config['type'],
                'description'   => $config['description'],
                'default'       => $config['default'],
                'auth_callback' => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    static public function get_templates(array $args = []): array {
        $defaults = [
            'post_type'      => 'lz_template',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if (!empty($args['category'])) {
            $defaults['meta_query'][] = [
                'key'   => '_lz_template_category',
                'value' => sanitize_text_field($args['category']),
            ];
        }

        if (!empty($args['plan'])) {
            $defaults['meta_query'][] = [
                'key'   => '_lz_required_plan',
                'value' => sanitize_text_field($args['plan']),
            ];
        }

        $query = new \WP_Query($defaults);
        $templates = [];

        foreach ($query->posts as $post) {
            $accessible = true;
            if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
                $accessible = LZ_Subscription_Gate::is_template_accessible($post->ID);
            }
            if (!empty($args['check_access']) && !$accessible) {
                continue;
            }
            $templates[$post->ID] = [
                'id'           => $post->ID,
                'title'        => $post->post_title,
                'thumbnail'    => get_the_post_thumbnail_url($post->ID, 'medium') ?: '',
                'accessible'   => $accessible,
                'category'     => get_post_meta($post->ID, '_lz_template_category', true) ?: 'general',
                'required_plan' => get_post_meta($post->ID, '_lz_required_plan', true) ?: '',
            ];
        }

        return $templates;
    }

    static public function get_categories(): array {
        global $wpdb;
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
                WHERE meta_key = %s AND meta_value != '' 
                AND post_id IN (
                    SELECT ID FROM {$wpdb->posts} 
                    WHERE post_type = %s AND post_status = 'publish'
                )",
                '_lz_template_category',
                'lz_template'
            )
        );
        return !empty($results) ? $results : ['general'];
    }

    static public function save_template_data(int $post_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (!isset($_POST['lz_template_data'])) {
            return;
        }
        $raw = wp_unslash($_POST['lz_template_data']);
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return;
        }
        $sanitized = wp_json_encode($decoded, JSON_UNESCAPED_UNICODE);
        update_post_meta($post_id, '_lz_template_data', wp_slash($sanitized));
    }

    static public function get_template_data(int $post_id): ?array {
        $raw = get_post_meta($post_id, '_lz_template_data', true);
        if (empty($raw)) {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    static public function delete_template_data(int $post_id): void {
        $post_type = get_post_type($post_id);
        if ('lz_template' !== $post_type) {
            return;
        }
        delete_post_meta($post_id, '_lz_template_data');
        delete_post_meta($post_id, '_lz_required_plan');
        delete_post_meta($post_id, '_lz_template_category');
    }

    static public function apply_to_post(int $template_id, int $post_id): bool {
        $data = self::get_template_data($template_id);
        if (empty($data)) {
            return false;
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
            $accessible = LZ_Subscription_Gate::is_template_accessible($template_id);
            if (!$accessible) {
                return false;
            }
            $data = array_values(array_filter($data, function ($node) {
                if (!is_array($node) || !isset($node['type'])) {
                    return false;
                }
                if ('module' !== $node['type']) {
                    return true;
                }
                $module_slug = $node['module'] ?? '';
                if (!$module_slug) {
                    return true;
                }
                return LZ_Subscription_Gate::is_module_accessible($module_slug);
            }));
        }

        $node_id_map = [];
        $new_data = [];

        foreach ($data as $node) {
            if (!isset($node['node_id'])) {
                continue;
            }
            $old_id = $node['node_id'];
            $new_id = LZ_Page_Data::generate_node_id();
            $node_id_map[$old_id] = $new_id;
        }

        foreach ($data as $node) {
            if (!isset($node['node_id'])) {
                continue;
            }
            $new_node = $node;
            $new_node['node_id'] = $node_id_map[$node['node_id']];
            if (!empty($new_node['parent_id']) && isset($node_id_map[$new_node['parent_id']])) {
                $new_node['parent_id'] = $node_id_map[$new_node['parent_id']];
            }
            $new_data[] = $new_node;
        }

        LZ_Page_Data::update_layout_data($post_id, $new_data, 'published');
        LZ_CSS_Accumulator::clear();

        return true;
    }
}
