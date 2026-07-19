import { createElement } from '@wordpress/element';

export default function FieldCode( { field, value, onChange } ) {
	return createElement( 'textarea', {
		className: 'lz-field-code',
		name: field.key,
		rows: field.rows || 8,
		style: { fontFamily: 'monospace' },
		onInput: ( e ) => onChange( e.target.value ),
	}, value || '' );
}
