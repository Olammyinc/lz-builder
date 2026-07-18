<?php
if ( is_admin() ) {
    wp_editor( wp_kses_post( $field_value ), 'lz-editor-' . sanitize_key( $field_key ), [
        'textarea_name' => $field_key,
        'media_buttons' => false,
        'textarea_rows' => 8,
        'teeny'         => true,
        'quicktags'     => false,
    ] );
} else {
    ?>
    <textarea class="lz-field lz-field-editor" data-field-key="<?php echo esc_attr( $field_key ); ?>" rows="8"><?php echo esc_textarea( $field_value ); ?></textarea>
    <?php
}
?>
