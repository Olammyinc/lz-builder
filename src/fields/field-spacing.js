import { createElement } from '@wordpress/element';

const SIDES = [ 'top', 'right', 'bottom', 'left' ];
const UNITS = [ 'px', 'em', '%' ];

export default function FieldSpacing( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) {
		onChange( { [ k + suffix ]: val } );
	}

	return createElement( 'div', { className: 'lz-field-spacing-wrap' },
		createElement( 'div', { className: 'lz-spacing-inputs' },
			...SIDES.map( ( side ) =>
				createElement( 'label', { key: side },
					side.charAt( 0 ).toUpperCase() + side.slice( 1 ),
					createElement( 'input', {
						type: 'number', className: 'lz-field-spacing', step: 'any',
						value: value[ k + '_' + side ] || '',
						onInput: ( e ) => sub( '_' + side, e.target.value ),
					} ),
				)
			),
		),
		createElement( 'select', {
			className: 'lz-field-spacing-unit',
			value: value[ k + '_unit' ] || 'px',
			onChange: ( e ) => sub( '_unit', e.target.value ),
		},
			...UNITS.map( ( u ) =>
				createElement( 'option', { key: u, value: u }, u )
			),
		),
	);
}
