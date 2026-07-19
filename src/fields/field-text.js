import { createElement } from '@wordpress/element';

export default function FieldText( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'text',
		className: 'lz-field-input',
		name: field.key,
		value: value || '',
		placeholder: field.placeholder || '',
		onInput: ( e ) => onChange( e.target.value ),
	} );
}
