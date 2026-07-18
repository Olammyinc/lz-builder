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

// Typography — handle both nested (typography->font_family) and flat (typography_font_family) formats.
$typo = isset($settings->typography) && is_object($settings->typography) ? $settings->typography : null;
if ($typo) {
    $font_family   = $typo->font_family ?? '';
    $font_weight   = $typo->font_weight ?? '';
    $font_size     = $typo->font_size ?? '';
    $font_size_unit     = $typo->font_size_unit ?? 'px';
    $line_height   = $typo->line_height ?? '';
    $line_height_unit   = $typo->line_height_unit ?? '';
    $text_transform     = $typo->text_transform ?? '';
    $letter_spacing     = $typo->letter_spacing ?? '';
    $letter_spacing_unit = $typo->letter_spacing_unit ?? 'px';
} else {
    // Fallback to flat keys.
    $font_family   = $settings->typography_font_family ?? '';
    $font_weight   = $settings->typography_font_weight ?? '';
    $font_size     = $settings->typography_font_size ?? '';
    $font_size_unit     = $settings->typography_font_size_unit ?? 'px';
    $line_height   = $settings->typography_line_height ?? '';
    $line_height_unit   = $settings->typography_line_height_unit ?? '';
    $text_transform     = $settings->typography_text_transform ?? '';
    $letter_spacing     = $settings->typography_letter_spacing ?? '';
    $letter_spacing_unit = $settings->typography_letter_spacing_unit ?? 'px';
}

if (!empty($font_family)) {
    $style .= 'font-family:' . esc_attr($font_family) . ';';
}
if (!empty($font_weight)) {
    $style .= 'font-weight:' . esc_attr($font_weight) . ';';
}
if ($font_size !== '' && $font_size !== null) {
    $style .= 'font-size:' . esc_attr($font_size . $font_size_unit) . ';';
}
if ($line_height !== '' && $line_height !== null) {
    $style .= 'line-height:' . esc_attr($line_height . $line_height_unit) . ';';
}
if (!empty($text_transform)) {
    $style .= 'text-transform:' . esc_attr($text_transform) . ';';
}
if ($letter_spacing !== '' && $letter_spacing !== null) {
    $style .= 'letter-spacing:' . esc_attr($letter_spacing . $letter_spacing_unit) . ';';
}

// Margin top.
if (isset($settings->margin_top) && $settings->margin_top !== '') {
    $margin_top_unit = $settings->margin_top_unit ?? 'px';
    $style .= 'margin-top:' . esc_attr($settings->margin_top . $margin_top_unit) . ';';
}
// Margin bottom.
if (isset($settings->margin_bottom) && $settings->margin_bottom !== '') {
    $margin_bottom_unit = $settings->margin_bottom_unit ?? 'px';
    $style .= 'margin-bottom:' . esc_attr($settings->margin_bottom . $margin_bottom_unit) . ';';
}
?>
<<?php echo tag_escape($tag); ?> class="lz-heading <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($style); ?>">
    <?php echo esc_html($text); ?>
</<?php echo tag_escape($tag); ?>>
