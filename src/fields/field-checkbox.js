import { createElement } from '@wordpress/element';

export default function FieldCheckbox( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'checkbox',
		className: 'lz-field-checkbox',
		name: field.key,
		checked: !! value,
		onChange: ( e ) => onChange( e.target.checked ? '1' : '' ),
	} );
}
