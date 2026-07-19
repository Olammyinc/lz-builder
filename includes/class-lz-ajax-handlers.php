<?php
namespace LzBuilder;

class LZ_AJAX_Handlers {

    private static bool $rest_routes_registered = false;

    public static function init(): void {
        $ajax_actions = [
            'save_layout',
            'save_draft',
            'get_layout',
            'add_row',
            'add_module',
            'delete_node',
            'move_node',
            'duplicate_node',
            'save_settings',
            'render_node',
            'render_settings_form',
            'get_settings_schema',
            'get_templates',
            'apply_template',
            'search_modules',
        ];

        foreach ($ajax_actions as $action) {
            add_action("wp_ajax_lz_builder_{$action}", [self::class, $action]);
        }

        add_action('rest_api_init', [self::class, 'register_rest_routes']);
    }

    public static function register_rest_routes(): void {
        if (self::$rest_routes_registered) {
            return;
        }
        self::$rest_routes_registered = true;

        $routes = [
            'publish'                 => ['methods' => 'POST', 'callback' => 'publish_rest'],
            'save-draft'              => ['methods' => 'POST', 'callback' => 'save_draft_rest'],
            'get-layout'              => ['methods' => 'GET',  'callback' => 'get_layout_rest'],
            'add-row'                 => ['methods' => 'POST', 'callback' => 'add_row_rest'],
            'add-module'              => ['methods' => 'POST', 'callback' => 'add_module_rest'],
            'delete-node'             => ['methods' => 'POST', 'callback' => 'delete_node_rest'],
            'move-node'               => ['methods' => 'POST', 'callback' => 'move_node_rest'],
            'duplicate-node'          => ['methods' => 'POST', 'callback' => 'duplicate_node_rest'],
            'save-settings'           => ['methods' => 'POST', 'callback' => 'save_settings_rest'],
            'render-node'             => ['methods' => 'GET',  'callback' => 'render_node_rest'],
            'render-settings-form'    => ['methods' => 'POST', 'callback' => 'render_settings_form_rest'],
            'get-settings-schema'     => ['methods' => 'POST', 'callback' => 'get_settings_schema_rest'],
            'templates'               => ['methods' => 'GET',  'callback' => 'get_templates_rest'],
            'apply-template'          => ['methods' => 'POST', 'callback' => 'apply_template_rest'],
            'search-modules'          => ['methods' => 'GET',  'callback' => 'search_modules_rest'],
        ];

        foreach ($routes as $route => $args) {
            register_rest_route('lz-builder/v1', '/' . $route, [
                'methods'             => $args['methods'],
                'callback'            => [self::class, $args['callback']],
                'permission_callback' => function ($request) {
                    $post_id = (int) $request->get_param('post_id');
                    if ($post_id > 0) {
                        return current_user_can('edit_post', $post_id);
                    }
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    private static function verify_nonce(): bool {
        $nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
        if (empty($nonce)) {
            $nonce = isset($_SERVER['HTTP_X_WP_NONCE']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_WP_NONCE'])) : '';
        }
        return wp_verify_nonce($nonce, 'lz_builder_nonce') !== false;
    }

    private static function check_permissions(): void {
        if (!self::verify_nonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'lz-builder')]);
        }
        $post_id = self::get_post_id_from_request();
        if ($post_id <= 0) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'lz-builder')]);
        }
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => __('Permission denied for this post.', 'lz-builder')]);
        }
    }

    private static function check_basic_permissions(): void {
        if (!self::verify_nonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'lz-builder')]);
        }
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'lz-builder')]);
        }
    }

    private static function get_post_id_from_request(): int {
        return isset($_REQUEST['post_id']) ? (int) $_REQUEST['post_id'] : 0;
    }

    // ---- Save / Publish / Draft ----

    public static function save_layout(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();

        // Publish: promote draft data to published (server-side; no client payload needed).
        $draft_data = LZ_Page_Data::get_layout_data($post_id, 'draft');
        $data       = is_array($draft_data) ? $draft_data : [];

        if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
            $data = LZ_Subscription_Gate::filter_available_modules($data);
        }

        LZ_Page_Data::update_layout_data($post_id, $data, 'published');
        LZ_CSS_Accumulator::clear();
        wp_send_json_success(['message' => __('Layout published.', 'lz-builder')]);
    }

    public static function save_draft(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();

        // Draft is already authoritative server-side.  This endpoint is a no-op
        // that just acknowledges the save and clears the CSS accumulator.
        LZ_CSS_Accumulator::clear();
        wp_send_json_success(['message' => __('Draft saved.', 'lz-builder')]);
    }

    public static function get_layout(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $status  = isset($_REQUEST['status']) ? sanitize_text_field(wp_unslash($_REQUEST['status'])) : 'published';
        $data    = LZ_Page_Data::get_layout_data($post_id, $status);
        wp_send_json_success(['data' => $data]);
    }

    // ---- Row ----

    public static function add_row(): void {
        self::check_permissions();
        $post_id  = self::get_post_id_from_request();
        $layout   = isset($_POST['layout']) ? sanitize_text_field(wp_unslash($_POST['layout'])) : '1-col';
        $position = isset($_POST['position']) ? (int) $_POST['position'] : 0;
        $row_id   = LZ_Page_Data::add_row($post_id, $layout, $position, 'draft');
        $layout_html = self::get_layout_html_safe($post_id);
        wp_send_json_success(['node_id' => $row_id, 'layout' => $layout_html, 'message' => __('Row added.', 'lz-builder')]);
    }

    // ---- Module ----

    public static function add_module(): void {
        self::check_permissions();
        $post_id    = self::get_post_id_from_request();
        $module_slug = isset($_POST['module']) ? sanitize_text_field(wp_unslash($_POST['module'])) : '';
        $parent_id  = isset($_POST['parent_id']) ? sanitize_text_field(wp_unslash($_POST['parent_id'])) : '';
        $position   = isset($_POST['position']) ? (int) $_POST['position'] : 0;

        if (empty($module_slug)) {
            wp_send_json_error(['message' => __('Missing parameters.', 'lz-builder')]);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_module_accessible($module_slug)) {
            wp_send_json_error(['message' => __('Module not available on your plan.', 'lz-builder')]);
        }

        if (empty($parent_id)) {
            $parent_id = LZ_Page_Data::find_last_column($post_id, 'draft');
        }

        $node_id = LZ_Page_Data::add_module($post_id, $module_slug, $parent_id, $position, 'draft');
        if (is_wp_error($node_id)) {
            wp_send_json_error(['message' => $node_id->get_error_message()]);
        }

        // Render just the new module — fast, single-node render.
        $rendered_html = '';
        $render_error  = '';
        try {
            $module_node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
            if ($module_node) {
                $module_obj = LZ_Module_Registry::get_instance()->get_module($module_slug);
                if ($module_obj) {
                    $settings = isset($module_node->settings) && is_object($module_node->settings)
                        ? $module_node->settings
                        : (is_array($module_node->settings ?? null) ? (object) $module_node->settings : new \stdClass());
                    $rendered_html = $module_obj->render($module_node, $settings);
                } else {
                    $render_error = sprintf(__('Module class for "%s" not found.', 'lz-builder'), $module_slug);
                }
            } else {
                $render_error = __('Node not found after creation.', 'lz-builder');
            }
        } catch (\Throwable $e) {
            $render_error = $e->getMessage();
            error_log('[Lz Builder] Module render error for "' . $module_slug . '": ' . $render_error);
        }

        wp_send_json_success([
            'node_id'      => $node_id,
            'html'         => $rendered_html,
            'parent_id'    => $parent_id,
            'layout'       => self::get_layout_html_safe($post_id),
            'render_error' => $render_error,
            'message'      => __('Module added.', 'lz-builder'),
        ]);
    }

    // ---- Delete / Move / Duplicate ----

    public static function delete_node(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $node_id = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';
        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }
        LZ_Page_Data::delete_node($node_id, $post_id, 'draft');
        $layout_html = self::get_layout_html_safe($post_id);
        wp_send_json_success(['message' => __('Node deleted.', 'lz-builder'), 'layout' => $layout_html]);
    }

    public static function move_node(): void {
        self::check_permissions();
        $post_id   = self::get_post_id_from_request();
        $node_id   = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';
        $parent_id = isset($_POST['parent_id']) ? sanitize_text_field(wp_unslash($_POST['parent_id'])) : '';
        $position  = isset($_POST['position']) ? (int) $_POST['position'] : 0;

        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }

        LZ_Page_Data::move_node($node_id, $parent_id, $position, $post_id, 'draft');
        wp_send_json_success(['message' => __('Node moved.', 'lz-builder')]);
    }

    public static function duplicate_node(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $node_id = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';
        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }
        $new_id = LZ_Page_Data::duplicate_node($node_id, $post_id, 'draft');
        $layout_html = self::get_layout_html_safe($post_id);
        wp_send_json_success(['node_id' => $new_id, 'layout' => $layout_html, 'message' => __('Node duplicated.', 'lz-builder')]);
    }

    // ---- Settings ----

    public static function save_settings(): void {
        self::check_permissions();
        $node_id  = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';
        $settings = isset($_POST['settings']) ? json_decode(wp_unslash($_POST['settings'])) : null;

        if (empty($node_id) || !$settings instanceof \stdClass) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'lz-builder')]);
        }

        $post_id = self::get_post_id_from_request();

        $node        = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node) {
            wp_send_json_error(['message' => __('Node not found.', 'lz-builder')]);
        }
        $module_slug = $node->module ?? '';
        $module_obj  = $node && 'module' === $node->type && $module_slug
            ? LZ_Module_Registry::get_instance()->get_module($module_slug)
            : null;

        if ($module_obj && $settings instanceof \stdClass) {
            $form = $module_obj->get_settings_form();
            \LzBuilder\LZ_Settings_Form::register($module_slug, $form);
            $settings = (object) \LzBuilder\LZ_Settings_Form::sanitize_settings(
                (array) $settings,
                $module_slug
            );
        }

        $updated = LZ_Page_Data::save_settings($node_id, $settings, $post_id, 'draft');
        LZ_CSS_Accumulator::clear();

        // Re-fetch after save so the rendered HTML reflects the new settings.
        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        $html = '';
        if ($module_obj && $node) {
            try {
                $settings_obj = isset($node->settings) && is_object($node->settings)
                    ? $node->settings : new \stdClass();
                $html = $module_obj->render($node, $settings_obj);
                if (false === strpos($html, 'data-node-id=')) {
                    $html = preg_replace(
                        '/<(\w+)([^>]*)>/',
                        '<$1$2 data-node-id="' . esc_attr($node_id) . '">',
                        $html,
                        1
                    );
                }
            } catch (\Throwable $e) {
                error_log('[Lz Builder] Re-render error: ' . $e->getMessage());
            }
        }

        wp_send_json_success([
            'settings' => $updated,
            'html'     => $html,
            'message'  => __('Settings saved.', 'lz-builder'),
        ]);
    }

    public static function render_settings_form(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $node_id = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';

        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node || 'module' !== $node->type) {
            wp_send_json_error(['message' => __('Node not found or not a module.', 'lz-builder')]);
        }

        $module_slug = $node->module ?? '';
        $module_obj  = LZ_Module_Registry::get_instance()->get_module($module_slug);
        if (!$module_obj || !method_exists($module_obj, 'get_settings_form')) {
            wp_send_json_error(['message' => __('Module not found.', 'lz-builder')]);
        }

        $form = $module_obj->get_settings_form();
        $current_settings = isset($node->settings) && is_object($node->settings)
            ? (array) $node->settings
            : [];

        $html = self::build_settings_form_html($form, $current_settings, $node_id, $module_obj);

        wp_send_json_success([
            'html'     => $html,
            'node_id'  => $node_id,
            'settings' => $current_settings,
        ]);
    }

    /**
     * AJAX: get_settings_schema
     *
     * Returns the module's settings form as a JSON schema + current values,
     * so the React client can render fields without dangerouslySetInnerHTML.
     */
    public static function get_settings_schema(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $node_id = isset($_POST['node_id']) ? sanitize_text_field(wp_unslash($_POST['node_id'])) : '';

        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node || 'module' !== $node->type) {
            wp_send_json_error(['message' => __('Node not found or not a module.', 'lz-builder')]);
        }

        $module_slug = $node->module ?? '';
        $module_obj  = LZ_Module_Registry::get_instance()->get_module($module_slug);
        if (!$module_obj || !method_exists($module_obj, 'get_settings_form')) {
            wp_send_json_error(['message' => __('Module not found.', 'lz-builder')]);
        }

        $current_settings = isset($node->settings) && is_object($node->settings)
            ? (array) $node->settings
            : [];

        $schema = self::build_settings_schema($module_obj, $current_settings, $node);

        wp_send_json_success($schema);
    }

    /**
     * Build the settings schema array shared by AJAX and REST endpoints.
     *
     * Traverses get_settings_form() tabs→sections→fields and produces a JSON-safe
     * representation with ordered options arrays and values merged over defaults.
     */
    private static function build_settings_schema($module_obj, array $current_settings, \stdClass $node): array {
        $form = $module_obj->get_settings_form();

        // Register first so LZ_Settings_Form can traverse it for defaults
        \LzBuilder\LZ_Settings_Form::register($module_obj->get_slug(), $form);

        $defaults      = LZ_Settings_Form::get_defaults($module_obj->get_slug());
        $merged_values = array_merge($defaults, $current_settings);

        $tabs = [];
        foreach ($form as $tab) {
            $tab_out = [
                'title'    => $tab['title'] ?? '',
                'sections' => [],
            ];

            foreach ($tab['sections'] ?? [] as $section) {
                $section_out = [
                    'title'  => $section['title'] ?? '',
                    'fields' => [],
                ];

                foreach ($section['fields'] ?? [] as $field_key => $field) {
                    if (!is_array($field) || !isset($field['type'])) {
                        continue;
                    }

                    $field_out = $field;

                    // Preview hint for live-preview (Tier 1).
                    $field_out['preview'] = $field['preview'] ?? self::get_field_preview_type($field['type']);

                    // Convert associative options to ordered [{value, label}] for JSON stability
                    if (isset($field['options']) && is_array($field['options'])) {
                        $ordered = [];
                        foreach ($field['options'] as $opt_val => $opt_label) {
                            $ordered[] = [
                                'value' => $opt_val,
                                'label' => $opt_label,
                            ];
                        }
                        $field_out['options'] = $ordered;
                    } else {
                        $field_out['options'] = null;
                    }

                    $section_out['fields'][$field_key] = $field_out;
                }

                $tab_out['sections'][] = $section_out;
            }

            $tabs[] = $tab_out;
        }

        return [
            'node_id' => $node->node_id,
            'module'  => $node->module ?? '',
            'title'   => $module_obj->get_name() . ' ' . __('Settings', 'lz-builder'),
            'tabs'    => $tabs,
            'values'  => $merged_values,
        ];
    }

    /**
     * Determine whether a field type can be live-previewed via CSS
     * or requires a server re-render (markup change).
     */
    private static function get_field_preview_type(string $type): string {
        static $css_types = [
            'color'      => true,
            'typography' => true,
            'border'     => true,
            'dimension'  => true,
            'spacing'    => true,
            'unit'       => true,
            'align'      => true,
            'shadow'     => true,
            'gradient'   => true,
            'font'       => true,
        ];
        return isset($css_types[$type]) ? 'css' : 'render';
    }

    /**
     * Shared settings-form HTML builder used by both AJAX and REST endpoints.
     */
    private static function build_settings_form_html(array $form, array $current_settings, string $node_id, $module_obj): string {
        $html = '<form class="lz-settings-form" id="lz-settings-form" data-node-id="' . esc_attr($node_id) . '">';
        $html .= '<div class="lz-settings-header">';
        $html .= '<h3 class="lz-settings-title">' . esc_html($module_obj->get_name()) . ' ' . __('Settings', 'lz-builder') . '</h3>';
        $html .= '<button type="button" class="lz-btn lz-btn-back" id="lz-settings-back">&larr; ' . __('Back', 'lz-builder') . '</button>';
        $html .= '</div>';

        foreach ($form as $tab_index => $tab) {
            $tab_id = 'lz-tab-' . $tab_index;
            $html .= '<div class="lz-settings-tab" data-tab="' . esc_attr($tab_id) . '">';
            if (!empty($tab['title'])) {
                $html .= '<h4 class="lz-settings-tab-title">' . esc_html($tab['title']) . '</h4>';
            }
            if (isset($tab['sections']) && is_array($tab['sections'])) {
                foreach ($tab['sections'] as $section) {
                    if (!empty($section['title'])) {
                        $html .= '<h5 class="lz-settings-section-title">' . esc_html($section['title']) . '</h5>';
                    }
                    if (isset($section['fields']) && is_array($section['fields'])) {
                        $html .= '<div class="lz-settings-fields">';
                        foreach ($section['fields'] as $field_key => $field) {
                            if (!is_array($field) || !isset($field['type'])) continue;
                            $field_value = $current_settings[$field_key] ?? ($field['default'] ?? '');
                            $html .= '<div class="lz-field lz-field-' . esc_attr($field['type']) . '">';
                            $html .= '<label class="lz-field-label">' . esc_html($field['label'] ?? $field_key) . '</label>';

                            switch ($field['type']) {
                                case 'text':
                                    $html .= '<input type="text" class="lz-field-input" name="' . esc_attr($field_key) . '" value="' . esc_attr($field_value) . '" placeholder="' . esc_attr($field['placeholder'] ?? '') . '">';
                                    break;
                                case 'select':
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($field_key) . '">';
                                    foreach (($field['options'] ?? []) as $opt_val => $opt_label) {
                                        $selected = (string) $field_value === (string) $opt_val ? ' selected' : '';
                                        $html .= '<option value="' . esc_attr($opt_val) . '"' . $selected . '>' . esc_html($opt_label) . '</option>';
                                    }
                                    $html .= '</select>';
                                    break;
                                case 'color':
                                    $color_val = !empty($field_value) ? $field_value : 'transparent';
                                    $html .= '<div class="lz-color-field">';
                                    $html .= '<input type="text" class="lz-field-input lz-field-color-text" name="' . esc_attr($field_key) . '" value="' . esc_attr($field_value) . '" placeholder="#000000">';
                                    $hex = !empty($field_value) ? $field_value : '#8899aa';
                                    $html .= '<span class="lz-color-swatch" style="background-color:' . esc_attr($hex) . '" data-color="' . esc_attr($hex) . '" tabindex="0" role="button" aria-label="' . esc_attr__('Pick color', 'lz-builder') . '">';
                                    $html .= '<input type="color" class="lz-field-color-native" value="' . esc_attr($field_value) . '">';
                                    $html .= '</span>';
                                    $html .= '</div>';
                                    break;
                                case 'textarea':
                                case 'editor':
                                    $html .= '<textarea class="lz-field-textarea" name="' . esc_attr($field_key) . '" rows="' . esc_attr($field['rows'] ?? 4) . '">' . esc_textarea($field_value) . '</textarea>';
                                    break;
                                case 'checkbox':
                                    $checked = $field_value ? ' checked' : '';
                                    $html .= '<input type="checkbox" class="lz-field-checkbox" name="' . esc_attr($field_key) . '" value="1"' . $checked . '>';
                                    break;
                                case 'hidden':
                                    $html .= '<input type="hidden" name="' . esc_attr($field_key) . '" value="' . esc_attr($field_value) . '">';
                                    break;
                                case 'button-group':
                                    $html .= '<div class="lz-field-btn-group">';
                                    foreach (($field['options'] ?? []) as $opt_val => $opt_label) {
                                        $active = (string) $field_value === (string) $opt_val ? ' lz-btn-group-active' : '';
                                        $html .= '<button type="button" class="lz-btn-group-option' . $active . '" data-value="' . esc_attr($opt_val) . '">' . esc_html($opt_label) . '</button>';
                                    }
                                    $html .= '<input type="hidden" name="' . esc_attr($field_key) . '" value="' . esc_attr($field_value) . '">';
                                    $html .= '</div>';
                                    break;
                                case 'unit':
                                    $unit_value = $field_value !== '' ? $field_value : ($field['default'] ?? '');
                                    $unit = $current_settings[$field_key . '_unit'] ?? ($field['default_unit'] ?? 'px');
                                    $units = $field['units'] ?? ['px', 'em', '%'];
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input lz-field-unit-value" name="' . esc_attr($field_key) . '" value="' . esc_attr($unit_value) . '" step="any">';
                                    $html .= '<select class="lz-field-select lz-field-unit-select" name="' . esc_attr($field_key . '_unit') . '">';
                                    foreach ($units as $u) {
                                        $sel = $unit === $u ? ' selected' : '';
                                        $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>';
                                    }
                                    $html .= '</select>';
                                    $html .= '</div>';
                                    break;

                                case 'typography':
                                    $typo_key = $field_key;
                                    $html .= '<div class="lz-field-typography">';
                                    $ff_val = $current_settings[$typo_key . '_font_family'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Font Family', 'lz-builder') . '</label>';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_font_family') . '">';
                                    $font_families = [
                                        '' => __('Default', 'lz-builder'), 'Arial, Helvetica, sans-serif' => 'Arial',
                                        'Helvetica, Arial, sans-serif' => 'Helvetica', 'Georgia, serif' => 'Georgia',
                                        'Times New Roman, serif' => 'Times New Roman', 'Verdana, Geneva, sans-serif' => 'Verdana',
                                        'Trebuchet MS, sans-serif' => 'Trebuchet MS', 'Courier New, monospace' => 'Courier New',
                                        'Impact, sans-serif' => 'Impact', 'Palatino Linotype, serif' => 'Palatino',
                                        'Tahoma, Geneva, sans-serif' => 'Tahoma', 'Open Sans, sans-serif' => 'Open Sans',
                                        'Roboto, sans-serif' => 'Roboto', 'Lato, sans-serif' => 'Lato',
                                        'Montserrat, sans-serif' => 'Montserrat', 'Inter, sans-serif' => 'Inter',
                                        'Poppins, sans-serif' => 'Poppins', 'Nunito, sans-serif' => 'Nunito',
                                        'Raleway, sans-serif' => 'Raleway', 'Ubuntu, sans-serif' => 'Ubuntu',
                                        'Merriweather, serif' => 'Merriweather', 'Playfair Display, serif' => 'Playfair Display',
                                        'Source Sans Pro, sans-serif' => 'Source Sans Pro', 'system-ui, sans-serif' => 'System UI',
                                    ];
                                    foreach ($font_families as $ff_css => $ff_label) {
                                        $ff_sel = $ff_val === $ff_css ? ' selected' : '';
                                        $html .= '<option value="' . esc_attr($ff_css) . '"' . $ff_sel . '>' . esc_html($ff_label) . '</option>';
                                    }
                                    $html .= '</select>';
                                    $fw_val = $current_settings[$typo_key . '_font_weight'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Weight', 'lz-builder') . '</label>';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_font_weight') . '">';
                                    $weights = ['' => __('Default', 'lz-builder'), '100' => '100', '200' => '200', '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900'];
                                    foreach ($weights as $w => $wl) {
                                        $sel = (string) $fw_val === (string) $w ? ' selected' : '';
                                        $html .= '<option value="' . esc_attr($w) . '"' . $sel . '>' . esc_html($wl) . '</option>';
                                    }
                                    $html .= '</select>';
                                    $fs_val = $current_settings[$typo_key . '_font_size'] ?? '';
                                    $fs_unit = $current_settings[$typo_key . '_font_size_unit'] ?? 'px';
                                    $html .= '<label class="lz-field-sub-label">' . __('Font Size', 'lz-builder') . '</label>';
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($typo_key . '_font_size') . '" value="' . esc_attr($fs_val) . '" step="any">';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_font_size_unit') . '">';
                                    foreach (['px', 'em', 'rem', 'vw'] as $u) { $sel = $fs_unit === $u ? ' selected' : ''; $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>'; }
                                    $html .= '</select></div>';
                                    $lh_val = $current_settings[$typo_key . '_line_height'] ?? '';
                                    $lh_unit = $current_settings[$typo_key . '_line_height_unit'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Line Height', 'lz-builder') . '</label>';
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($typo_key . '_line_height') . '" value="' . esc_attr($lh_val) . '" step="any">';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_line_height_unit') . '">';
                                    foreach (['' => __('None', 'lz-builder'), 'em' => 'em', 'px' => 'px', '%' => '%'] as $uv => $ul) { $sel = $lh_unit === $uv ? ' selected' : ''; $html .= '<option value="' . esc_attr($uv) . '"' . $sel . '>' . esc_html($ul) . '</option>'; }
                                    $html .= '</select></div>';
                                    $tt_val = $current_settings[$typo_key . '_text_transform'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Text Transform', 'lz-builder') . '</label>';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_text_transform') . '">';
                                    $transforms = ['' => __('None', 'lz-builder'), 'uppercase' => __('Uppercase', 'lz-builder'), 'lowercase' => __('Lowercase', 'lz-builder'), 'capitalize' => __('Capitalize', 'lz-builder')];
                                    foreach ($transforms as $tv => $tl) { $sel = $tt_val === $tv ? ' selected' : ''; $html .= '<option value="' . esc_attr($tv) . '"' . $sel . '>' . esc_html($tl) . '</option>'; }
                                    $html .= '</select>';
                                    $ls_val = $current_settings[$typo_key . '_letter_spacing'] ?? '';
                                    $ls_unit = $current_settings[$typo_key . '_letter_spacing_unit'] ?? 'px';
                                    $html .= '<label class="lz-field-sub-label">' . __('Letter Spacing', 'lz-builder') . '</label>';
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($typo_key . '_letter_spacing') . '" value="' . esc_attr($ls_val) . '" step="any">';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($typo_key . '_letter_spacing_unit') . '">';
                                    foreach (['px', 'em'] as $u) { $sel = $ls_unit === $u ? ' selected' : ''; $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>'; }
                                    $html .= '</select></div>';
                                    $html .= '</div>';
                                    break;

                                case 'border':
                                    $b_key = $field_key;
                                    $html .= '<div class="lz-field-border">';
                                    $bs_val = $current_settings[$b_key . '_style'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Style', 'lz-builder') . '</label>';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($b_key . '_style') . '">';
                                    $styles = ['' => __('None', 'lz-builder'), 'solid' => __('Solid', 'lz-builder'), 'dashed' => __('Dashed', 'lz-builder'), 'dotted' => __('Dotted', 'lz-builder'), 'double' => __('Double', 'lz-builder')];
                                    foreach ($styles as $sv => $sl) { $sel = $bs_val === $sv ? ' selected' : ''; $html .= '<option value="' . esc_attr($sv) . '"' . $sel . '>' . esc_html($sl) . '</option>'; }
                                    $html .= '</select>';
                                    $bw_val = $current_settings[$b_key . '_width'] ?? '';
                                    $bw_unit = $current_settings[$b_key . '_width_unit'] ?? 'px';
                                    $html .= '<label class="lz-field-sub-label">' . __('Width', 'lz-builder') . '</label>';
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($b_key . '_width') . '" value="' . esc_attr($bw_val) . '" step="any">';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($b_key . '_width_unit') . '">';
                                    foreach (['px', 'em'] as $u) { $sel = $bw_unit === $u ? ' selected' : ''; $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>'; }
                                    $html .= '</select></div>';
                                    $bc_val = $current_settings[$b_key . '_color'] ?? '';
                                    $html .= '<label class="lz-field-sub-label">' . __('Color', 'lz-builder') . '</label>';
                                    $bc_hex = !empty($bc_val) ? $bc_val : '#8899aa';
                                    $html .= '<div class="lz-color-field">';
                                    $html .= '<input type="text" class="lz-field-input lz-field-color-text" name="' . esc_attr($b_key . '_color') . '" value="' . esc_attr($bc_val) . '" placeholder="#000000">';
                                    $html .= '<span class="lz-color-swatch" style="background-color:' . esc_attr($bc_hex) . '" data-color="' . esc_attr($bc_hex) . '" tabindex="0" role="button" aria-label="' . esc_attr__('Pick color', 'lz-builder') . '">';
                                    $html .= '<input type="color" class="lz-field-color-native" value="' . esc_attr($bc_val) . '">';
                                    $html .= '</span></div>';
                                    $br_val = $current_settings[$b_key . '_radius'] ?? '';
                                    $br_unit = $current_settings[$b_key . '_radius_unit'] ?? 'px';
                                    $html .= '<label class="lz-field-sub-label">' . __('Radius', 'lz-builder') . '</label>';
                                    $html .= '<div class="lz-field-unit-wrap">';
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($b_key . '_radius') . '" value="' . esc_attr($br_val) . '" step="any">';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($b_key . '_radius_unit') . '">';
                                    foreach (['px', '%', 'em'] as $u) { $sel = $br_unit === $u ? ' selected' : ''; $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>'; }
                                    $html .= '</select></div>';
                                    $html .= '</div>';
                                    break;

                                case 'dimension':
                                    $d_key = $field_key;
                                    $d_unit = $current_settings[$d_key . '_unit'] ?? 'px';
                                    $d_linked = !empty($current_settings[$d_key . '_linked']);
                                    $html .= '<div class="lz-field-dimension">';
                                    $html .= '<label class="lz-field-sub-label">' . esc_html($field['label'] ?? $field_key) . '</label>';
                                    $html .= '<label class="lz-field-inline-label"><input type="checkbox" class="lz-field-checkbox" name="' . esc_attr($d_key . '_linked') . '" value="1"' . ($d_linked ? ' checked' : '') . '> ' . __('Link all sides', 'lz-builder') . '</label>';
                                    $sides = ['top' => __('Top', 'lz-builder'), 'right' => __('Right', 'lz-builder'), 'bottom' => __('Bottom', 'lz-builder'), 'left' => __('Left', 'lz-builder')];
                                    $html .= '<div class="lz-dimension-grid">';
                                    foreach ($sides as $side => $label) {
                                        $sd_val = $current_settings[$d_key . '_' . $side] ?? '';
                                        $html .= '<div class="lz-dimension-item"><span class="lz-dimension-label">' . esc_html($label) . '</span>';
                                        $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($d_key . '_' . $side) . '" value="' . esc_attr($sd_val) . '" step="any"></div>';
                                    }
                                    $html .= '</div>';
                                    $html .= '<select class="lz-field-select lz-dimension-unit" name="' . esc_attr($d_key . '_unit') . '">';
                                    foreach (['px', 'em', '%', 'rem', 'vw', 'vh'] as $u) { $sel = $d_unit === $u ? ' selected' : ''; $html .= '<option value="' . esc_attr($u) . '"' . $sel . '>' . esc_html($u) . '</option>'; }
                                    $html .= '</select>';
                                    $html .= '</div>';
                                    break;

                                case 'link':
                                    $l_val = is_object($field_value) ? $field_value : (is_array($field_value) ? (object) $field_value : new \stdClass());
                                    $l_url = $l_val->url ?? '';
                                    $l_target = $l_val->target ?? '';
                                    $l_nofollow = !empty($l_val->nofollow);
                                    $html .= '<div class="lz-field-link">';
                                    $html .= '<label class="lz-field-sub-label">' . __('URL', 'lz-builder') . '</label>';
                                    $html .= '<input type="text" class="lz-field-input" name="' . esc_attr($field_key . '_url') . '" value="' . esc_attr($l_url) . '" placeholder="https://">';
                                    $html .= '<label class="lz-field-sub-label">' . __('Target', 'lz-builder') . '</label>';
                                    $html .= '<select class="lz-field-select" name="' . esc_attr($field_key . '_target') . '">';
                                    $html .= '<option value=""' . ($l_target === '' ? ' selected' : '') . '>' . __('Same Window', 'lz-builder') . '</option>';
                                    $html .= '<option value="_blank"' . ($l_target === '_blank' ? ' selected' : '') . '>' . __('New Window', 'lz-builder') . '</option>';
                                    $html .= '</select>';
                                    $html .= '<label class="lz-field-inline-label"><input type="checkbox" class="lz-field-checkbox" name="' . esc_attr($field_key . '_nofollow') . '" value="1"' . ($l_nofollow ? ' checked' : '') . '> ' . __('nofollow', 'lz-builder') . '</label>';
                                    $html .= '</div>';
                                    break;

                                case 'icon':
                                    $icon_val = is_string($field_value) ? $field_value : '';
                                    $html .= '<input type="text" class="lz-field-input" name="' . esc_attr($field_key) . '" value="' . esc_attr($icon_val) . '" placeholder="dashicons-admin-site">';
                                    break;

                                case 'photo':
                                    $photo_val = is_numeric($field_value) ? intval($field_value) : (is_string($field_value) ? $field_value : '');
                                    $html .= '<input type="number" class="lz-field-input" name="' . esc_attr($field_key) . '" value="' . esc_attr($photo_val) . '" placeholder="' . esc_attr__('Attachment ID', 'lz-builder') . '">';
                                    break;

                                case 'code':
                                    $code_val = is_string($field_value) ? $field_value : '';
                                    $html .= '<textarea class="lz-field-code" name="' . esc_attr($field_key) . '" rows="' . esc_attr($field['rows'] ?? 8) . '" style="font-family:monospace;">' . esc_textarea($code_val) . '</textarea>';
                                    break;

                                default:
                                    $html .= '<div class="lz-field-unknown">' . esc_html(sprintf(__('Field type "%s" not supported yet.', 'lz-builder'), $field['type'])) . '</div>';
                                    break;
                            }
                            $html .= '</div>';
                        }
                        $html .= '</div>';
                    }
                }
            }
            $html .= '</div>';
        }

        $html .= '<div class="lz-settings-actions">';
        $html .= '<button type="submit" class="lz-btn lz-btn-primary lz-btn-save-settings">' . __('Save', 'lz-builder') . '</button>';
        $html .= '</div>';
        $html .= '</form>';

        return $html;
    }

    /**
     * Safely get layout HTML for preview updates after mutations.
     */
    private static function get_layout_html_safe(int $post_id): string {
        try {
            return LZ_Page_Data::get_builder_content($post_id, 'draft');
        } catch (\Throwable $e) {
            error_log('[Lz Builder] Layout HTML error: ' . $e->getMessage());
            return '';
        }
    }

    public static function render_node(): void {
        self::check_permissions();
        $post_id = self::get_post_id_from_request();
        $node_id = isset($_REQUEST['node_id']) ? sanitize_text_field(wp_unslash($_REQUEST['node_id'])) : '';

        if (empty($node_id)) {
            wp_send_json_error(['message' => __('Node ID required.', 'lz-builder')]);
        }

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node) {
            wp_send_json_error(['message' => __('Node not found.', 'lz-builder')]);
        }

        if ($node->type !== 'module') {
            $html = '<div class="lz-builder-node lz-node-' . esc_attr($node->type) . '" data-node-id="' . esc_attr($node->node_id) . '"></div>';
            wp_send_json_success(['html' => $html]);
        }

        $module = LZ_Module_Registry::get_instance()->get_module($node->module ?? '');
        if (!$module || !method_exists($module, 'render')) {
            wp_send_json_error(['message' => __('Module renderer not found.', 'lz-builder')]);
        }

        $html = $module->render($node, $node->settings);
        wp_send_json_success(['html' => $html]);
    }

    public static function get_templates(): void {
        self::check_basic_permissions();
        $args = [
            'post_type'      => 'lz_template',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query = new \WP_Query($args);
        $templates = [];

        foreach ($query->posts as $post) {
            $accessible = true;
            if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
                $accessible = LZ_Subscription_Gate::is_template_accessible($post->ID);
            }
            $templates[] = [
                'id'          => $post->ID,
                'title'       => $post->post_title,
                'thumbnail'   => get_the_post_thumbnail_url($post->ID, 'medium') ?: '',
                'accessible'  => $accessible,
                'category'    => get_post_meta($post->ID, '_lz_template_category', true) ?: 'general',
            ];
        }

        wp_send_json_success(['templates' => $templates]);
    }

    public static function apply_template(): void {
        self::check_permissions();
        $post_id     = self::get_post_id_from_request();
        $template_id = isset($_POST['template_id']) ? (int) $_POST['template_id'] : 0;

        if (!$template_id) {
            wp_send_json_error(['message' => __('Template ID required.', 'lz-builder')]);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_template_accessible($template_id)) {
            wp_send_json_error(['message' => __('Template not available on your plan.', 'lz-builder')]);
        }

        $template_data = LZ_Page_Data::get_layout_data($template_id);
        if (empty($template_data)) {
            wp_send_json_error(['message' => __('Template has no data.', 'lz-builder')]);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
            $template_data = LZ_Subscription_Gate::filter_available_modules($template_data);
        }

        // Template applied to draft, not published — user must publish to make live.
        LZ_Page_Data::update_layout_data($post_id, $template_data, 'draft');
        if (class_exists('LzBuilder\LZ_CSS_Accumulator')) {
            LZ_CSS_Accumulator::clear();
        }
        wp_send_json_success(['message' => __('Template applied.', 'lz-builder')]);
    }

    public static function search_modules(): void {
        self::check_basic_permissions();
        $search  = isset($_REQUEST['search']) ? sanitize_text_field(wp_unslash($_REQUEST['search'])) : '';
        $modules = LZ_Module_Registry::get_instance()->get_all_modules();
        $results = [];

        foreach ($modules as $slug => $module) {
            if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_module_accessible($slug)) {
                continue;
            }
            $name = $module->get_name();
            $description = $module->get_description();
            if (!empty($search)) {
                $search_lower = strtolower($search);
                $name_lower   = strtolower($name);
                $desc_lower   = strtolower($description);
                $slug_lower   = strtolower($slug);
                if (strpos($name_lower, $search_lower) === false
                    && strpos($desc_lower, $search_lower) === false
                    && strpos($slug_lower, $search_lower) === false) {
                    continue;
                }
            }
            $results[] = [
                'slug'        => $slug,
                'name'        => $name,
                'description' => $description,
                'icon'        => $module->get_icon(),
                'category'    => $module->get_category(),
            ];
        }

        wp_send_json_success(['modules' => $results]);
    }

    // ======== REST Endpoints ========

    public static function publish_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id   = (int) $request->get_param('post_id');
        $draft_data = LZ_Page_Data::get_layout_data($post_id, 'draft');
        $data       = is_array($draft_data) ? $draft_data : [];

        if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
            $data = LZ_Subscription_Gate::filter_available_modules($data);
        }
        LZ_Page_Data::update_layout_data($post_id, $data, 'published');
        LZ_CSS_Accumulator::clear();
        return new \WP_REST_Response(['success' => true, 'message' => __('Layout published.', 'lz-builder')], 200);
    }

    public static function save_draft_rest(\WP_REST_Request $request): \WP_REST_Response {
        LZ_CSS_Accumulator::clear();
        return new \WP_REST_Response(['success' => true, 'message' => __('Draft saved.', 'lz-builder')], 200);
    }

    public static function get_layout_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $status  = $request->get_param('status') ?: 'published';
        $data    = LZ_Page_Data::get_layout_data($post_id, $status);
        return new \WP_REST_Response(['success' => true, 'data' => $data], 200);
    }

    public static function add_row_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id  = (int) $request->get_param('post_id');
        $layout   = $request->get_param('layout') ?: '1-col';
        $position = (int) $request->get_param('position');
        $row_id   = LZ_Page_Data::add_row($post_id, $layout, $position, 'draft');
        return new \WP_REST_Response(['success' => true, 'node_id' => $row_id], 200);
    }

    public static function add_module_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id     = (int) $request->get_param('post_id');
        $module_slug = $request->get_param('module');
        $parent_id   = $request->get_param('parent_id') ?: '';
        $position    = (int) $request->get_param('position');

        if (empty($module_slug)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Missing parameters.', 'lz-builder')], 400);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_module_accessible($module_slug)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Module not available on your plan.', 'lz-builder')], 403);
        }

        if (empty($parent_id)) {
            $parent_id = LZ_Page_Data::find_last_column($post_id, 'draft');
        }

        $node_id = LZ_Page_Data::add_module($post_id, $module_slug, $parent_id, $position, 'draft');
        return new \WP_REST_Response(['success' => true, 'node_id' => $node_id], 200);
    }

    public static function delete_node_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $node_id = $request->get_param('node_id');
        if (empty($node_id)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node ID required.', 'lz-builder')], 400);
        }
        LZ_Page_Data::delete_node($node_id, $post_id, 'draft');
        return new \WP_REST_Response(['success' => true, 'message' => __('Node deleted.', 'lz-builder')], 200);
    }

    public static function move_node_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id   = (int) $request->get_param('post_id');
        $node_id   = $request->get_param('node_id');
        $parent_id = $request->get_param('parent_id');
        $position  = (int) $request->get_param('position');
        if (empty($node_id)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node ID required.', 'lz-builder')], 400);
        }
        LZ_Page_Data::move_node($node_id, $parent_id, $position, $post_id, 'draft');
        return new \WP_REST_Response(['success' => true, 'message' => __('Node moved.', 'lz-builder')], 200);
    }

    public static function duplicate_node_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $node_id = $request->get_param('node_id');
        if (empty($node_id)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node ID required.', 'lz-builder')], 400);
        }
        $new_id = LZ_Page_Data::duplicate_node($node_id, $post_id, 'draft');
        return new \WP_REST_Response(['success' => true, 'node_id' => $new_id], 200);
    }

    public static function save_settings_rest(\WP_REST_Request $request): \WP_REST_Response {
        $node_id  = $request->get_param('node_id');
        $settings = $request->get_param('settings');
        $post_id  = (int) $request->get_param('post_id');

        if (empty($node_id) || !is_object($settings)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Invalid parameters.', 'lz-builder')], 400);
        }

        $node        = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node not found.', 'lz-builder')], 404);
        }
        $module_slug = $node->module ?? '';
        $module_obj  = LZ_Module_Registry::get_instance()->get_module($module_slug);

        if ($module_obj && is_object($settings)) {
            $form = $module_obj->get_settings_form();
            \LzBuilder\LZ_Settings_Form::register($module_slug, $form);
            $settings = (object) \LzBuilder\LZ_Settings_Form::sanitize_settings((array) $settings, $module_slug);
        }

        $updated = LZ_Page_Data::save_settings($node_id, (object) $settings, $post_id, 'draft');
        return new \WP_REST_Response(['success' => true, 'settings' => $updated], 200);
    }

    public static function render_node_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $node_id = $request->get_param('node_id');

        if (empty($node_id)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node ID required.', 'lz-builder')], 400);
        }

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node not found.', 'lz-builder')], 404);
        }

        if ($node->type !== 'module') {
            $html = '<div class="lz-builder-node lz-node-' . esc_attr($node->type) . '" data-node-id="' . esc_attr($node->node_id) . '"></div>';
            return new \WP_REST_Response(['success' => true, 'html' => $html], 200);
        }

        $module = LZ_Module_Registry::get_instance()->get_module($node->module ?? '');
        if (!$module || !method_exists($module, 'render')) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Module renderer not found.', 'lz-builder')], 500);
        }

        $html = $module->render($node, $node->settings);
        return new \WP_REST_Response(['success' => true, 'html' => $html], 200);
    }

    public static function render_settings_form_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $node_id = $request->get_param('node_id');

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node || 'module' !== $node->type) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node not found.', 'lz-builder')], 404);
        }

        $module_obj = LZ_Module_Registry::get_instance()->get_module($node->module ?? '');
        if (!$module_obj || !method_exists($module_obj, 'get_settings_form')) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Module not found.', 'lz-builder')], 404);
        }

        // Delegate to the shared settings-form HTML builder.
        $form = $module_obj->get_settings_form();
        $current_settings = isset($node->settings) && is_object($node->settings)
            ? (array) $node->settings
            : [];
        $html = self::build_settings_form_html($form, $current_settings, $node_id, $module_obj);

        return new \WP_REST_Response(['success' => true, 'html' => $html, 'node_id' => $node_id], 200);
    }

    /**
     * REST: get-settings-schema
     */
    public static function get_settings_schema_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id = (int) $request->get_param('post_id');
        $node_id = $request->get_param('node_id');

        $node = LZ_Page_Data::get_node($node_id, $post_id, 'draft');
        if (!$node || 'module' !== $node->type) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Node not found.', 'lz-builder')], 404);
        }

        $module_obj = LZ_Module_Registry::get_instance()->get_module($node->module ?? '');
        if (!$module_obj || !method_exists($module_obj, 'get_settings_form')) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Module not found.', 'lz-builder')], 404);
        }

        $current_settings = isset($node->settings) && is_object($node->settings)
            ? (array) $node->settings
            : [];

        $schema = self::build_settings_schema($module_obj, $current_settings, $node);

        return new \WP_REST_Response(['success' => true] + $schema, 200);
    }

    public static function get_templates_rest(\WP_REST_Request $request): \WP_REST_Response {
        $args = [
            'post_type'      => 'lz_template',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $query     = new \WP_Query($args);
        $templates = [];

        foreach ($query->posts as $post) {
            $accessible = true;
            if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
                $accessible = LZ_Subscription_Gate::is_template_accessible($post->ID);
            }
            $templates[] = [
                'id'         => $post->ID,
                'title'      => $post->post_title,
                'thumbnail'  => get_the_post_thumbnail_url($post->ID, 'medium') ?: '',
                'accessible' => $accessible,
                'category'   => get_post_meta($post->ID, '_lz_template_category', true) ?: 'general',
            ];
        }

        return new \WP_REST_Response(['success' => true, 'templates' => $templates], 200);
    }

    public static function apply_template_rest(\WP_REST_Request $request): \WP_REST_Response {
        $post_id     = (int) $request->get_param('post_id');
        $template_id = (int) $request->get_param('template_id');

        if (!$template_id) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Template ID required.', 'lz-builder')], 400);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_template_accessible($template_id)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Template not available on your plan.', 'lz-builder')], 403);
        }

        $template_data = LZ_Page_Data::get_layout_data($template_id);
        if (empty($template_data)) {
            return new \WP_REST_Response(['success' => false, 'message' => __('Template has no data.', 'lz-builder')], 400);
        }

        if (class_exists('LzBuilder\LZ_Subscription_Gate')) {
            $template_data = LZ_Subscription_Gate::filter_available_modules($template_data);
        }

        LZ_Page_Data::update_layout_data($post_id, $template_data, 'draft');
        if (class_exists('LzBuilder\LZ_CSS_Accumulator')) {
            LZ_CSS_Accumulator::clear();
        }
        return new \WP_REST_Response(['success' => true, 'message' => __('Template applied.', 'lz-builder')], 200);
    }

    public static function search_modules_rest(\WP_REST_Request $request): \WP_REST_Response {
        $search  = $request->get_param('search') ?: '';
        $modules = LZ_Module_Registry::get_instance()->get_all_modules();
        $results = [];

        foreach ($modules as $slug => $module) {
            if (class_exists('LzBuilder\LZ_Subscription_Gate') && !LZ_Subscription_Gate::is_module_accessible($slug)) {
                continue;
            }
            $name = $module->get_name();
            $desc = $module->get_description();
            if (!empty($search)) {
                $s = strtolower($search);
                if (strpos(strtolower($name), $s) === false
                    && strpos(strtolower($desc), $s) === false
                    && strpos(strtolower($slug), $s) === false) {
                    continue;
                }
            }
            $results[] = [
                'slug'        => $slug,
                'name'        => $name,
                'description' => $desc,
                'icon'        => $module->get_icon(),
                'category'    => $module->get_category(),
            ];
        }

        return new \WP_REST_Response(['success' => true, 'modules' => $results], 200);
    }
}
