<?php
$video = wp_parse_args( $field_value, [
	'type'     => 'embed',
	'url'      => '',
	'poster'   => '',
	'autoplay' => false,
	'loop'     => false,
] );
?>
<div class="lz-field-video-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<select class="lz-field-video-type" name="type">
		<option value="embed" <?php selected( $video['type'], 'embed' ); ?>><?php esc_html_e( 'Embed URL', 'lz-builder' ); ?></option>
		<option value="file" <?php selected( $video['type'], 'file' ); ?>><?php esc_html_e( 'Media File', 'lz-builder' ); ?></option>
	</select>
	<input type="text" class="lz-field lz-field-video-url" name="url" value="<?php echo esc_url( $video['url'] ); ?>" placeholder="<?php esc_attr_e( 'Video URL', 'lz-builder' ); ?>">
	<label><?php esc_html_e( 'Poster Image', 'lz-builder' ); ?>
		<input type="text" class="lz-field lz-field-video-poster" name="poster" value="<?php echo esc_url( $video['poster'] ); ?>" placeholder="<?php esc_attr_e( 'Poster image URL', 'lz-builder' ); ?>">
	</label>
	<label class="lz-field-checkbox-label">
		<input type="checkbox" class="lz-field-video-autoplay" name="autoplay" value="1" <?php checked( $video['autoplay'], true ); ?>>
		<?php esc_html_e( 'Autoplay', 'lz-builder' ); ?>
	</label>
	<label class="lz-field-checkbox-label">
		<input type="checkbox" class="lz-field-video-loop" name="loop" value="1" <?php checked( $video['loop'], true ); ?>>
		<?php esc_html_e( 'Loop', 'lz-builder' ); ?>
	</label>
</div>
