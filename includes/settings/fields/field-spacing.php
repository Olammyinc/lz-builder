<?php
$spacing = wp_parse_args( $field_value, [
	'top'    => '',
	'right'  => '',
	'bottom' => '',
	'left'   => '',
	'unit'   => 'px',
] );
?>
<div class="lz-field-spacing-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<div class="lz-spacing-inputs">
		<label><?php esc_html_e( 'Top', 'lz-builder' ); ?>
			<input type="number" class="lz-field-spacing" name="top" value="<?php echo esc_attr( $spacing['top'] ); ?>" step="any">
		</label>
		<label><?php esc_html_e( 'Right', 'lz-builder' ); ?>
			<input type="number" class="lz-field-spacing" name="right" value="<?php echo esc_attr( $spacing['right'] ); ?>" step="any">
		</label>
		<label><?php esc_html_e( 'Bottom', 'lz-builder' ); ?>
			<input type="number" class="lz-field-spacing" name="bottom" value="<?php echo esc_attr( $spacing['bottom'] ); ?>" step="any">
		</label>
		<label><?php esc_html_e( 'Left', 'lz-builder' ); ?>
			<input type="number" class="lz-field-spacing" name="left" value="<?php echo esc_attr( $spacing['left'] ); ?>" step="any">
		</label>
	</div>
	<select class="lz-field-spacing-unit" name="unit">
		<?php foreach ( $field['units'] ?? [ 'px', 'em', '%' ] as $unit ) : ?>
		<option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $spacing['unit'], $unit ); ?>><?php echo esc_html( $unit ); ?></option>
		<?php endforeach; ?>
	</select>
</div>
