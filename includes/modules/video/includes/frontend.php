<?php
$video_type = !empty($settings->video_type) ? $settings->video_type : 'embed';
$embed_url = !empty($settings->embed_url) ? $settings->embed_url : '';
$video_file = !empty($settings->video_file) ? $settings->video_file : '';
$poster_id = !empty($settings->poster) ? intval($settings->poster) : 0;
$autoplay = !empty($settings->autoplay);
$loop = !empty($settings->loop);
$controls = !empty($settings->controls);
$aspect_ratio = !empty($settings->aspect_ratio) ? $settings->aspect_ratio : '16:9';
$node_class = 'lz-node-' . $node->node_id;

$poster_url = '';
if ($poster_id > 0) {
    $poster_src = wp_get_attachment_image_url($poster_id, 'full');
    if ($poster_src) {
        $poster_url = $poster_src;
    }
}

$ar_class = 'lz-video-' . str_replace(':', '-', $aspect_ratio);
?>
<div class="lz-video <?php echo esc_attr($node_class); ?> <?php echo esc_attr($ar_class); ?>">
    <div class="lz-video-wrap">
        <?php if ($video_type === 'embed' && !empty($embed_url)) : ?>
            <?php
            $embed_html = wp_oembed_get($embed_url);
            if ($embed_html) {
                echo $embed_html;
            } else {
                $iframe_src = '';
                if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $embed_url, $m)) {
                    $iframe_src = 'https://www.youtube.com/embed/' . $m[1];
                } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $embed_url, $m)) {
                    $iframe_src = 'https://www.youtube.com/embed/' . $m[1];
                } elseif (preg_match('/vimeo\.com\/(\d+)/', $embed_url, $m)) {
                    $iframe_src = 'https://player.vimeo.com/video/' . $m[1];
                }
                if ($iframe_src) {
                    $autoplay_param = $autoplay ? '&autoplay=1' : '';
                    echo '<iframe src="' . esc_url($iframe_src . $autoplay_param) . '" frameborder="0" allowfullscreen></iframe>';
                }
            }
            ?>
        <?php elseif ($video_type === 'file' && !empty($video_file)) : ?>
            <video <?php
                echo $autoplay ? ' autoplay' : '';
                echo $loop ? ' loop' : '';
                echo $controls ? ' controls' : '';
                echo !empty($poster_url) ? ' poster="' . esc_url($poster_url) . '"' : '';
            ?>>
                <source src="<?php echo esc_url($video_file); ?>" type="<?php echo esc_attr(wp_check_filetype($video_file)['type'] ?? 'video/mp4'); ?>">
                <?php esc_html_e('Your browser does not support the video tag.', 'lz-builder'); ?>
            </video>
        <?php endif; ?>
    </div>
</div>
