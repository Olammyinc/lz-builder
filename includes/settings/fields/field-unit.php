<div class="lz-field-unit-wrap">
    <input type="number" class="lz-field lz-field-unit" data-field-key="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $field_value ); ?>" step="any">
    <select class="lz-field-unit-select">
        <?php $units = $field['units'] ?? [ 'px', 'em', '%' ]; ?>
        <?php foreach ( $units as $unit ) : ?>
        <option value="<?php echo esc_attr( $unit ); ?>"><?php echo esc_html( $unit ); ?></option>
        <?php endforeach; ?>
    </select>
</div>
