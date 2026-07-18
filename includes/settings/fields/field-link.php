<?php
$link = wp_parse_args( $field_value, [ 'url' => '', 'target' => '', 'rel' => '' ] );
?>
<div class="lz-field-link-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
    <input type="text" class="lz-field lz-field-link-url" name="url" value="<?php echo esc_url( $link['url'] ); ?>" placeholder="<?php esc_attr_e( 'Enter URL', 'lz-builder' ); ?>">
    <label class="lz-field-link-target">
        <input type="checkbox" name="target" value="_blank" <?php checked( $link['target'], '_blank' ); ?>>
        <?php esc_html_e( 'Open in new tab', 'lz-builder' ); ?>
    </label>
    <label class="lz-field-link-rel">
        <input type="checkbox" name="rel" value="nofollow" <?php checked( $link['rel'], 'nofollow' ); ?>>
        <?php esc_html_e( 'No follow', 'lz-builder' ); ?>
    </label>
</div>
