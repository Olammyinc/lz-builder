import { createElement } from '@wordpress/element';

export default function FieldButtonGroup( { field, value, onChange } ) {
	const options = field.options || [];
	return createElement( 'div', { className: 'lz-field-btn-group' },
		...options.map( ( opt ) =>
			createElement( 'button', {
				key: opt.value,
				type: 'button',
				className: 'lz-btn-group-option' + ( value === opt.value ? ' lz-btn-group-active' : '' ),
				'data-value': opt.value,
				onClick: () => onChange( opt.value ),
			}, opt.label )
		),
		createElement( 'input', {
			type: 'hidden',
			name: field.key,
			value: value || '',
		} ),
	);
}
