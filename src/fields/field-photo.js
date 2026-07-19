import { createElement } from '@wordpress/element';

export default function FieldPhoto( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'number',
		className: 'lz-field-input',
		name: field.key,
		value: value || '',
		placeholder: 'Attachment ID',
		onInput: ( e ) => onChange( e.target.value ),
	} );
}
