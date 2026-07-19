import { createElement } from '@wordpress/element';

export default function FieldOrdering( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'text', className: 'lz-field-input',
		value: value || '',
		placeholder: 'Ordering (JSON array)',
		onInput: ( e ) => onChange( e.target.value ),
	} );
}
