<?php
$typo = wp_parse_args( $field_value, [
    'font_family'    => '',
    'weight'         => '',
    'size'           => '',
    'size_unit'      => 'px',
    'line_height'    => '',
    'line_height_unit' => 'em',
    'alignment'      => '',
    'spacing'        => '',
] );
?>
<div class="lz-field-typography-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <div class="lz-typo-row">
        <select class="lz-field-typo-font" name="font_family">
            <option value=""><?php esc_html_e( 'Default', 'lz-builder' ); ?></option>
            <?php foreach ( ( $field['fonts'] ?? [] ) as $font ) : ?>
            <option value="<?php echo esc_attr( $font ); ?>" <?php selected( $typo['font_family'], $font ); ?>><?php echo esc_html( $font ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="lz-typo-row">
        <select class="lz-field-typo-weight" name="weight">
            <option value=""><?php esc_html_e( 'Weight', 'lz-builder' ); ?></option>
            <option value="normal" <?php selected( $typo['weight'], 'normal' ); ?>><?php esc_html_e( 'Normal', 'lz-builder' ); ?></option>
            <option value="bold" <?php selected( $typo['weight'], 'bold' ); ?>><?php esc_html_e( 'Bold', 'lz-builder' ); ?></option>
            <option value="300" <?php selected( $typo['weight'], '300' ); ?>>300</option>
            <option value="400" <?php selected( $typo['weight'], '400' ); ?>>400</option>
            <option value="500" <?php selected( $typo['weight'], '500' ); ?>>500</option>
            <option value="600" <?php selected( $typo['weight'], '600' ); ?>>600</option>
            <option value="700" <?php selected( $typo['weight'], '700' ); ?>>700</option>
        </select>
    </div>
    <div class="lz-typo-row">
        <input type="number" class="lz-field-typo-size" name="size" value="<?php echo esc_attr( $typo['size'] ); ?>" step="any" placeholder="<?php esc_attr_e( 'Size', 'lz-builder' ); ?>">
        <select class="lz-field-typo-size-unit" name="size_unit">
            <?php foreach ( [ 'px', 'em', 'rem', 'vw' ] as $unit ) : ?>
            <option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $typo['size_unit'], $unit ); ?>><?php echo esc_html( $unit ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="lz-typo-row">
        <input type="number" class="lz-field-typo-line-height" name="line_height" value="<?php echo esc_attr( $typo['line_height'] ); ?>" step="any" placeholder="<?php esc_attr_e( 'Line Height', 'lz-builder' ); ?>">
        <select class="lz-field-typo-line-unit" name="line_height_unit">
            <?php foreach ( [ 'em', 'px', '%' ] as $unit ) : ?>
            <option value="<?php echo esc_attr( $unit ); ?>" <?php selected( $typo['line_height_unit'], $unit ); ?>><?php echo esc_html( $unit ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="lz-typo-row">
        <div class="lz-field-button-group" name="alignment">
            <?php foreach ( [ 'left' => __( 'L', 'lz-builder' ), 'center' => __( 'C', 'lz-builder' ), 'right' => __( 'R', 'lz-builder' ), 'justify' => __( 'J', 'lz-builder' ) ] as $aln_val => $aln_label ) : ?>
            <button type="button" class="lz-field-button <?php echo $typo['alignment'] === $aln_val ? 'lz-active' : ''; ?>" data-value="<?php echo esc_attr( $aln_val ); ?>"><?php echo esc_html( $aln_label ); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="lz-typo-row">
        <input type="range" class="lz-field-typo-spacing" name="spacing" min="0" max="10" step="0.1" value="<?php echo esc_attr( $typo['spacing'] ); ?>">
        <span class="lz-typo-spacing-value"><?php echo esc_html( $typo['spacing'] ); ?></span>
    </div>
</div>
