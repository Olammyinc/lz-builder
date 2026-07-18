<?php
use LzBuilder\LZ_Page_Data;
use LzBuilder\LZ_Module_Registry;

$node_class = 'lz-node-' . $node->node_id;
$row_style = '';

$bg_color = !empty($settings->background_color) ? $settings->background_color : '';
if (!empty($bg_color)) {
    $row_style .= 'background-color:' . esc_attr($bg_color) . ';';
}

$min_height = isset($settings->min_height) && $settings->min_height !== '' ? $settings->min_height : '';
if ($min_height !== '') {
    $unit = isset($settings->min_height_unit) ? $settings->min_height_unit : 'px';
    $row_style .= 'min-height:' . esc_attr($min_height . $unit) . ';';
}

$width_type = !empty($settings->width) ? $settings->width : 'full';
if ($width_type === 'fixed') {
    $mw = isset($settings->max_content_width) && $settings->max_content_width !== '' ? $settings->max_content_width : 1170;
    $mw_unit = isset($settings->max_content_width_unit) ? $settings->max_content_width_unit : 'px';
    $row_style .= 'max-width:' . esc_attr($mw . $mw_unit) . ';margin-left:auto;margin-right:auto;';
}

// Background image.
if (!empty($settings->background_image)) {
    $bg_img_url = wp_get_attachment_image_url(intval($settings->background_image), 'full');
    if ($bg_img_url) {
        $row_style .= 'background-image:url(' . esc_url($bg_img_url) . ');';
    }
}
if (!empty($settings->background_repeat)) {
    $row_style .= 'background-repeat:' . esc_attr($settings->background_repeat) . ';';
}
if (!empty($settings->background_size)) {
    $row_style .= 'background-size:' . esc_attr($settings->background_size) . ';';
}

// Border.
$border_style  = $settings->border_style ?? '';
$border_width  = $settings->border_width ?? '';
$border_color  = $settings->border_color ?? '';
$border_radius = $settings->border_radius ?? '';
if (!empty($border_style)) {
    $row_style .= 'border-style:' . esc_attr($border_style) . ';';
}
if ($border_width !== '' && $border_width !== null) {
    $border_width_unit = $settings->border_width_unit ?? 'px';
    $row_style .= 'border-width:' . esc_attr($border_width . $border_width_unit) . ';';
}
if (!empty($border_color)) {
    $row_style .= 'border-color:' . esc_attr($border_color) . ';';
}
if ($border_radius !== '' && $border_radius !== null) {
    $border_radius_unit = $settings->border_radius_unit ?? 'px';
    $row_style .= 'border-radius:' . esc_attr($border_radius . $border_radius_unit) . ';';
}

// Padding (dimension field).
$padding_top    = $settings->padding_top ?? '';
$padding_right  = $settings->padding_right ?? '';
$padding_bottom = $settings->padding_bottom ?? '';
$padding_left   = $settings->padding_left ?? '';
$padding_unit   = $settings->padding_unit ?? 'px';
$padding_linked = $settings->padding_linked ?? false;
if ($padding_linked && $padding_top !== '' && $padding_top !== null) {
    $row_style .= 'padding:' . esc_attr($padding_top . $padding_unit) . ';';
} else {
    if ($padding_top !== '' && $padding_top !== null) {
        $row_style .= 'padding-top:' . esc_attr($padding_top . $padding_unit) . ';';
    }
    if ($padding_right !== '' && $padding_right !== null) {
        $row_style .= 'padding-right:' . esc_attr($padding_right . $padding_unit) . ';';
    }
    if ($padding_bottom !== '' && $padding_bottom !== null) {
        $row_style .= 'padding-bottom:' . esc_attr($padding_bottom . $padding_unit) . ';';
    }
    if ($padding_left !== '' && $padding_left !== null) {
        $row_style .= 'padding-left:' . esc_attr($padding_left . $padding_unit) . ';';
    }
}

// Margin (dimension field).
$margin_top    = $settings->margin_top ?? '';
$margin_right  = $settings->margin_right ?? '';
$margin_bottom = $settings->margin_bottom ?? '';
$margin_left   = $settings->margin_left ?? '';
$margin_unit   = $settings->margin_unit ?? 'px';
$margin_linked = $settings->margin_linked ?? false;
if ($margin_linked && $margin_top !== '' && $margin_top !== null) {
    $row_style .= 'margin:' . esc_attr($margin_top . $margin_unit) . ';';
} else {
    if ($margin_top !== '' && $margin_top !== null) {
        $row_style .= 'margin-top:' . esc_attr($margin_top . $margin_unit) . ';';
    }
    if ($margin_right !== '' && $margin_right !== null) {
        $row_style .= 'margin-right:' . esc_attr($margin_right . $margin_unit) . ';';
    }
    if ($margin_bottom !== '' && $margin_bottom !== null) {
        $row_style .= 'margin-bottom:' . esc_attr($margin_bottom . $margin_unit) . ';';
    }
    if ($margin_left !== '' && $margin_left !== null) {
        $row_style .= 'margin-left:' . esc_attr($margin_left . $margin_unit) . ';';
    }
}
?>
<div class="lz-row <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($row_style); ?>">
    <?php
    $children = LZ_Page_Data::get_nodes_by_type('column', $node->node_id);
    foreach ($children as $child) {
        $grandchildren = LZ_Page_Data::get_nodes_by_type('column', $child->node_id);
        if (!empty($grandchildren)) {
            foreach ($grandchildren as $col) {
                $col_module = LZ_Module_Registry::get_instance()->get_module('column');
                if ($col_module) {
                    $col_settings = isset($col->settings) && is_array($col->settings) ? (object) $col->settings : new \stdClass();
                    echo $col_module->render($col, $col_settings);
                }
            }
        } else {
            $col_module = LZ_Module_Registry::get_instance()->get_module('column');
            if ($col_module) {
                $col_settings = isset($child->settings) && is_array($child->settings) ? (object) $child->settings : new \stdClass();
                echo $col_module->render($child, $col_settings);
            }
        }
    }
    ?>
</div>
