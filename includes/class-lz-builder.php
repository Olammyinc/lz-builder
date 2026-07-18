<?php
namespace LzBuilder;

final class LZ_Builder {
    static public function init(): void {
        add_action('init', [__CLASS__, 'load_modules'], 1);
        add_action('init', [__CLASS__, 'load_textdomain']);
        add_action('init', [__CLASS__, 'init_field_types']);
        add_action('admin_init', [__CLASS__, 'init_admin']);
        add_action('wp', [__CLASS__, 'init_ui'], 11);
        add_action('init', ['\LzBuilder\LZ_AJAX_Handlers', 'init']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets']);
        add_filter('the_content', [__CLASS__, 'render_content']);
        add_filter('template_include', [__CLASS__, 'load_builder_template'], 999);
        add_action('admin_bar_menu', [__CLASS__, 'admin_bar_menu'], 999);
        register_activation_hook(LZ_BUILDER_FILE, ['\LzBuilder\LZ_Activation', 'activate']);
        register_deactivation_hook(LZ_BUILDER_FILE, ['\LzBuilder\LZ_Activation', 'deactivate']);
    }

    static public function init_field_types(): void {
        if (class_exists('\LzBuilder\Settings\LZ_Field_Types')) {
            \LzBuilder\Settings\LZ_Field_Types::init();
        }
    }

    static public function init_admin(): void {
        LZ_Template_CPT::init();
        LZ_Admin::init();
    }

    static public function load_textdomain(): void {
        load_plugin_textdomain('lz-builder', false, dirname(plugin_basename(LZ_BUILDER_FILE)) . '/languages');
    }

    static public function load_modules(): void {
        do_action('lz_builder_before_load_modules');
        LZ_Module_Registry::get_instance()->init();
        do_action('lz_builder_after_load_modules');
        if (function_exists('wu_register_limit_module')) {
            LZ_Subscription_Gate::register_limitation_modules();
        }
    }

    static public function register_assets(): void {
        wp_register_style('lz-builder', LZ_BUILDER_URL . 'assets/css/build/lz-builder.css', [], LZ_BUILDER_VERSION);
        wp_register_style('lz-builder-admin', LZ_BUILDER_URL . 'assets/css/build/lz-admin.css', [], LZ_BUILDER_VERSION);
        wp_register_style('lz-builder-frontend', LZ_BUILDER_URL . 'assets/css/build/lz-builder-frontend.css', [], LZ_BUILDER_VERSION);
        $app_asset = LZ_BUILDER_DIR . 'assets/js/build/lz-builder.asset.php';
        $app_deps = file_exists($app_asset) ? include $app_asset : ['dependencies' => ['wp-element'], 'version' => LZ_BUILDER_VERSION];
        wp_register_script('lz-builder-app', LZ_BUILDER_URL . 'assets/js/build/lz-builder.js', $app_deps['dependencies'], $app_deps['version'], true);
    }

    static public function is_builder_active(): bool {
        if (!isset($_GET['lz_builder'])) return false;
        $post_id = get_queried_object_id();
        if ($post_id && !current_user_can('edit_post', $post_id)) return false;
        return current_user_can('edit_posts');
    }

    static public function init_ui(): void {
        if (!self::is_builder_active()) {
            return;
        }
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_builder_assets'], 100);
        add_action('wp_head', [__CLASS__, 'render_builder_head']);
    }

    static public function enqueue_builder_assets(): void {
        wp_enqueue_style('lz-builder');
        wp_enqueue_script('lz-builder-app');

        $post_id = get_the_ID();
        $data = [
            'post_id'        => $post_id,
            'modules'        => LZ_Module_Registry::get_instance()->get_modules_for_panel(),
            'locked_modules' => LZ_Subscription_Gate::get_locked_modules_data(),
            'site_has_um'    => class_exists('WP_Ultimo'),
            'nonce'          => wp_create_nonce('lz_builder_nonce'),
            'ajax_url'       => admin_url('admin-ajax.php'),
            'rest_url'       => rest_url('lz-builder/v1/'),
            'exit_url'       => remove_query_arg('lz_builder', get_permalink($post_id)),
            'strings'        => [
                'upgrade_to_unlock' => __('Upgrade to Unlock', 'lz-builder'),
                'save'              => __('Save', 'lz-builder'),
                'publish'           => __('Publish', 'lz-builder'),
            ],
        ];
        wp_localize_script('lz-builder-app', 'LZBuilderData', $data);
    }

    static public function render_builder_head(): void {
        echo '<meta name="lz-builder-active" content="1">';
    }

    static public function render_builder_ui(): void {
        include LZ_BUILDER_DIR . 'templates/builder-shell.php';
    }

    static public function enqueue_frontend_assets(): void {
        if (is_singular()) {
            $post_id = get_the_ID();
            if ($post_id && (LZ_Page_Data::has_builder_data($post_id) || !empty(LZ_Page_Data::get_layout_data($post_id, 'draft')))) {
                wp_enqueue_style('lz-builder-frontend');
            }
        }
    }

    static public function render_content(string $content): string {
        if (!is_main_query() || !in_the_loop()) {
            return $content;
        }
        $post_id = get_the_ID();

        $is_preview = isset($_GET['lz_builder_preview']);
        if ($is_preview && !current_user_can('edit_post', $post_id)) {
            return $content;
        }
        $status = $is_preview ? 'draft' : 'published';

        if (!$is_preview && !LZ_Page_Data::has_builder_data($post_id)) {
            return $content;
        }

        $builder_html = LZ_Page_Data::get_builder_content($post_id, $status);
        if (empty($builder_html) && !$is_preview) {
            return $content;
        }
        return $builder_html;
    }

    static public function load_builder_template(string $template): string {
        if (self::is_builder_active()) {
            return LZ_BUILDER_DIR . 'templates/builder-shell.php';
        }
        if (isset($_GET['lz_builder_preview'])) {
            $pid = get_queried_object_id();
            if ($pid && current_user_can('edit_post', $pid)) {
                return LZ_BUILDER_DIR . 'templates/builder-preview.php';
            }
        }
        return $template;
    }

    static public function admin_bar_menu(\WP_Admin_Bar $wp_admin_bar): void {
        if (self::is_builder_active()) {
            $exit_url = remove_query_arg('lz_builder', get_permalink());
            if (!$exit_url) {
                $exit_url = home_url();
            }
            $wp_admin_bar->add_node([
                'id'    => 'lz-builder-exit',
                'title' => __('Exit Builder', 'lz-builder'),
                'href'  => $exit_url,
            ]);
            return;
        }
        $url = '';
        $title = '';
        $post_types = get_option('lz_builder_post_types', ['page', 'post']);
        if (!is_array($post_types)) {
            $post_types = ['page', 'post'];
        }
        $post = get_post();
        if (!$post) {
            return;
        }
        if (!in_array($post->post_type, $post_types, true) || !current_user_can('edit_post', $post->ID)) {
            return;
        }
        if (is_admin() || is_singular()) {
            $url = add_query_arg('lz_builder', '1', get_permalink($post->ID));
            $title = __('Edit with Lz Builder', 'lz-builder');
        }
        if ($url && $title) {
            $wp_admin_bar->add_node([
                'id'    => 'lz-builder',
                'title' => $title,
                'href'  => $url,
                'meta'  => ['class' => 'lz-builder-admin-bar'],
            ]);
        }
    }
}
