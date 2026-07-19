import { createElement } from '@wordpress/element';

export default function FieldVideo( { field, value, onChange } ) {
	const k = field.key;
	function sub( suffix, val ) { onChange( { [ k + suffix ]: val } ); }

	return createElement( 'div', { className: 'lz-field-video-wrap' },
		createElement( 'select', { className: 'lz-field-video-type', value: value[ k + '_type' ] || 'embed', onChange: ( e ) => sub( '_type', e.target.value ) },
			createElement( 'option', { value: 'embed' }, 'Embed URL' ),
			createElement( 'option', { value: 'file' }, 'Media File' ),
		),
		createElement( 'input', { type: 'text', className: 'lz-field lz-field-video-url', value: value[ k + '_url' ] || '', placeholder: 'Video URL', onInput: ( e ) => sub( '_url', e.target.value ) } ),
		createElement( 'input', { type: 'text', className: 'lz-field lz-field-video-poster', value: value[ k + '_poster' ] || '', placeholder: 'Poster image URL', onInput: ( e ) => sub( '_poster', e.target.value ) } ),
		createElement( 'label', { className: 'lz-field-checkbox-label' },
			createElement( 'input', { type: 'checkbox', className: 'lz-field-video-autoplay', checked: !! value[ k + '_autoplay' ], onChange: ( e ) => sub( '_autoplay', e.target.checked ? '1' : '' ) } ), ' Autoplay',
		),
		createElement( 'label', { className: 'lz-field-checkbox-label' },
			createElement( 'input', { type: 'checkbox', className: 'lz-field-video-loop', checked: !! value[ k + '_loop' ], onChange: ( e ) => sub( '_loop', e.target.checked ? '1' : '' ) } ), ' Loop',
		),
	);
}
