import { createElement } from '@wordpress/element';

const SIDES = [ 'top', 'right', 'bottom', 'left' ];
const UNITS = [ 'px', 'em', '%', 'rem', 'vw', 'vh' ];

export default function FieldDimension( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) {
		onChange( { [ k + suffix ]: val } );
	}

	return createElement( 'div', { className: 'lz-field-dimension' },
		createElement( 'label', { className: 'lz-field-inline-label' },
			createElement( 'input', {
				type: 'checkbox', className: 'lz-field-checkbox',
				checked: !! value[ k + '_linked' ],
				onChange: ( e ) => sub( '_linked', e.target.checked ? '1' : '' ),
			} ), ' Link all sides',
		),
		createElement( 'div', { className: 'lz-dimension-grid' },
			...SIDES.map( ( side ) =>
				createElement( 'div', { key: side, className: 'lz-dimension-item' },
					createElement( 'span', { className: 'lz-dimension-label' }, side ),
					createElement( 'input', {
						type: 'number', className: 'lz-field-input', step: 'any',
						value: value[ k + '_' + side ] || '',
						onInput: ( e ) => sub( '_' + side, e.target.value ),
					} ),
				)
			),
		),
		createElement( 'select', {
			className: 'lz-field-select lz-dimension-unit',
			value: value[ k + '_unit' ] || 'px',
			onChange: ( e ) => sub( '_unit', e.target.value ),
		},
			...UNITS.map( ( u ) =>
				createElement( 'option', { key: u, value: u }, u )
			),
		),
	);
}
