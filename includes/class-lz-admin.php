<?php
namespace LzBuilder;

final class LZ_Admin {

    static public function init(): void {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('current_screen', [__CLASS__, 'init_rendering']);
        add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_gutenberg_assets']);
        add_action('admin_footer', [__CLASS__, 'print_gutenberg_template']);
        add_action('admin_post_lz_builder_save_settings', [__CLASS__, 'save_settings']);
        add_action('admin_post_lz_builder_save_plans', [__CLASS__, 'save_module_plans']);
        add_action('admin_page_lz-builder-settings', [__CLASS__, 'admin_menu_workaround']);
        add_filter('post_row_actions', [__CLASS__, 'post_row_actions'], 10, 2);
        add_filter('page_row_actions', [__CLASS__, 'post_row_actions'], 10, 2);
    }

    static public function init_rendering(): void {
        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'post-new.php'], true)) {
            return;
        }
        $post_types = get_option('lz_builder_post_types', ['page', 'post']);
        if (!is_array($post_types)) {
            $post_types = ['page', 'post'];
        }
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, $post_types, true)) {
            return;
        }
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('edit_form_after_title', [__CLASS__, 'render_builder_banner']);
    }

    static public function enqueue_gutenberg_assets(): void {
        $post_types = get_option('lz_builder_post_types', ['page', 'post']);
        if (!is_array($post_types)) {
            $post_types = ['page', 'post'];
        }
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->post_type, $post_types, true)) {
            return;
        }
        wp_enqueue_script(
            'lz-builder-gutenberg',
            LZ_BUILDER_URL . 'assets/js/admin/lz-builder-gutenberg.js',
            ['jquery'],
            LZ_BUILDER_VERSION,
            true
        );
        $post = get_post();
        if ($post) {
            wp_localize_script('lz-builder-gutenberg', 'LZBuilderGutenberg', [
                'editUrl' => add_query_arg('lz_builder', '1', get_permalink($post->ID)),
            ]);
        }
    }

    static public function print_gutenberg_template(): void {
        $screen = get_current_screen();
        if (!$screen || !$screen->is_block_editor()) {
            return;
        }
        ?>
        <script id="lz-builder-gutenberg-button-tmpl" type="text/html">
            <div id="lz-builder-gutenberg-button">
                <button type="button" class="button button-primary button-large">
                    <?php esc_html_e('Edit with Lz Builder', 'lz-builder'); ?>
                </button>
            </div>
        </script>
        <?php
    }

    static public function add_admin_menu(): void {
        add_menu_page(
            __('Lz Builder', 'lz-builder'),
            __('Lz Builder', 'lz-builder'),
            'manage_options',
            'lz-builder',
            [__CLASS__, 'render_builder_page'],
            'dashicons-layout',
            30
        );

        add_submenu_page(
            'lz-builder',
            __('Builder', 'lz-builder'),
            __('Builder', 'lz-builder'),
            'edit_posts',
            'lz-builder',
            [__CLASS__, 'render_builder_page']
        );

        add_submenu_page(
            'lz-builder',
            __('Templates', 'lz-builder'),
            __('Templates', 'lz-builder'),
            'manage_options',
            'edit.php?post_type=lz_template'
        );

        add_submenu_page(
            'lz-builder',
            __('Settings', 'lz-builder'),
            __('Settings', 'lz-builder'),
            'manage_options',
            'lz-builder-settings',
            [__CLASS__, 'render_settings_page']
        );

        add_submenu_page(
            'lz-builder',
            __('Module Plans', 'lz-builder'),
            __('Module Plans', 'lz-builder'),
            'manage_options',
            'lz-builder-plans',
            [__CLASS__, 'render_plans_page']
        );
    }

    static public function admin_menu_workaround(): void {
        if (isset($_POST['lz_save_settings'])) {
            self::save_settings();
        }
    }

    static public function render_builder_page(): void {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'lz-builder'));
        }
        echo '<div class="wrap"><h1>' . esc_html__('Lz Builder', 'lz-builder') . '</h1>';
        echo '<p>' . esc_html__('Create or edit pages using the frontend builder. Navigate to any supported post type and click "Edit with Lz Builder" from the admin bar.', 'lz-builder') . '</p>';
        echo '</div>';
    }

    static public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'lz-builder'));
        }
        include LZ_BUILDER_DIR . 'templates/admin-settings.php';
    }

    static public function render_plans_page(): void {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions.', 'lz-builder'));
        }
        include LZ_BUILDER_DIR . 'templates/admin-plans.php';
    }

    static public function post_row_actions(array $actions, \WP_Post $post): array {
        $post_types = get_option('lz_builder_post_types', ['page', 'post']);
        if (in_array($post->post_type, $post_types, true) && current_user_can('edit_post', $post->ID)) {
            $actions['lz_builder'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(add_query_arg('lz_builder', '1', get_permalink($post))),
                esc_html__('Edit with Lz Builder', 'lz-builder')
            );
        }
        return $actions;
    }

    static public function render_builder_banner(): void {
        $post = get_post();
        if (!$post) {
            return;
        }
        $post_type_obj = get_post_type_object($post->post_type);
        $post_type_name = $post_type_obj ? strtolower($post_type_obj->labels->singular_name) : $post->post_type;
        $enabled = LZ_Page_Data::has_builder_data($post->ID);
        ?>
        <div class="lz-builder-admin-banner">
            <?php wp_nonce_field('lz_builder_editor_switch', '_lz_builder_editor'); ?>
            <div class="lz-builder-admin-tabs">
                <span class="lz-builder-tab<?php echo !$enabled ? ' active' : ''; ?>">
                    <?php esc_html_e('Text Editor', 'lz-builder'); ?>
                </span>
                <span class="lz-builder-tab<?php echo $enabled ? ' active' : ''; ?>">
                    <?php esc_html_e('Lz Builder', 'lz-builder'); ?>
                </span>
            </div>
            <div class="lz-builder-admin-ui">
                <h3>
                    <?php
                    if ($enabled) {
                        printf(
                            esc_html__('Lz Builder is currently active for this %s.', 'lz-builder'),
                            esc_html($post_type_name)
                        );
                    } else {
                        printf(
                            esc_html__('Launch the drag-and-drop builder for this %s.', 'lz-builder'),
                            esc_html($post_type_name)
                        );
                    }
                    ?>
                </h3>
                <a href="<?php echo esc_url(add_query_arg('lz_builder', '1', get_permalink($post->ID))); ?>" class="button button-primary button-hero">
                    <?php esc_html_e('Launch Builder', 'lz-builder'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    static public function enqueue_admin_assets(): void {
        wp_enqueue_style('lz-builder-admin', LZ_BUILDER_URL . 'assets/css/build/lz-admin.css', [], LZ_BUILDER_VERSION);
    }

    static public function save_settings(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('lz_builder_settings');

        if (isset($_POST['lz_clear_cache'])) {
            if (class_exists('LzBuilder\LZ_CSS_Accumulator')) {
                LZ_CSS_Accumulator::clear();
            }
            $cache_dir = LZ_Activation::get_cache_dir();
            if (is_dir($cache_dir)) {
                $files = glob($cache_dir . '*');
                if (is_array($files)) {
                    array_map('unlink', $files);
                }
            }
            add_settings_error('lz_builder', 'cache_cleared', __('Builder cache cleared.', 'lz-builder'), 'success');
        }

        if (isset($_POST['lz_save_settings'])) {
            $post_types = isset($_POST['lz_post_types']) ? array_map('sanitize_text_field', wp_unslash($_POST['lz_post_types'])) : [];
            update_option('lz_builder_post_types', $post_types);

            $tablet = isset($_POST['lz_tablet_breakpoint']) ? absint($_POST['lz_tablet_breakpoint']) : 768;
            update_option('lz_tablet_breakpoint', $tablet);

            $phone = isset($_POST['lz_phone_breakpoint']) ? absint($_POST['lz_phone_breakpoint']) : 480;
            update_option('lz_phone_breakpoint', $phone);

            add_settings_error('lz_builder', 'settings_saved', __('Settings saved.', 'lz-builder'), 'success');
        }

        set_transient('lz_builder_settings_errors', get_settings_errors('lz_builder'), 30);
        wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }

    static public function save_module_plans(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        check_admin_referer('lz_builder_plans');

        if (isset($_POST['lz_save_plans']) && isset($_POST['module_plans'])) {
            $plans = wp_unslash($_POST['module_plans']);
            foreach ($plans as $slug => $plan) {
                $slug = sanitize_text_field($slug);
                $plan = sanitize_text_field($plan);
                update_option("lz_module_plan_{$slug}", $plan);
            }
            add_settings_error('lz_builder_plans', 'plans_saved', __('Module plan assignments saved.', 'lz-builder'), 'success');
        }

        set_transient('lz_builder_plans_errors', get_settings_errors('lz_builder_plans'), 30);
        wp_safe_redirect(add_query_arg('plans-updated', 'true', wp_get_referer()));
        exit;
    }
}
