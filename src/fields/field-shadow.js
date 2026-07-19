import { createElement } from '@wordpress/element';

export default function FieldShadow( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) { onChange( { [ k + suffix ]: val } ); }

	return createElement( 'div', { className: 'lz-field-shadow' },
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Color' ),
		createElement( 'input', { type: 'text', className: 'lz-field-input', value: value[ k + '_color' ] || '', placeholder: '#000000', onInput: ( e ) => sub( '_color', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Horizontal' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_horizontal' ] || '', onInput: ( e ) => sub( '_horizontal', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Vertical' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_vertical' ] || '', onInput: ( e ) => sub( '_vertical', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Blur' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_blur' ] || '', onInput: ( e ) => sub( '_blur', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Spread' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_spread' ] || '', onInput: ( e ) => sub( '_spread', e.target.value ) } ),
	);
}
