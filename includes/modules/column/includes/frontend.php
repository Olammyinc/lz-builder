<?php
use LzBuilder\LZ_Page_Data;
use LzBuilder\LZ_Module_Registry;

$size = isset($settings->size) && $settings->size !== '' ? floatval($settings->size) : 100;
$node_class = 'lz-node-' . $node->node_id;
$bg_color = !empty($settings->background_color) ? $settings->background_color : '';
$valign = !empty($settings->vertical_alignment) ? $settings->vertical_alignment : 'top';

$col_style = 'width:' . esc_attr($size) . '%;';
if (!empty($bg_color)) {
    $col_style .= 'background-color:' . esc_attr($bg_color) . ';';
}
if (!empty($valign) && $valign !== 'top') {
    $align_map = ['center' => 'center', 'bottom' => 'flex-end'];
    $align_val = $align_map[$valign] ?? 'flex-start';
    $col_style .= 'display:flex;flex-direction:column;justify-content:' . esc_attr($align_val) . ';';
}

// Padding (dimension field).
$padding_top    = $settings->padding_top ?? '';
$padding_right  = $settings->padding_right ?? '';
$padding_bottom = $settings->padding_bottom ?? '';
$padding_left   = $settings->padding_left ?? '';
$padding_unit   = $settings->padding_unit ?? 'px';
$padding_linked = $settings->padding_linked ?? false;
if ($padding_linked && $padding_top !== '' && $padding_top !== null) {
    $col_style .= 'padding:' . esc_attr($padding_top . $padding_unit) . ';';
} else {
    if ($padding_top !== '' && $padding_top !== null) {
        $col_style .= 'padding-top:' . esc_attr($padding_top . $padding_unit) . ';';
    }
    if ($padding_right !== '' && $padding_right !== null) {
        $col_style .= 'padding-right:' . esc_attr($padding_right . $padding_unit) . ';';
    }
    if ($padding_bottom !== '' && $padding_bottom !== null) {
        $col_style .= 'padding-bottom:' . esc_attr($padding_bottom . $padding_unit) . ';';
    }
    if ($padding_left !== '' && $padding_left !== null) {
        $col_style .= 'padding-left:' . esc_attr($padding_left . $padding_unit) . ';';
    }
}

// Border.
$border_style  = $settings->border_style ?? '';
$border_width  = $settings->border_width ?? '';
$border_color  = $settings->border_color ?? '';
$border_radius = $settings->border_radius ?? '';
if (!empty($border_style)) {
    $col_style .= 'border-style:' . esc_attr($border_style) . ';';
}
if ($border_width !== '' && $border_width !== null) {
    $border_width_unit = $settings->border_width_unit ?? 'px';
    $col_style .= 'border-width:' . esc_attr($border_width . $border_width_unit) . ';';
}
if (!empty($border_color)) {
    $col_style .= 'border-color:' . esc_attr($border_color) . ';';
}
if ($border_radius !== '' && $border_radius !== null) {
    $border_radius_unit = $settings->border_radius_unit ?? 'px';
    $col_style .= 'border-radius:' . esc_attr($border_radius . $border_radius_unit) . ';';
}
?>
<div class="lz-column lz-col-<?php echo esc_attr($size); ?> <?php echo esc_attr($node_class); ?>" data-node="<?php echo esc_attr($node->node_id); ?>" style="<?php echo esc_attr($col_style); ?>">
    <?php
    $modules = LZ_Page_Data::get_nodes_by_type('module', $node->node_id);
    foreach ($modules as $mod_node) {
        $mod = LZ_Module_Registry::get_instance()->get_module($mod_node->module);
        if ($mod) {
            $mod_settings = isset($mod_node->settings) && is_array($mod_node->settings) ? (object) $mod_node->settings : new \stdClass();
            echo $mod->render($mod_node, $mod_settings);
        }
    }
    ?>
</div>
