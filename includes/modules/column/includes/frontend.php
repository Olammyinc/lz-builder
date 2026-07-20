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

$col_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'padding');
$col_style .= \LzBuilder\LZ_CSS_Accumulator::build_border_inline($settings, 'border');
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
