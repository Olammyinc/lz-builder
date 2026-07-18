<div class="lz-field-icon-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <input type="hidden" class="lz-field lz-field-icon" value="<?php echo esc_attr( $field_value ); ?>">
    <div class="lz-icon-grid">
        <?php foreach ( ( $field['icons'] ?? [] ) as $icon ) : ?>
        <span class="lz-icon-item <?php echo $field_value === $icon ? 'lz-active' : ''; ?>" data-icon="<?php echo esc_attr( $icon ); ?>">
            <i class="<?php echo esc_attr( $icon ); ?>"></i>
        </span>
        <?php endforeach; ?>
    </div>
    <input type="text" class="lz-icon-search" placeholder="<?php esc_attr_e( 'Search icons...', 'lz-builder' ); ?>">
</div>
