<?php
namespace LzBuilder;

class LZ_CSS_Accumulator {

    const BREAKPOINT_DEFAULT = 'default';
    const BREAKPOINT_TABLET = 'tablet';
    const BREAKPOINT_PHONE = 'phone';

    const MEDIA_QUERIES = [
        'default' => '',
        'tablet'  => '@media (max-width: 768px)',
        'phone'   => '@media (max-width: 480px)',
    ];

    private static array $rules = [];

    public static function add_rule(string $selector, string $property, string $value, string $breakpoint = 'default'): void {
        if (empty($selector) || empty($property)) {
            return;
        }
        if (!isset(self::$rules[$breakpoint])) {
            self::$rules[$breakpoint] = [];
        }
        if (!isset(self::$rules[$breakpoint][$selector])) {
            self::$rules[$breakpoint][$selector] = [];
        }
        self::$rules[$breakpoint][$selector][$property] = $value;
    }

    public static function add_rules(string $selector, array $props, string $breakpoint = 'default'): void {
        foreach ($props as $property => $value) {
            if (is_numeric($property) && is_string($value)) {
                $parts = explode(':', $value, 2);
                if (count($parts) === 2) {
                    self::add_rule($selector, trim($parts[0]), trim($parts[1]), $breakpoint);
                }
                continue;
            }
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            self::add_rule($selector, $property, (string) $value, $breakpoint);
        }
    }

    public static function border(string $selector, \stdClass $settings, string $key = 'border', string $breakpoint = 'default'): void {
        $prefix = $key . '_';
        $style  = $settings->{$prefix . 'style'} ?? '';
        $width  = $settings->{$prefix . 'width'} ?? '';
        $color  = $settings->{$prefix . 'color'} ?? '';

        if (!empty($style)) {
            self::add_rule($selector, 'border-style', $style, $breakpoint);
        }
        if ($width !== '' && $width !== null && $width !== false) {
            $unit = $settings->{$prefix . 'width_unit'} ?? 'px';
            self::add_rule($selector, 'border-width', $width . $unit, $breakpoint);
        }
        if (!empty($color)) {
            self::add_rule($selector, 'border-color', $color, $breakpoint);
        }

        $radius_tl = $settings->{$prefix . 'radius_top_left'} ?? '';
        $radius_tr = $settings->{$prefix . 'radius_top_right'} ?? '';
        $radius_br = $settings->{$prefix . 'radius_bottom_right'} ?? '';
        $radius_bl = $settings->{$prefix . 'radius_bottom_left'} ?? '';
        $radius    = $settings->{$prefix . 'radius'} ?? '';

        if (!empty($radius)) {
            $unit = $settings->{$prefix . 'radius_unit'} ?? 'px';
            self::add_rule($selector, 'border-radius', $radius . $unit, $breakpoint);
        } elseif (!empty($radius_tl) || !empty($radius_tr) || !empty($radius_br) || !empty($radius_bl)) {
            $unit = $settings->{$prefix . 'radius_unit'} ?? 'px';
            $vals = [
                !empty($radius_tl) ? $radius_tl . $unit : '0',
                !empty($radius_tr) ? $radius_tr . $unit : '0',
                !empty($radius_br) ? $radius_br . $unit : '0',
                !empty($radius_bl) ? $radius_bl . $unit : '0',
            ];
            self::add_rule($selector, 'border-radius', implode(' ', $vals), $breakpoint);
        }
    }

    public static function typography(string $selector, \stdClass $settings, string $key = 'typography', string $breakpoint = 'default'): void {
        $prefix = $key . '_';

        $font_family = $settings->{$prefix . 'font_family'} ?? '';
        $font_weight = $settings->{$prefix . 'font_weight'} ?? '';
        $font_size   = $settings->{$prefix . 'font_size'} ?? '';
        $line_height = $settings->{$prefix . 'line_height'} ?? '';
        $letter_spacing = $settings->{$prefix . 'letter_spacing'} ?? '';
        $text_align = $settings->{$prefix . 'text_align'} ?? '';
        $text_transform = $settings->{$prefix . 'text_transform'} ?? '';
        $text_decoration = $settings->{$prefix . 'text_decoration'} ?? '';
        $font_style = $settings->{$prefix . 'font_style'} ?? '';
        $color      = $settings->{$prefix . 'color'} ?? '';

        if (!empty($font_family)) {
            self::add_rule($selector, 'font-family', $font_family, $breakpoint);
        }
        if (!empty($font_weight)) {
            self::add_rule($selector, 'font-weight', $font_weight, $breakpoint);
        }
        if ($font_size !== '' && $font_size !== null && $font_size !== false) {
            $unit = $settings->{$prefix . 'font_size_unit'} ?? 'px';
            self::add_rule($selector, 'font-size', $font_size . $unit, $breakpoint);
        }
        if ($line_height !== '' && $line_height !== null && $line_height !== false) {
            $unit = $settings->{$prefix . 'line_height_unit'} ?? '';
            self::add_rule($selector, 'line-height', $line_height . $unit, $breakpoint);
        }
        if ($letter_spacing !== '' && $letter_spacing !== null && $letter_spacing !== false) {
            $unit = $settings->{$prefix . 'letter_spacing_unit'} ?? 'px';
            self::add_rule($selector, 'letter-spacing', $letter_spacing . $unit, $breakpoint);
        }
        if (!empty($text_align)) {
            self::add_rule($selector, 'text-align', $text_align, $breakpoint);
        }
        if (!empty($text_transform)) {
            self::add_rule($selector, 'text-transform', $text_transform, $breakpoint);
        }
        if (!empty($text_decoration)) {
            self::add_rule($selector, 'text-decoration', $text_decoration, $breakpoint);
        }
        if (!empty($font_style)) {
            self::add_rule($selector, 'font-style', $font_style, $breakpoint);
        }
        if (!empty($color)) {
            self::add_rule($selector, 'color', $color, $breakpoint);
        }
    }

    public static function dimension(string $selector, \stdClass $settings, string $key, array $props = ['top', 'right', 'bottom', 'left'], string $breakpoint = 'default'): void {
        $prefix = $key . '_';
        $unit   = $settings->{$prefix . 'unit'} ?? 'px';
        $linked = $settings->{$prefix . 'linked'} ?? false;

        if ($linked) {
            $value = $settings->{$prefix . $props[0]} ?? '';
            if ($value !== '' && $value !== null && $value !== false) {
                self::add_rule($selector, $key, $value . $unit, $breakpoint);
            }
            return;
        }

        $values = [];
        $all_empty = true;
        foreach ($props as $prop) {
            $val = $settings->{$prefix . $prop} ?? '';
            $values[] = $val;
            if ($val !== '' && $val !== null && $val !== false) {
                $all_empty = false;
            }
        }

        if ($all_empty) {
            return;
        }

        if (count($values) === 4) {
            $shorthand = implode(' ', array_map(function ($v) use ($unit) {
                return ($v !== '' && $v !== null && $v !== false) ? $v . $unit : '0';
            }, $values));
            self::add_rule($selector, $key, $shorthand, $breakpoint);
        } else {
            $property_map = [
                'top'    => $key . '-top',
                'right'  => $key . '-right',
                'bottom' => $key . '-bottom',
                'left'   => $key . '-left',
            ];
            foreach ($props as $index => $prop) {
                $val = $values[$index];
                if ($val !== '' && $val !== null && $val !== false && isset($property_map[$prop])) {
                    self::add_rule($selector, $property_map[$prop], $val . $unit, $breakpoint);
                }
            }
        }
    }

    /**
     * Build dimension CSS as an inline style string for frontend renderers.
     */
    public static function build_dimension_inline(\stdClass $settings, string $key): string {
        $prefix = $key . '_';
        $unit   = $settings->{$prefix . 'unit'} ?? 'px';
        // Fallback for old per-side unit format (e.g. margin_top_unit → margin_unit).
        if (!isset($settings->{$prefix . 'unit'})) {
            foreach (['top', 'right', 'bottom', 'left'] as $fallback_side) {
                if (isset($settings->{$prefix . $fallback_side . '_unit'})) {
                    $unit = $settings->{$prefix . $fallback_side . '_unit'} ?? 'px';
                    break;
                }
            }
        }
        $linked = $settings->{$prefix . 'linked'} ?? false;

        // Fallback for old vertical/horizontal format (e.g. padding_vertical → padding_top + padding_bottom).
        $has_sides = isset($settings->{$prefix . 'top'}) || isset($settings->{$prefix . 'right'})
                   || isset($settings->{$prefix . 'bottom'}) || isset($settings->{$prefix . 'left'});
        if (!$has_sides) {
            $v = $settings->{$prefix . 'vertical'} ?? '';
            $h = $settings->{$prefix . 'horizontal'} ?? '';
            $style = '';
            if ($v !== '' && $v !== null && $v !== false) {
                $style .= $key . '-top:' . $v . $unit . ';' . $key . '-bottom:' . $v . $unit . ';';
            }
            if ($h !== '' && $h !== null && $h !== false) {
                $style .= $key . '-left:' . $h . $unit . ';' . $key . '-right:' . $h . $unit . ';';
            }
            return $style;
        }

        if ($linked) {
            $value = $settings->{$prefix . 'top'} ?? '';
            if ($value !== '' && $value !== null && $value !== false) {
                return $key . ':' . $value . $unit . ';';
            }
            return '';
        }

        $style = '';
        $sides = ['top', 'right', 'bottom', 'left'];
        foreach ($sides as $side) {
            $val = $settings->{$prefix . $side} ?? '';
            if ($val !== '' && $val !== null && $val !== false) {
                $style .= $key . '-' . $side . ':' . $val . $unit . ';';
            }
        }
        return $style;
    }

    /**
     * Build border CSS as an inline style string for frontend renderers.
     */
    public static function build_border_inline(\stdClass $settings, string $prefix = 'border'): string {
        $style = '';
        $k = $prefix . '_';

        $border_style = $settings->{$k . 'style'} ?? '';
        if (!empty($border_style)) {
            $style .= 'border-style:' . $border_style . ';';
        }

        $width = $settings->{$k . 'width'} ?? '';
        if ($width !== '' && $width !== null && $width !== false) {
            $unit = $settings->{$k . 'width_unit'} ?? 'px';
            $style .= 'border-width:' . $width . $unit . ';';
        }

        $color = $settings->{$k . 'color'} ?? '';
        if (!empty($color)) {
            $style .= 'border-color:' . $color . ';';
        }

        $radius = $settings->{$k . 'radius'} ?? '';
        if ($radius !== '' && $radius !== null && $radius !== false) {
            $unit = $settings->{$k . 'radius_unit'} ?? 'px';
            $style .= 'border-radius:' . $radius . $unit . ';';
        }

        return $style;
    }

    public static function render(): string {
        $output = '';
        $breakpoints = [self::BREAKPOINT_DEFAULT, self::BREAKPOINT_TABLET, self::BREAKPOINT_PHONE];

        foreach ($breakpoints as $breakpoint) {
            if (!isset(self::$rules[$breakpoint]) || empty(self::$rules[$breakpoint])) {
                continue;
            }

            $media = self::MEDIA_QUERIES[$breakpoint] ?? '';
            $block = '';

            foreach (self::$rules[$breakpoint] as $selector => $props) {
                $declarations = [];
                foreach ($props as $property => $value) {
                    $declarations[] = "\t{$property}: {$value};";
                }
                $block .= "{$selector} {\n" . implode("\n", $declarations) . "\n}\n";
            }

            if ($media) {
                $output .= "{$media} {\n{$block}}\n\n";
            } else {
                $output .= $block . "\n";
            }
        }

        return $output;
    }

    public static function render_responsive(string $selector, \stdClass $settings, array $props): void {
        foreach ($props as $prop) {
            $default = $settings->{$prop} ?? '';
            $medium  = $settings->{$prop . '_responsive_medium'} ?? '';
            $small   = $settings->{$prop . '_responsive_small'} ?? '';

            if ($default !== '' && $default !== null && $default !== false) {
                self::add_rule($selector, $prop, $default, self::BREAKPOINT_DEFAULT);
            }
            if ($medium !== '' && $medium !== null && $medium !== false) {
                self::add_rule($selector, $prop, $medium, self::BREAKPOINT_TABLET);
            }
            if ($small !== '' && $small !== null && $small !== false) {
                self::add_rule($selector, $prop, $small, self::BREAKPOINT_PHONE);
            }
        }
    }

    public static function clear(): void {
        self::$rules = [];
    }

    public static function render_file(int $post_id, string $css): string {
        $upload_dir = wp_upload_dir();
        $cache_dir  = trailingslashit($upload_dir['basedir']) . 'lz-builder/cache';

        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }

        $file = trailingslashit($cache_dir) . $post_id . '.css';
        $handle = fopen($file, 'w');
        if ($handle) {
            fwrite($handle, $css);
            fclose($handle);
        }

        return self::get_cache_url($post_id);
    }

    public static function get_cache_url(int $post_id): string {
        $upload_dir = wp_upload_dir();
        $cache_url  = trailingslashit($upload_dir['baseurl']) . 'lz-builder/cache/';

        if (is_ssl()) {
            $cache_url = str_replace('http://', 'https://', $cache_url);
        }

        return $cache_url . $post_id . '.css';
    }

    public static function get_rules(): array {
        return self::$rules;
    }
}
