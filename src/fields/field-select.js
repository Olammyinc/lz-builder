import { createElement } from '@wordpress/element';

export default function FieldSelect( { field, value, onChange } ) {
	const options = field.options || [];
	return createElement( 'select', {
		className: 'lz-field-select',
		name: field.key,
		value: value || '',
		onChange: ( e ) => onChange( e.target.value ),
	},
		...options.map( ( opt ) =>
			createElement( 'option', {
				key: opt.value,
				value: opt.value,
			}, opt.label )
		),
	);
}
