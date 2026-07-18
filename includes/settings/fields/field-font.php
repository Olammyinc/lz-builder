<select class="lz-field lz-field-font" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<option value=""><?php esc_html_e( 'Default', 'lz-builder' ); ?></option>
	<?php foreach ( ( $field['fonts'] ?? [] ) as $font ) : ?>
	<option value="<?php echo esc_attr( $font ); ?>" <?php selected( $field_value, $font ); ?>><?php echo esc_html( $font ); ?></option>
	<?php endforeach; ?>
</select>
