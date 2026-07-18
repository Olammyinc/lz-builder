<?php
$border = wp_parse_args( $field_value, [
    'style'   => '',
    'width'   => [ 'top' => '', 'right' => '', 'bottom' => '', 'left' => '' ],
    'color'   => '',
] );
?>
<div class="lz-field-border-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <select class="lz-field-border-style" name="style">
        <option value=""><?php esc_html_e( 'None', 'lz-builder' ); ?></option>
        <option value="solid" <?php selected( $border['style'], 'solid' ); ?>><?php esc_html_e( 'Solid', 'lz-builder' ); ?></option>
        <option value="dashed" <?php selected( $border['style'], 'dashed' ); ?>><?php esc_html_e( 'Dashed', 'lz-builder' ); ?></option>
        <option value="dotted" <?php selected( $border['style'], 'dotted' ); ?>><?php esc_html_e( 'Dotted', 'lz-builder' ); ?></option>
        <option value="double" <?php selected( $border['style'], 'double' ); ?>><?php esc_html_e( 'Double', 'lz-builder' ); ?></option>
    </select>
    <div class="lz-field-border-widths">
        <?php foreach ( [ 'top', 'right', 'bottom', 'left' ] as $side ) : ?>
        <input type="number" class="lz-field-border-width" name="<?php echo esc_attr( $side ); ?>" value="<?php echo esc_attr( $border['width'][ $side ] ); ?>" placeholder="<?php echo esc_attr( $side ); ?>" step="any">
        <?php endforeach; ?>
    </div>
    <input type="text" class="lz-field lz-field-color lz-field-border-color" name="color" value="<?php echo esc_attr( $border['color'] ); ?>" data-alpha-enabled="true" placeholder="<?php esc_attr_e( 'Color', 'lz-builder' ); ?>">
</div>
