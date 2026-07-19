import { createElement } from '@wordpress/element';

export default function FieldUnit( { field, value, onChange } ) {
	const units = field.units || [ 'px', 'em', '%' ];
	const numValue = value !== '' ? value : ( field.default || '' );
	const unitKey = field.key + '_unit';

	return createElement( 'div', { className: 'lz-field-unit-wrap' },
		createElement( 'input', {
			type: 'number',
			className: 'lz-field-input lz-field-unit-value',
			name: field.key,
			value: numValue,
			step: 'any',
			onInput: ( e ) => onChange( { [ field.key ]: e.target.value } ),
		} ),
		createElement( 'select', {
			className: 'lz-field-select lz-field-unit-select',
			name: unitKey,
			value: field.unit || units[ 0 ],
			onChange: ( e ) => onChange( { [ unitKey ]: e.target.value } ),
		},
			...units.map( ( u ) =>
				createElement( 'option', { key: u, value: u }, u )
			),
		),
	);
}
