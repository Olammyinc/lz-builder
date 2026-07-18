<label class="lz-field-checkbox-label">
    <input type="checkbox" class="lz-field lz-field-checkbox" data-field-key="<?php echo esc_attr( $field_key ); ?>" value="1" <?php checked( $field_value, true ); ?>>
    <?php echo isset( $field['label'] ) ? esc_html( $field['label'] ) : ''; ?>
</label>
