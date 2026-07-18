<div class="lz-field-button-group" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <?php foreach ( ( $field['options'] ?? [] ) as $opt_value => $opt_label ) : ?>
    <button type="button" class="lz-field-button <?php echo ( (string) $field_value === (string) $opt_value ) ? 'lz-active' : ''; ?>" data-value="<?php echo esc_attr( $opt_value ); ?>">
        <?php echo esc_html( $opt_label ); ?>
    </button>
    <?php endforeach; ?>
</div>
