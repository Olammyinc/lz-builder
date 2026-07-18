<?php
$text = !empty($settings->text) ? wp_kses_post($settings->text) : '';
$color = !empty($settings->color) ? $settings->color : '';
$node_class = 'lz-node-' . $node->node_id;

// Build inline style string.
$style = '';
if (!empty($color)) {
    $style .= 'color:' . esc_attr($color) . ';';
}

// Typography — handle both nested and flat formats.
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
?>
<div class="lz-text-editor <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($style); ?>">
    <?php echo $text; ?>
</div>
