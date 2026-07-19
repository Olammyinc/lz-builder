import { createElement } from '@wordpress/element';

export default function FieldSuggest( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'text', className: 'lz-field-input',
		value: value || '',
		placeholder: 'Type to search...',
		onInput: ( e ) => onChange( e.target.value ),
	} );
}
