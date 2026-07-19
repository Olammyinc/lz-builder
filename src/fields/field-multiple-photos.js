import { createElement } from '@wordpress/element';

export default function FieldMultiplePhotos( { field, value, onChange } ) {
	const ids = Array.isArray( value ) ? value : [];
	return createElement( 'div', { className: 'lz-field-multiple-photos' },
		createElement( 'input', {
			type: 'text', className: 'lz-field-input',
			value: ids.join( ',' ),
			placeholder: 'Attachment IDs (comma-separated)',
			onInput: ( e ) => onChange( e.target.value.split( ',' ).filter( Boolean ) ),
		} ),
	);
}
