import { createElement } from '@wordpress/element';

export default function FieldLink( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) {
		onChange( { [ k + suffix ]: val } );
	}

	return createElement( 'div', { className: 'lz-field-link' },
		createElement( 'label', { className: 'lz-field-sub-label' }, 'URL' ),
		createElement( 'input', {
			type: 'text', className: 'lz-field-input',
			value: value[ k + '_url' ] || '',
			placeholder: 'https://',
			onInput: ( e ) => sub( '_url', e.target.value ),
		} ),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Target' ),
		createElement( 'select', {
			className: 'lz-field-select',
			value: value[ k + '_target' ] || '',
			onChange: ( e ) => sub( '_target', e.target.value ),
		},
			createElement( 'option', { value: '' }, 'Same Window' ),
			createElement( 'option', { value: '_blank' }, 'New Window' ),
		),
		createElement( 'label', { className: 'lz-field-inline-label' },
			createElement( 'input', {
				type: 'checkbox', className: 'lz-field-checkbox',
				checked: !! value[ k + '_nofollow' ],
				onChange: ( e ) => sub( '_nofollow', e.target.checked ? '1' : '' ),
			} ), ' nofollow',
		),
	);
}
