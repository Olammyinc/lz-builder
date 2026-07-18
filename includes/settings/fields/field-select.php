<select class="lz-field lz-field-select" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <?php if ( isset( $field['placeholder'] ) && $field['placeholder'] ) : ?>
    <option value=""><?php echo esc_html( $field['placeholder'] ); ?></option>
    <?php endif; ?>
    <?php foreach ( ( $field['options'] ?? [] ) as $opt_value => $opt_label ) : ?>
    <option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $field_value, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
    <?php endforeach; ?>
</select>
