<?php
$photo_id = !empty($settings->photo) ? intval($settings->photo) : 0;
$size = !empty($settings->size) ? $settings->size : 'full';
$alt = !empty($settings->alt) ? $settings->alt : '';
$alignment = !empty($settings->alignment) ? $settings->alignment : 'center';
$border_radius = !empty($settings->border_radius) ? $settings->border_radius : '';
$node_class = 'lz-node-' . $node->node_id;

$src = '';
if ($photo_id > 0) {
    $image_data = wp_get_attachment_image_src($photo_id, $size);
    if ($image_data) {
        $src = $image_data[0];
    }
}

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

$img_style = '';
if ($border_radius !== '') {
    $unit = isset($settings->border_radius_unit) ? $settings->border_radius_unit : 'px';
    $img_style = 'border-radius:' . esc_attr($border_radius . $unit) . ';';
}

$wrap_style = 'text-align:' . esc_attr($alignment) . ';';
$wrap_style .= \LzBuilder\LZ_CSS_Accumulator::build_dimension_inline($settings, 'margin');
?>
<div class="lz-photo <?php echo esc_attr($node_class); ?>" style="<?php echo esc_attr($wrap_style); ?>">
    <?php if (!empty($link_url)) : ?>
    <a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>">
    <?php endif; ?>
        <?php if (!empty($src)) : ?>
        <img class="lz-photo-img" src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" style="<?php echo esc_attr($img_style); ?>">
        <?php endif; ?>
    <?php if (!empty($link_url)) : ?>
    </a>
    <?php endif; ?>
</div>
