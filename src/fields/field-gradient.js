import { createElement } from '@wordpress/element';

export default function FieldGradient( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) { onChange( { [ k + suffix ]: val } ); }

	return createElement( 'div', { className: 'lz-field-gradient' },
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Type' ),
		createElement( 'select', { className: 'lz-field-select', value: value[ k + '_type' ] || 'linear', onChange: ( e ) => sub( '_type', e.target.value ) },
			createElement( 'option', { value: 'linear' }, 'Linear' ),
			createElement( 'option', { value: 'radial' }, 'Radial' ),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Angle' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_angle' ] || '', onInput: ( e ) => sub( '_angle', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Color 1' ),
		createElement( 'input', { type: 'text', className: 'lz-field-input', value: value[ k + '_color_1' ] || '', placeholder: '#000000', onInput: ( e ) => sub( '_color_1', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Stop 1' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_stop_1' ] || '', onInput: ( e ) => sub( '_stop_1', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Color 2' ),
		createElement( 'input', { type: 'text', className: 'lz-field-input', value: value[ k + '_color_2' ] || '', placeholder: '#000000', onInput: ( e ) => sub( '_color_2', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Stop 2' ),
		createElement( 'input', { type: 'number', className: 'lz-field-input', step: 'any', value: value[ k + '_stop_2' ] || '', onInput: ( e ) => sub( '_stop_2', e.target.value ) } ),
	);
}
