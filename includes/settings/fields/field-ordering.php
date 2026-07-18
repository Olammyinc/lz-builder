<?php
$items = is_array( $field_value ) ? $field_value : [];
if ( empty( $items ) && ! empty( $field['options'] ) ) {
	$items = $field['options'];
}
?>
<div class="lz-field-ordering-wrap" data-field-key="<?php echo esc_attr( $field_key ); ?>">
	<ul class="lz-ordering-list">
		<?php foreach ( $items as $item_key => $item_label ) : ?>
		<li class="lz-ordering-item" data-key="<?php echo esc_attr( $item_key ); ?>">
			<span class="lz-ordering-handle">&#x2630;</span>
			<span class="lz-ordering-label"><?php echo esc_html( $item_label ); ?></span>
		</li>
		<?php endforeach; ?>
	</ul>
	<input type="hidden" class="lz-field lz-field-ordering" value="<?php echo esc_attr( wp_json_encode( array_keys( $items ) ) ); ?>">
</div>
