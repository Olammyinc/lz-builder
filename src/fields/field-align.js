import { createElement } from '@wordpress/element';

export default function FieldAlign( { field, value, onChange } ) {
	const opts = field.options || [
		{ value: 'left', label: 'Left' },
		{ value: 'center', label: 'Center' },
		{ value: 'right', label: 'Right' },
	];
	return createElement( 'div', { className: 'lz-field-btn-group' },
		...opts.map( ( o ) =>
			createElement( 'button', {
				key: o.value,
				type: 'button',
				className: 'lz-btn-group-option' + ( value === o.value ? ' lz-btn-group-active' : '' ),
				onClick: () => onChange( o.value ),
			}, o.label )
		),
		createElement( 'input', { type: 'hidden', name: field.key, value: value || '' } ),
	);
}
