import { createElement } from '@wordpress/element';

export default function FieldHidden( { field, value } ) {
	return createElement( 'input', {
		type: 'hidden',
		name: field.key,
		value: value || '',
	} );
}
