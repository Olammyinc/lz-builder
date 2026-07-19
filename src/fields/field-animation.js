import { createElement } from '@wordpress/element';

export default function FieldAnimation( { field, value, onChange } ) {
	const opts = field.options || [];
	return createElement( 'select', {
		className: 'lz-field-select',
		value: value || '',
		onChange: ( e ) => onChange( e.target.value ),
	},
		createElement( 'option', { value: '' }, 'None' ),
		...opts.map( ( o ) =>
			createElement( 'option', { key: o.value, value: o.value }, o.label )
		),
	);
}
