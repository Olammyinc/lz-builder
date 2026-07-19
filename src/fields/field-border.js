import { createElement, useRef } from '@wordpress/element';

const STYLES = [ '', 'solid', 'dashed', 'dotted', 'double' ];
const WIDTH_UNITS = [ 'px', 'em' ];
const RADIUS_UNITS = [ 'px', '%', 'em' ];

export default function FieldBorder( { field, value, onChange } ) {
	const k = field.key;
	const colorVal = value[ k + '_color' ] || 'transparent';
	const swatchRef = useRef( null );

	function sub( suffix, val ) {
		onChange( { [ k + suffix ]: val } );
	}

	return createElement( 'div', { className: 'lz-field-border' },
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Style' ),
		createElement( 'select', {
			className: 'lz-field-select',
			value: value[ k + '_style' ] || '',
			onChange: ( e ) => sub( '_style', e.target.value ),
		},
			...STYLES.map( ( s ) =>
				createElement( 'option', { key: s, value: s }, s || 'None' )
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Width' ),
		createElement( 'div', { className: 'lz-field-unit-wrap' },
			createElement( 'input', {
				type: 'number', className: 'lz-field-input', step: 'any',
				value: value[ k + '_width' ] || '',
				onInput: ( e ) => sub( '_width', e.target.value ),
			} ),
			createElement( 'select', {
				className: 'lz-field-select',
				value: value[ k + '_width_unit' ] || 'px',
				onChange: ( e ) => sub( '_width_unit', e.target.value ),
			},
				...WIDTH_UNITS.map( ( u ) =>
					createElement( 'option', { key: u, value: u }, u )
				),
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Color' ),
		createElement( 'div', { className: 'lz-color-field' },
			createElement( 'input', {
				type: 'text', className: 'lz-field-input lz-field-color-text',
				value: value[ k + '_color' ] || '',
				placeholder: '#000000',
				onInput: ( e ) => sub( '_color', e.target.value ),
			} ),
			createElement( 'span', {
				ref: swatchRef,
				className: 'lz-color-swatch',
				style: { backgroundColor: colorVal },
				tabIndex: 0, role: 'button',
				onClick: () => {
					const native = swatchRef.current?.querySelector( 'input[type="color"]' );
					if ( native ) native.click();
				},
			},
				createElement( 'input', {
					type: 'color', className: 'lz-field-color-native',
					value: colorVal,
					onInput: ( e ) => sub( '_color', e.target.value ),
				} ),
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Radius' ),
		createElement( 'div', { className: 'lz-field-unit-wrap' },
			createElement( 'input', {
				type: 'number', className: 'lz-field-input', step: 'any',
				value: value[ k + '_radius' ] || '',
				onInput: ( e ) => sub( '_radius', e.target.value ),
			} ),
			createElement( 'select', {
				className: 'lz-field-select',
				value: value[ k + '_radius_unit' ] || 'px',
				onChange: ( e ) => sub( '_radius_unit', e.target.value ),
			},
				...RADIUS_UNITS.map( ( u ) =>
					createElement( 'option', { key: u, value: u }, u )
				),
			),
		),
	);
}
