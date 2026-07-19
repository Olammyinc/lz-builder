import { createElement, useRef } from '@wordpress/element';

export default function FieldColor( { field, value, onChange } ) {
	const hex = ( value && /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test( value ) ) ? value : '#000000';
	const swatchRef = useRef( null );

	return createElement( 'div', { className: 'lz-color-field' },
		createElement( 'input', {
			type: 'text',
			className: 'lz-field-input lz-field-color-text',
			name: field.key,
			value: value || '',
			placeholder: '#000000',
			onInput: ( e ) => onChange( e.target.value ),
		} ),
		createElement( 'span', {
			ref: swatchRef,
			className: 'lz-color-swatch',
			style: { backgroundColor: value || 'transparent' },
			tabIndex: 0,
			role: 'button',
			onClick: () => {
				const native = swatchRef.current?.querySelector( 'input[type="color"]' );
				if ( native ) native.click();
			},
			onKeyDown: ( e ) => {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					const native = swatchRef.current?.querySelector( 'input[type="color"]' );
					if ( native ) native.click();
				}
			},
		},
			createElement( 'input', {
				type: 'color',
				className: 'lz-field-color-native',
				value: hex,
				onInput: ( e ) => onChange( e.target.value ),
			} ),
		),
	);
}
