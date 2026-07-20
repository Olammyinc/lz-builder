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

$max_width = isset($settings->max_content_width) && $settings->max_content_width !== '' ? $settings->max_content_width : '';
if ($max_width !== '') {
    $unit = isset($settings->max_content_width_unit) ? $settings->max_content_width_unit : 'px';
    $row_style .= 'max-width:' . esc_attr($max_width . $unit) . ';margin-left:auto;margin-right:auto;';
}

$bg_img_id = isset($settings->background_image) ? intval($settings->background_image) : 0;
if ($bg_img_id > 0) {
    $bg_img_url = wp_get_attachment_image_url($bg_img_id, 'full');
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

$row_style .= \LzBuilder\LZ_CSS_Accumulator::build_border_inline($settings, 'border');
$row_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'padding');
$row_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'margin');
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
