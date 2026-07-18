<select class="lz-field lz-field-animation" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<option value=""><?php esc_html_e( 'None', 'lz-builder' ); ?></option>
	<?php foreach ( ( $field['animations'] ?? [] ) as $anim ) : ?>
	<option value="<?php echo esc_attr( $anim ); ?>" <?php selected( $field_value, $anim ); ?>><?php echo esc_html( $anim ); ?></option>
	<?php endforeach; ?>
</select>
