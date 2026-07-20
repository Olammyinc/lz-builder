<?php
$text = !empty($settings->text) ? $settings->text : 'Click Here';
$style_type = !empty($settings->style) ? $settings->style : 'flat';
$size = !empty($settings->size) ? $settings->size : 'medium';
$width = !empty($settings->width) ? $settings->width : 'auto';
$alignment = !empty($settings->alignment) ? $settings->alignment : 'center';
$bg = !empty($settings->background_color) ? $settings->background_color : '#007cba';
$text_color = !empty($settings->text_color) ? $settings->text_color : '#ffffff';
$node_class = 'lz-node-' . $node->node_id;

$link_url = '';
$link_target = '';
if (!empty($settings->link)) {
    $link = is_object($settings->link) ? $settings->link : (is_array($settings->link) ? (object) $settings->link : new \stdClass());
    $link_url = $link->url ?? ($settings->link_url ?? '');
    $link_target = $link->target ?? ($settings->link_target ?? '');
} elseif (!empty($settings->link_url)) {
    $link_url = $settings->link_url;
    $link_target = $settings->link_target ?? '';
}

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

$btn_style .= \LzBuilder\LZ_CSS_Accumulator::build_border_inline($settings, 'border');
$btn_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'padding');

if ($width === 'full') {
    $btn_style .= 'width:100%;display:block;text-align:center;';
} elseif ($width === 'custom' && isset($settings->width_custom) && $settings->width_custom !== '') {
    $wu = isset($settings->width_custom_unit) ? $settings->width_custom_unit : 'px';
    $btn_style .= 'width:' . esc_attr($settings->width_custom . $wu) . ';display:inline-block;';
}

$wrap_style = 'text-align:' . esc_attr($alignment) . ';';
$wrap_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'margin');
?>
<div class="lz-button-wrap <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($wrap_style); ?>">
    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" class="lz-button <?php echo esc_attr($node_class); ?> lz-button-<?php echo esc_attr($size); ?> lz-button-<?php echo esc_attr($style_type); ?>" style="<?php echo esc_attr($btn_style); ?>">
        <?php echo esc_html($text); ?>
    </a>
</div>
