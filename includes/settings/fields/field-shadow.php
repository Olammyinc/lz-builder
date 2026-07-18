<?php
$shadow = wp_parse_args( $field_value, [
	'color'      => '',
	'horizontal' => '0',
	'vertical'   => '0',
	'blur'       => '0',
	'spread'     => '0',
] );
?>
<div class="lz-field-shadow-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<label><?php esc_html_e( 'Color', 'lz-builder' ); ?>
		<input type="text" class="lz-field lz-field-color lz-field-shadow-color" name="color" value="<?php echo esc_attr( $shadow['color'] ); ?>" data-alpha-enabled="true">
	</label>
	<label><?php esc_html_e( 'Horizontal', 'lz-builder' ); ?>
		<input type="number" class="lz-field-shadow-h" name="horizontal" value="<?php echo esc_attr( $shadow['horizontal'] ); ?>" step="any">
	</label>
	<label><?php esc_html_e( 'Vertical', 'lz-builder' ); ?>
		<input type="number" class="lz-field-shadow-v" name="vertical" value="<?php echo esc_attr( $shadow['vertical'] ); ?>" step="any">
	</label>
	<label><?php esc_html_e( 'Blur', 'lz-builder' ); ?>
		<input type="number" class="lz-field-shadow-blur" name="blur" value="<?php echo esc_attr( $shadow['blur'] ); ?>" step="any" min="0">
	</label>
	<label><?php esc_html_e( 'Spread', 'lz-builder' ); ?>
		<input type="number" class="lz-field-shadow-spread" name="spread" value="<?php echo esc_attr( $shadow['spread'] ); ?>" step="any">
	</label>
</div>
