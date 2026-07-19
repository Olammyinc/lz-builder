import { createElement } from '@wordpress/element';

export default function FieldFont( { field, value, onChange } ) {
	const fonts = field.options || [ { value: '', label: 'Default' } ];
	return createElement( 'select', {
		className: 'lz-field-select',
		name: field.key,
		value: value || '',
		onChange: ( e ) => onChange( e.target.value ),
	},
		...fonts.map( ( f ) =>
			createElement( 'option', { key: f.value, value: f.value }, f.label )
		),
	);
}
