<?php
$dimension = wp_parse_args( $field_value, [
    'top'    => '',
    'right'  => '',
    'bottom' => '',
    'left'   => '',
] );
?>
<div class="lz-field-dimension-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <label class="lz-field-dimension-label"><?php esc_html_e( 'Top', 'lz-builder' ); ?>
        <input type="number" class="lz-field-dimension" name="top" value="<?php echo esc_attr( $dimension['top'] ); ?>" step="any">
    </label>
    <label class="lz-field-dimension-label"><?php esc_html_e( 'Right', 'lz-builder' ); ?>
        <input type="number" class="lz-field-dimension" name="right" value="<?php echo esc_attr( $dimension['right'] ); ?>" step="any">
    </label>
    <label class="lz-field-dimension-label"><?php esc_html_e( 'Bottom', 'lz-builder' ); ?>
        <input type="number" class="lz-field-dimension" name="bottom" value="<?php echo esc_attr( $dimension['bottom'] ); ?>" step="any">
    </label>
    <label class="lz-field-dimension-label"><?php esc_html_e( 'Left', 'lz-builder' ); ?>
        <input type="number" class="lz-field-dimension" name="left" value="<?php echo esc_attr( $dimension['left'] ); ?>" step="any">
    </label>
</div>
