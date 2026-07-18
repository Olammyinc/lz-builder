<div class="lz-field-photo-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <div class="lz-photo-preview">
        <?php if ( intval( $field_value ) > 0 ) : ?>
        <?php echo wp_get_attachment_image( intval( $field_value ), 'thumbnail' ); ?>
        <?php endif; ?>
    </div>
    <input type="hidden" class="lz-field lz-field-photo" value="<?php echo esc_attr( $field_value ); ?>">
    <button type="button" class="lz-photo-upload button"><?php esc_html_e( 'Select Photo', 'lz-builder' ); ?></button>
    <button type="button" class="lz-photo-remove button" <?php echo empty( $field_value ) ? 'style="display:none"' : ''; ?>><?php esc_html_e( 'Remove', 'lz-builder' ); ?></button>
</div>
