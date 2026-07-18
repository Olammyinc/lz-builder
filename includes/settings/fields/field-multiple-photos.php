<?php
$photo_ids = is_array( $field_value ) ? $field_value : [];
?>
<div class="lz-field-multiple-photos-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<div class="lz-multiple-photos-grid">
		<?php foreach ( $photo_ids as $photo_id ) : ?>
		<?php if ( intval( $photo_id ) > 0 ) : ?>
		<div class="lz-multiple-photo-item" data-id="<?php echo esc_attr( $photo_id ); ?>">
			<?php echo wp_get_attachment_image( intval( $photo_id ), 'thumbnail' ); ?>
			<button type="button" class="lz-photo-remove-item">&#x2715;</button>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<input type="hidden" class="lz-field lz-field-multiple-photos" value="<?php echo esc_attr( wp_json_encode( $photo_ids ) ); ?>">
	<button type="button" class="lz-multiple-photos-upload button"><?php esc_html_e( 'Add Photos', 'lz-builder' ); ?></button>
</div>
