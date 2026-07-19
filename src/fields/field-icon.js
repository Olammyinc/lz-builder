import { createElement } from '@wordpress/element';

export default function FieldIcon( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'text',
		className: 'lz-field-input',
		name: field.key,
		value: value || '',
		placeholder: 'dashicons-admin-site',
		onInput: ( e ) => onChange( e.target.value ),
	} );
}
