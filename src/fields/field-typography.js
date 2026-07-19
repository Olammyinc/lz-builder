import { createElement } from '@wordpress/element';

const FONT_FAMILIES = [
	{ value: '', label: 'Default' },
	{ value: 'Arial, Helvetica, sans-serif', label: 'Arial' },
	{ value: 'Helvetica, Arial, sans-serif', label: 'Helvetica' },
	{ value: 'Georgia, serif', label: 'Georgia' },
	{ value: 'Times New Roman, serif', label: 'Times New Roman' },
	{ value: 'Verdana, Geneva, sans-serif', label: 'Verdana' },
	{ value: 'Courier New, monospace', label: 'Courier New' },
	{ value: 'Open Sans, sans-serif', label: 'Open Sans' },
	{ value: 'Roboto, sans-serif', label: 'Roboto' },
	{ value: 'Lato, sans-serif', label: 'Lato' },
	{ value: 'Montserrat, sans-serif', label: 'Montserrat' },
	{ value: 'Inter, sans-serif', label: 'Inter' },
	{ value: 'Poppins, sans-serif', label: 'Poppins' },
	{ value: 'Nunito, sans-serif', label: 'Nunito' },
	{ value: 'Raleway, sans-serif', label: 'Raleway' },
	{ value: 'Ubuntu, sans-serif', label: 'Ubuntu' },
	{ value: 'Merriweather, serif', label: 'Merriweather' },
	{ value: 'Playfair Display, serif', label: 'Playfair Display' },
	{ value: 'system-ui, sans-serif', label: 'System UI' },
];

const WEIGHTS = [
	{ value: '', label: 'Default' },
	...Array.from( { length: 9 }, ( _, i ) => ( { value: String( ( i + 1 ) * 100 ), label: String( ( i + 1 ) * 100 ) } ) ),
];

const SIZE_UNITS = [ 'px', 'em', 'rem', 'vw' ];
const LINE_UNITS = [ '', 'em', 'px', '%' ];
const TRANSFORMS = [ '', 'uppercase', 'lowercase', 'capitalize' ];
const LS_UNITS = [ 'px', 'em' ];

export default function FieldTypography( { field, value, onChange } ) {
	const k = field.key;
	const lines = value && value[ k + '_line_height_unit' ] !== undefined ? '' : '';

	function sub( suffix, val ) {
		onChange( { [ k + suffix ]: val } );
	}

	return createElement( 'div', { className: 'lz-field-typography' },
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Font Family' ),
		createElement( 'select', {
			className: 'lz-field-select',
			onChange: ( e ) => sub( '_font_family', e.target.value ),
			value: value[ k + '_font_family' ] || '',
		},
			...FONT_FAMILIES.map( ( f ) =>
				createElement( 'option', { key: f.value, value: f.value }, f.label )
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Weight' ),
		createElement( 'select', {
			className: 'lz-field-select',
			onChange: ( e ) => sub( '_font_weight', e.target.value ),
			value: value[ k + '_font_weight' ] || '',
		},
			...WEIGHTS.map( ( w ) =>
				createElement( 'option', { key: w.value, value: w.value }, w.label )
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Font Size' ),
		createElement( 'div', { className: 'lz-field-unit-wrap' },
			createElement( 'input', {
				type: 'number', className: 'lz-field-input', step: 'any',
				value: value[ k + '_font_size' ] || '',
				onInput: ( e ) => sub( '_font_size', e.target.value ),
			} ),
			createElement( 'select', {
				className: 'lz-field-select',
				value: value[ k + '_font_size_unit' ] || 'px',
				onChange: ( e ) => sub( '_font_size_unit', e.target.value ),
			},
				...SIZE_UNITS.map( ( u ) =>
					createElement( 'option', { key: u, value: u }, u )
				),
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Line Height' ),
		createElement( 'div', { className: 'lz-field-unit-wrap' },
			createElement( 'input', {
				type: 'number', className: 'lz-field-input', step: 'any',
				value: value[ k + '_line_height' ] || '',
				onInput: ( e ) => sub( '_line_height', e.target.value ),
			} ),
			createElement( 'select', {
				className: 'lz-field-select',
				value: value[ k + '_line_height_unit' ] || lines,
				onChange: ( e ) => sub( '_line_height_unit', e.target.value ),
			},
				...LINE_UNITS.map( ( u ) =>
					createElement( 'option', { key: u, value: u }, u || 'None' )
				),
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Text Transform' ),
		createElement( 'select', {
			className: 'lz-field-select',
			value: value[ k + '_text_transform' ] || '',
			onChange: ( e ) => sub( '_text_transform', e.target.value ),
		},
			...TRANSFORMS.map( ( t ) =>
				createElement( 'option', { key: t, value: t }, t || 'None' )
			),
		),
		createElement( 'label', { className: 'lz-field-sub-label' }, 'Letter Spacing' ),
		createElement( 'div', { className: 'lz-field-unit-wrap' },
			createElement( 'input', {
				type: 'number', className: 'lz-field-input', step: 'any',
				value: value[ k + '_letter_spacing' ] || '',
				onInput: ( e ) => sub( '_letter_spacing', e.target.value ),
			} ),
			createElement( 'select', {
				className: 'lz-field-select',
				value: value[ k + '_letter_spacing_unit' ] || 'px',
				onChange: ( e ) => sub( '_letter_spacing_unit', e.target.value ),
			},
				...LS_UNITS.map( ( u ) =>
					createElement( 'option', { key: u, value: u }, u )
				),
			),
		),
	);
}
