<?php
$tag = !empty($settings->tag) ? $settings->tag : 'h2';
$text = !empty($settings->text) ? $settings->text : 'Hello World';
$alignment = !empty($settings->alignment) ? $settings->alignment : 'left';
$color = !empty($settings->color) ? $settings->color : '';
$node_class = 'lz-node-' . $node->node_id;

// Build inline style string from all relevant settings.
$style = 'text-align:' . esc_attr($alignment) . ';';
if (!empty($color)) {
    $style .= 'color:' . esc_attr($color) . ';';
}

// Typography — handle flat key format (typography_font_family).
$typo_prefix = 'typography_';
$font_family    = $settings->{$typo_prefix . 'font_family'} ?? '';
$font_weight    = $settings->{$typo_prefix . 'font_weight'} ?? '';
$font_size      = $settings->{$typo_prefix . 'font_size'} ?? '';
$font_size_unit = $settings->{$typo_prefix . 'font_size_unit'} ?? 'px';
$line_height    = $settings->{$typo_prefix . 'line_height'} ?? '';
$line_height_unit = $settings->{$typo_prefix . 'line_height_unit'} ?? '';
$text_transform  = $settings->{$typo_prefix . 'text_transform'} ?? '';
$letter_spacing  = $settings->{$typo_prefix . 'letter_spacing'} ?? '';
$letter_spacing_unit = $settings->{$typo_prefix . 'letter_spacing_unit'} ?? 'px';

if (!empty($font_family)) {
    $style .= 'font-family:' . esc_attr($font_family) . ';';
}
if (!empty($font_weight)) {
    $style .= 'font-weight:' . esc_attr($font_weight) . ';';
}
if ($font_size !== '' && $font_size !== null && $font_size !== false) {
    $style .= 'font-size:' . esc_attr($font_size . $font_size_unit) . ';';
}
if ($line_height !== '' && $line_height !== null && $line_height !== false) {
    $style .= 'line-height:' . esc_attr($line_height . $line_height_unit) . ';';
}
if (!empty($text_transform)) {
    $style .= 'text-transform:' . esc_attr($text_transform) . ';';
}
if ($letter_spacing !== '' && $letter_spacing !== null && $letter_spacing !== false) {
    $style .= 'letter-spacing:' . esc_attr($letter_spacing . $letter_spacing_unit) . ';';
}

$style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'margin');
$style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'padding');
$style .= \LzBuilder\LZ_CSS_Accumulator::build_border_inline($settings, 'border');
?>
<<?php echo tag_escape($tag); ?> class="lz-heading <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($style); ?>">
    <?php echo esc_html($text); ?>
</<?php echo tag_escape($tag); ?>>
