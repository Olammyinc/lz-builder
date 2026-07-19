import { createElement } from '@wordpress/element';

export default function FieldRaw( { field, value, onChange } ) {
	return createElement( 'textarea', {
		className: 'lz-field-code',
		rows: field.rows || 6,
		style: { fontFamily: 'monospace' },
		onInput: ( e ) => onChange( e.target.value ),
	}, value || '' );
}
