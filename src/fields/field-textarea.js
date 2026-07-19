import { createElement } from '@wordpress/element';

export default function FieldTextarea( { field, value, onChange } ) {
	return createElement( 'textarea', {
		className: 'lz-field-textarea',
		name: field.key,
		rows: field.rows || 4,
		onInput: ( e ) => onChange( e.target.value ),
	}, value || '' );
}
