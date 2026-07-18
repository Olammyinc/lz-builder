<?php
$text = !empty($settings->text) ? $settings->text : 'Click Here';
$style_type = !empty($settings->style) ? $settings->style : 'flat';
$size = !empty($settings->size) ? $settings->size : 'medium';
$width = !empty($settings->width) ? $settings->width : 'auto';
$alignment = !empty($settings->alignment) ? $settings->alignment : 'center';
$bg = !empty($settings->background_color) ? $settings->background_color : '#007cba';
$text_color = !empty($settings->text_color) ? $settings->text_color : '#ffffff';
$border_radius = isset($settings->border_radius) && $settings->border_radius !== '' ? $settings->border_radius : 4;
$padding_v = isset($settings->padding_vertical) && $settings->padding_vertical !== '' ? $settings->padding_vertical : 10;
$padding_h = isset($settings->padding_horizontal) && $settings->padding_horizontal !== '' ? $settings->padding_horizontal : 20;
$node_class = 'lz-node-' . $node->node_id;

$link_url = '';
$link_target = '';
// Handle both nested (link->url) and flat (link_url) link formats.
if (!empty($settings->link)) {
    $link = is_object($settings->link) ? $settings->link : (is_array($settings->link) ? (object) $settings->link : new \stdClass());
    $link_url = $link->url ?? ($settings->link_url ?? '');
    $link_target = $link->target ?? ($settings->link_target ?? '');
} elseif (!empty($settings->link_url)) {
    $link_url = $settings->link_url;
    $link_target = $settings->link_target ?? '';
}

$border_radius_unit = isset($settings->border_radius_unit) ? $settings->border_radius_unit : 'px';
$padding_v_unit = isset($settings->padding_vertical_unit) ? $settings->padding_vertical_unit : 'px';
$padding_h_unit = isset($settings->padding_horizontal_unit) ? $settings->padding_horizontal_unit : 'px';

$btn_style = '';
if ($style_type === 'flat' || empty($style_type)) {
    $btn_style .= 'background:' . esc_attr($bg) . ';';
    $btn_style .= 'color:' . esc_attr($text_color) . ';';
} elseif ($style_type === 'gradient') {
    $btn_style .= 'background:linear-gradient(135deg,' . esc_attr($bg) . ', ' . esc_attr($bg) . 'dd);';
    $btn_style .= 'color:' . esc_attr($text_color) . ';';
} elseif ($style_type === 'outlined') {
    $btn_style .= 'background:transparent;';
    $btn_style .= 'border:2px solid ' . esc_attr($bg) . ';';
    $btn_style .= 'color:' . esc_attr($bg) . ';';
}

$btn_style .= 'border-radius:' . esc_attr($border_radius . $border_radius_unit) . ';';
$btn_style .= 'padding:' . esc_attr($padding_v . $padding_v_unit) . ' ' . esc_attr($padding_h . $padding_h_unit) . ';';

$width_display = 'auto';
if ($width === 'full') {
    $width_display = '100%';
    $btn_style .= 'width:100%;display:block;text-align:center;';
} elseif ($width === 'custom' && isset($settings->width_custom) && $settings->width_custom !== '') {
    $wu = isset($settings->width_custom_unit) ? $settings->width_custom_unit : 'px';
    $width_display = $settings->width_custom . $wu;
    $btn_style .= 'width:' . esc_attr($width_display) . ';display:inline-block;';
}

// Build wrapper style with alignment and margins.
$wrap_style = 'text-align:' . esc_attr($alignment) . ';';
if (isset($settings->margin_top) && $settings->margin_top !== '') {
    $margin_top_unit = $settings->margin_top_unit ?? 'px';
    $wrap_style .= 'margin-top:' . esc_attr($settings->margin_top . $margin_top_unit) . ';';
}
if (isset($settings->margin_bottom) && $settings->margin_bottom !== '') {
    $margin_bottom_unit = $settings->margin_bottom_unit ?? 'px';
    $wrap_style .= 'margin-bottom:' . esc_attr($settings->margin_bottom . $margin_bottom_unit) . ';';
}
?>
<div class="lz-button-wrap <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($wrap_style); ?>">
    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" class="lz-button <?php echo esc_attr($node_class); ?> lz-button-<?php echo esc_attr($size); ?> lz-button-<?php echo esc_attr($style_type); ?>" style="<?php echo esc_attr($btn_style); ?>">
        <?php echo esc_html($text); ?>
    </a>
</div>
