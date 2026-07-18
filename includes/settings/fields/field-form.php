<?php
$form_data = wp_parse_args( $field_value, $field['default'] ?? [] );
?>
<div class="lz-field-form-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<?php if ( isset( $field['label'] ) && $field['label'] ) : ?>
	<div class="lz-field-form-label"><?php echo esc_html( $field['label'] ); ?></div>
	<?php endif; ?>
	<?php if ( ! empty( $field['form'] ) ) : ?>
	<div class="lz-field-form-fields">
		<?php foreach ( $field['form'] as $child_key => $child_field ) : ?>
		<div class="lz-field-form-field">
			<label><?php echo esc_html( $child_field['label'] ?? $child_key ); ?></label>
			<?php
			$child_value = $form_data[ $child_key ] ?? $child_field['default'] ?? '';
			\LzBuilder\LZ_Settings_Form::render_field( $child_field['type'] ?? 'text', $field_key . '[' . $child_key . ']', $child_value, $child_field );
			?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>
