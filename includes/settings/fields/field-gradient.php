<?php
$gradient = wp_parse_args( $field_value, [
	'type'    => 'linear',
	'angle'   => '90',
	'color_1' => '',
	'color_2' => '',
	'stop_1'  => '0',
	'stop_2'  => '100',
] );
?>
<div class="lz-field-gradient-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<select class="lz-field-gradient-type" name="type">
		<option value="linear" <?php selected( $gradient['type'], 'linear' ); ?>><?php esc_html_e( 'Linear', 'lz-builder' ); ?></option>
		<option value="radial" <?php selected( $gradient['type'], 'radial' ); ?>><?php esc_html_e( 'Radial', 'lz-builder' ); ?></option>
	</select>
	<input type="number" class="lz-field-gradient-angle" name="angle" value="<?php echo esc_attr( $gradient['angle'] ); ?>" placeholder="<?php esc_attr_e( 'Angle', 'lz-builder' ); ?>" min="0" max="360">
	<div class="lz-gradient-stops">
		<label><?php esc_html_e( 'Start', 'lz-builder' ); ?>
			<input type="text" class="lz-field lz-field-color lz-field-gradient-color" name="color_1" value="<?php echo esc_attr( $gradient['color_1'] ); ?>" data-alpha-enabled="true">
		</label>
		<input type="number" class="lz-field-gradient-stop" name="stop_1" value="<?php echo esc_attr( $gradient['stop_1'] ); ?>" placeholder="%" min="0" max="100">
		<label><?php esc_html_e( 'End', 'lz-builder' ); ?>
			<input type="text" class="lz-field lz-field-color lz-field-gradient-color" name="color_2" value="<?php echo esc_attr( $gradient['color_2'] ); ?>" data-alpha-enabled="true">
		</label>
		<input type="number" class="lz-field-gradient-stop" name="stop_2" value="<?php echo esc_attr( $gradient['stop_2'] ); ?>" placeholder="%" min="0" max="100">
	</div>
</div>
