<?php
namespace LzBuilder;

class LZ_Page_Data {
    private static array $cache = [];

    public static array $row_layouts = [
        '1-col'              => [100],
        '2-cols'             => [50, 50],
        '3-cols'             => [33.33, 33.33, 33.33],
        '4-cols'             => [25, 25, 25, 25],
        '5-cols'             => [20, 20, 20, 20, 20],
        '6-cols'             => [16.65, 16.65, 16.65, 16.65, 16.65, 16.65],
        'left-sidebar'       => [33.33, 66.66],
        'right-sidebar'      => [66.66, 33.33],
        'left-right-sidebar' => [25, 50, 25],
    ];

    public static function get_layout_data(int $post_id, string $status = 'published'): array {
        $cache_key = $post_id . '|' . $status;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        $meta_key = 'published' === $status ? '_lz_builder_data' : '_lz_builder_draft';
        $raw = get_post_meta($post_id, $meta_key, true);
        $data = [];
        if ($raw && is_string($raw)) {
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        // Draft fallback: only when no draft meta has ever been saved.
        // An explicitly empty draft ('[]') must not trigger the fallback.
        if ('draft' === $status && '' === $raw) {
            return self::get_layout_data($post_id, 'published');
        }

        self::$cache[$cache_key] = $data;
        return $data;
    }

    public static function has_builder_data(int $post_id): bool {
        $data = self::get_layout_data($post_id);
        return !empty($data);
    }

    public static function get_builder_content(int $post_id, string $status = 'published'): string {
        $data = self::get_layout_data($post_id, $status);
        if (empty($data)) {
            return '';
        }
        $ordered = self::sort_nodes($data);
        $html = '<div class="lz-builder-content">';
        foreach ($ordered as $node) {
            if ('row' === $node['type'] && empty($node['parent_id'])) {
                $html .= self::render_row($node, $data);
            }
        }
        $html .= '</div>';
        return $html;
    }

    private static function sort_nodes(array $data): array {
        $parent_map = [];
        foreach ($data as $node) {
            $pid = $node['parent_id'] ?? '';
            $parent_map[$pid][] = $node;
        }
        $sorted = [];
        $walk = function (array $nodes, int &$pos) use (&$walk, &$sorted, $parent_map) {
            usort($nodes, fn($a, $b) => ($a['position'] ?? 0) - ($b['position'] ?? 0));
            foreach ($nodes as $node) {
                $node['position'] = $pos++;
                $sorted[] = $node;
                $nid = $node['node_id'];
                if (isset($parent_map[$nid])) {
                    $walk($parent_map[$nid], $pos);
                }
            }
        };
        $pos = 0;
        $walk($parent_map[''] ?? [], $pos);
        return $sorted;
    }

    private static function render_row(array $row, array $all_nodes): string {
        $settings = isset($row['settings']) ? (object) $row['settings'] : new \stdClass();
        $width_class = ($settings->width ?? 'fixed') === 'full' ? 'lz-row-full' : 'lz-row-fixed';
        $html = '<div class="lz-row ' . $width_class . '" data-node="' . esc_attr($row['node_id']) . '">';

        // Try column-group → column hierarchy (new format).
        $column_group_children = self::filter_nodes_by_type($all_nodes, 'column-group', $row['node_id']);
        $columns = [];
        foreach ($column_group_children as $group) {
            $group_cols = self::filter_nodes_by_type($all_nodes, 'column', $group['node_id']);
            $columns = array_merge($columns, $group_cols);
        }

        // Fallback: direct column children of the row (old format, no column-group layer).
        if (empty($columns)) {
            $columns = self::filter_nodes_by_type($all_nodes, 'column', $row['node_id']);
        }

        foreach ($columns as $col) {
            $html .= '<div class="lz-col-group">';
            $col_settings = isset($col['settings']) ? (object) $col['settings'] : new \stdClass();
            $size = $col_settings->size ?? $col_settings->width ?? 100;
            $html .= '<div class="lz-column lz-col-' . intval($size) . '" data-node="' . esc_attr($col['node_id']) . '" style="width:' . floatval($size) . '%">';
            $modules = self::filter_nodes_by_type($all_nodes, 'module', $col['node_id']);
            foreach ($modules as $mod) {
                $html .= self::render_module_node($mod);
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    private static function render_module_node(array $module_node): string {
        $module_slug = $module_node['module'] ?? '';
        if (!$module_slug) return '';
        if (!class_exists('LzBuilder\LZ_Module_Registry')) return '';
        $module_obj = LZ_Module_Registry::get_instance()->get_module($module_slug);
        if (!$module_obj) return '';
        $node = (object) $module_node;
        $settings = isset($module_node['settings']) ? (object) $module_node['settings'] : new \stdClass();
        $html = $module_obj->render($node, $settings);

        if (false === strpos($html, 'data-node-id=')) {
            $html = preg_replace(
                '/<(\w+)([^>]*)>/',
                '<$1$2 data-node-id="' . esc_attr($node->node_id) . '">',
                $html,
                1
            );
        }
        return $html;
    }

    private static function filter_nodes_by_type(array $data, string $type, ?string $parent_id = null): array {
        return array_filter($data, function ($node) use ($type, $parent_id) {
            if (($node['type'] ?? '') !== $type) return false;
            if ($parent_id !== null && ($node['parent_id'] ?? '') !== $parent_id) return false;
            return true;
        });
    }

    public static function update_layout_data(int $post_id, array $data, string $status = 'published'): void {
        $meta_key = 'published' === $status ? '_lz_builder_data' : '_lz_builder_draft';
        $encoded = wp_json_encode($data, JSON_UNESCAPED_UNICODE);
        $saved = update_post_meta($post_id, $meta_key, wp_slash($encoded));
        $cache_key = $post_id . '|' . $status;
        self::$cache[$cache_key] = $data;
    }

    public static function delete_layout_data(int $post_id): void {
        delete_post_meta($post_id, '_lz_builder_data');
        delete_post_meta($post_id, '_lz_builder_draft');
        unset(self::$cache[$post_id . '|published'], self::$cache[$post_id . '|draft']);
    }

    public static function get_node(string $node_id, int $post_id, string $status = 'published'): ?\stdClass {
        $data = self::get_layout_data($post_id, $status);
        foreach ($data as $node) {
            if (!isset($node['node_id'])) {
                continue;
            }
            if ($node['node_id'] === $node_id) {
                return self::merge_node_defaults((object) $node);
            }
        }
        return null;
    }

    public static function get_nodes_by_type(string $type, ?string $parent_id = null, int $post_id = 0, string $status = 'published'): array {
        $data = $post_id > 0 ? self::get_layout_data($post_id, $status) : self::get_current_data();
        $nodes = [];
        foreach ($data as $node) {
            if (!isset($node['type'], $node['node_id'])) {
                continue;
            }
            if ($node['type'] !== $type) {
                continue;
            }
            if (null !== $parent_id) {
                $node_parent = $node['parent_id'] ?? null;
                if ($node_parent !== $parent_id) {
                    continue;
                }
            }
            $nodes[] = (object) $node;
        }
        usort($nodes, function ($a, $b) {
            $pos_a = isset($a->position) ? (int) $a->position : 0;
            $pos_b = isset($b->position) ? (int) $b->position : 0;
            return $pos_a <=> $pos_b;
        });
        return $nodes;
    }

    public static function get_categorized_nodes(int $post_id = 0): array {
        $data = $post_id > 0 ? self::get_layout_data($post_id) : self::get_current_data();
        $result = ['rows' => [], 'columns' => [], 'modules' => []];
        foreach ($data as $node) {
            if (!isset($node['type'], $node['node_id'])) {
                continue;
            }
            $type = $node['type'];
            if (isset($result[$type])) {
                $result[$type][] = (object) $node;
            }
        }
        return $result;
    }

    public static function add_node(array $args, string $status = 'draft'): string {
        $defaults = [
            'node_id'   => self::generate_node_id(),
            'type'      => 'module',
            'parent_id' => null,
            'position'  => 0,
            'module'    => '',
            'settings'  => new \stdClass(),
        ];
        $node = array_merge($defaults, $args);
        $node['node_id'] = $node['node_id'] ?? self::generate_node_id();
        $data = self::get_current_data($status);
        $data[] = $node;
        self::save_current_data($data, $status);
        return $node['node_id'];
    }

    public static function delete_node(string $node_id, int $post_id = 0, string $status = 'draft'): void {
        $data = $post_id > 0 ? self::get_layout_data($post_id, $status) : self::get_current_data($status);
        $to_remove = [$node_id];
        $children = self::get_child_ids($data, $node_id);
        $to_remove = array_merge($to_remove, $children);
        $data = array_values(array_filter($data, function ($node) use ($to_remove) {
            return isset($node['node_id']) && !in_array($node['node_id'], $to_remove, true);
        }));
        if ($post_id > 0) {
            self::update_layout_data($post_id, $data, $status);
        } else {
            self::save_current_data($data, $status);
        }
    }

    public static function move_node(string $node_id, string $new_parent_id, int $new_position, int $post_id = 0, string $status = 'draft'): void {
        $data = $post_id > 0 ? self::get_layout_data($post_id, $status) : self::get_current_data($status);
        foreach ($data as &$node) {
            if (isset($node['node_id']) && $node['node_id'] === $node_id) {
                $node['parent_id'] = $new_parent_id;
                $node['position'] = $new_position;
                break;
            }
        }
        unset($node);
        if ($post_id > 0) {
            self::update_layout_data($post_id, $data, $status);
        } else {
            self::save_current_data($data, $status);
        }
    }

    public static function duplicate_node(string $node_id, int $post_id = 0, string $status = 'draft'): string {
        $data = $post_id > 0 ? self::get_layout_data($post_id, $status) : self::get_current_data($status);
        $node_map = [];
        $new_root_id = '';

        $flat = [];
        self::collect_subtree($data, $node_id, $flat);

        foreach ($flat as $original) {
            $old_id = $original['node_id'];
            $new_id = self::generate_node_id();
            $node_map[$old_id] = $new_id;

            $new_node = $original;
            $new_node['node_id'] = $new_id;
            if ($new_node['parent_id'] && isset($node_map[$new_node['parent_id']])) {
                $new_node['parent_id'] = $node_map[$new_node['parent_id']];
            }

            if ($old_id === $node_id) {
                $new_root_id = $new_id;
                $max_pos = 0;
                $clone_parent = $original['parent_id'] ?? '';
                foreach ($data as $n) {
                    if (($n['parent_id'] ?? '') === $clone_parent && isset($n['position'])) {
                        $max_pos = max($max_pos, (int) $n['position']);
                    }
                }
                $new_node['position'] = $max_pos + 1;
            }

            $data[] = $new_node;
        }

        if ($post_id > 0) {
            self::update_layout_data($post_id, $data, $status);
        } else {
            self::save_current_data($data, $status);
        }
        return $new_root_id;
    }

    public static function generate_node_id(): string {
        return 'lz_node_' . wp_generate_uuid4();
    }

    public static function add_row(int $post_id, string $layout = '1-col', int $position = 0, string $status = 'draft'): string {
        if (!isset(self::$row_layouts[$layout])) {
            $layout = '1-col';
        }
        $widths = self::$row_layouts[$layout];
        $data = self::get_layout_data($post_id, $status);
        $row_id = self::generate_node_id();
        $group_id = self::generate_node_id();

        $row = [
            'node_id'   => $row_id,
            'type'      => 'row',
            'parent_id' => null,
            'position'  => $position,
            'module'    => '',
            'settings'  => ['layout' => $layout],
        ];
        $data[] = $row;

        $group = [
            'node_id'   => $group_id,
            'type'      => 'column-group',
            'parent_id' => $row_id,
            'position'  => 0,
            'module'    => '',
            'settings'  => [],
        ];
        $data[] = $group;

        foreach ($widths as $index => $width) {
            $col_id = self::generate_node_id();
            $col = [
                'node_id'   => $col_id,
                'type'      => 'column',
                'parent_id' => $group_id,
                'position'  => $index,
                'module'    => '',
                'settings'  => ['size' => $width],
            ];
            $data[] = $col;
        }

        self::update_layout_data($post_id, $data, $status);
        return $row_id;
    }

    public static function add_module(int $post_id, string $module_slug, string $parent_id, int $position = 0, string $status = 'draft'): string {
        $data = self::get_layout_data($post_id, $status);

        // Check whether any column nodes exist at all.
        $has_columns = false;
        foreach ($data as $node) {
            if (($node['type'] ?? '') === 'column') {
                $has_columns = true;
                break;
            }
        }

        // Auto-create a 1-col row when no columns exist.
        if (!$has_columns) {
            $row_id = self::add_row($post_id, '1-col', 0, $status);
            $data = self::get_layout_data($post_id, $status);
            $parent_id = '';

            foreach ($data as $node) {
                if (($node['type'] ?? '') === 'column' && ($node['parent_id'] ?? '') !== '') {
                    $parent_id = $node['node_id'];
                    break;
                }
            }
        }

        $node_id = self::generate_node_id();
        $defaults = self::get_module_default_settings($module_slug);

        // Auto-position: count existing siblings and append after them.
        if ($position <= 0 && !empty($parent_id)) {
            $sibling_count = 0;
            foreach ($data as $n) {
                if (($n['type'] ?? '') === 'module' && ($n['parent_id'] ?? '') === $parent_id) {
                    $sibling_count++;
                }
            }
            $position = $sibling_count + 1;
        }

        $node = [
            'node_id'   => $node_id,
            'type'      => 'module',
            'parent_id' => $parent_id,
            'position'  => $position,
            'module'    => $module_slug,
            'settings'  => $defaults,
        ];
        $data[] = $node;
        self::update_layout_data($post_id, $data, $status);
        return $node_id;
    }

    /**
     * Find the last column node ID, walking rows→column-groups→columns
     * in document order so placement is predictable.
     */
    public static function find_last_column(int $post_id, string $status = 'draft'): string {
        $data   = self::get_layout_data($post_id, $status);
        $ordered = self::sort_nodes($data);

        // columns with data-node-id attribute returned by serve calls
        $last_column = '';

        // The last column rendered in document order is in the
        // last row's last column-group's last column.
        // Walk the ordered output backwards since it's a tree walk
        // and columns are leaves — the last column node in sorted
        // order is the rightmost leaf.
        foreach (array_reverse($ordered) as $node) {
            if (($node['type'] ?? '') === 'column') {
                $last_column = $node['node_id'];
                break;
            }
        }
        return $last_column;
    }

    public static function get_node_settings(string $node_id, int $post_id = 0): ?\stdClass {
        $data = $post_id > 0 ? self::get_layout_data($post_id) : self::get_current_data();
        foreach ($data as $node) {
            if (!isset($node['node_id'])) {
                continue;
            }
            if ($node['node_id'] === $node_id) {
                return self::merge_node_defaults((object) $node)->settings;
            }
        }
        return null;
    }

    public static function save_settings(string $node_id, \stdClass $new_settings, int $post_id = 0, string $status = 'draft'): \stdClass {
        $data = $post_id > 0 ? self::get_layout_data($post_id, $status) : self::get_current_data($status);
        $updated = false;
        $merged = $new_settings;
        foreach ($data as &$node) {
            if (!isset($node['node_id']) || $node['node_id'] !== $node_id) {
                continue;
            }
            $node_obj = (object) $node;
            $current_settings = isset($node['settings']) ? (object) $node['settings'] : new \stdClass();
            $merged = (object) array_merge((array) $current_settings, (array) $new_settings);
            $module_slug = $node['module'] ?? '';
            if ($module_slug && class_exists('LzBuilder\LZ_Module_Registry')) {
                $module_obj = LZ_Module_Registry::get_instance()->get_module($module_slug);
                if ($module_obj && method_exists($module_obj, 'update')) {
                    $merged = $module_obj->update($merged);
                }
            }
            $node['settings'] = (array) $merged;
            $updated = true;
            break;
        }
        unset($node);
        if ($updated) {
            if ($post_id > 0) {
                self::update_layout_data($post_id, $data, $status);
            } else {
                self::save_current_data($data, $status);
            }
        }
        return $merged ?? $new_settings;
    }

    public static function validate_node_tree(array $nodes): array {
        if (!class_exists('LzBuilder\LZ_Subscription_Gate')) {
            return $nodes;
        }
        return array_values(array_filter($nodes, function ($node) {
            if (!is_array($node) || !isset($node['type'], $node['node_id'])) {
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

    public static function get_layout_from_content(int $post_id): string {
        $data = self::get_layout_data($post_id);
        if (empty($data)) {
            $post = get_post($post_id);
            return $post ? $post->post_content : '';
        }
        return '[lz_builder_layout post_id="' . $post_id . '"]';
    }

    private static function merge_node_defaults(\stdClass $node): \stdClass {
        if (!isset($node->type) || 'module' !== $node->type) {
            $node->settings = isset($node->settings) && is_array($node->settings)
                ? (object) $node->settings
                : ($node->settings ?? new \stdClass());
            return $node;
        }
        $module_slug = $node->module ?? '';
        $saved = isset($node->settings) && is_array($node->settings)
            ? $node->settings
            : ($node->settings ?? []);
        $defaults = self::get_module_default_settings($module_slug);
        $node->settings = (object) array_merge($defaults, $saved);
        return $node;
    }

    private static function get_module_default_settings(string $module_slug): array {
        if (!class_exists('LzBuilder\LZ_Module_Registry')) {
            return [];
        }
        $module_obj = LZ_Module_Registry::get_instance()->get_module($module_slug);
        if (!$module_obj || !method_exists($module_obj, 'get_settings_form')) {
            return [];
        }
        $form = $module_obj->get_settings_form();
        $defaults = [];
        self::extract_defaults_from_form($form, $defaults);
        return $defaults;
    }

    private static function extract_defaults_from_form(array $form, array &$defaults): void {
        foreach ($form as $tab) {
            if (!isset($tab['sections'])) {
                continue;
            }
            foreach ($tab['sections'] as $section) {
                if (!isset($section['fields'])) {
                    continue;
                }
                foreach ($section['fields'] as $field_key => $field) {
                    if (is_array($field) && array_key_exists('default', $field)) {
                        $defaults[$field_key] = $field['default'];
                    } elseif (is_array($field) && isset($field['fields'])) {
                        self::extract_defaults_from_form($field['fields'], $defaults);
                    }
                }
            }
        }
    }

    private static function get_current_data(string $status = 'draft'): array {
        global $post;
        if (isset($post) && $post instanceof \WP_Post) {
            return self::get_layout_data($post->ID, $status);
        }
        $backtrace = debug_backtrace(0, 5);
        foreach ($backtrace as $frame) {
            if (isset($frame['args'][0]) && (is_numeric($frame['args'][0]) || $frame['args'][0] instanceof \WP_Post)) {
                $pid = $frame['args'][0] instanceof \WP_Post ? $frame['args'][0]->ID : (int) $frame['args'][0];
                return self::get_layout_data($pid, $status);
            }
        }
        return [];
    }

    private static function save_current_data(array $data, string $status = 'draft'): void {
        global $post;
        if (isset($post) && $post instanceof \WP_Post) {
            self::update_layout_data($post->ID, $data, $status);
            return;
        }
        $backtrace = debug_backtrace(0, 5);
        foreach ($backtrace as $frame) {
            if (isset($frame['args'][0]) && is_numeric($frame['args'][0])) {
                self::update_layout_data((int) $frame['args'][0], $data, $status);
                return;
            }
        }
    }

    private static function get_child_ids(array $data, string $parent_id): array {
        $ids = [];
        foreach ($data as $node) {
            if (!isset($node['node_id'], $node['parent_id'])) {
                continue;
            }
            if ($node['parent_id'] === $parent_id) {
                $ids[] = $node['node_id'];
                $grandchildren = self::get_child_ids($data, $node['node_id']);
                $ids = array_merge($ids, $grandchildren);
            }
        }
        return $ids;
    }

    private static function collect_subtree(array $data, string $root_id, array &$collected): void {
        foreach ($data as $node) {
            if (!isset($node['node_id'])) {
                continue;
            }
            if ($node['node_id'] === $root_id) {
                $collected[] = $node;
                $children = self::get_child_ids($data, $root_id);
                foreach ($children as $child_id) {
                    self::collect_subtree($data, $child_id, $collected);
                }
                return;
            }
        }
    }
}
