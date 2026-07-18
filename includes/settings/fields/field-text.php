<input type="text" class="lz-field lz-field-text" data-field-key="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $field_value ); ?>" placeholder="<?php echo isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>">
<?php if ( isset( $field['description'] ) && $field['description'] ) : ?>
<p class="lz-field-description"><?php echo esc_html( $field['description'] ); ?></p>
<?php endif; ?>
