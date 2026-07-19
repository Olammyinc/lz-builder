import { createElement } from '@wordpress/element';

export default function FieldForm( { field, value, onChange } ) {
	return createElement( 'input', {
		type: 'text', className: 'lz-field-input',
		value: typeof value === 'object' ? JSON.stringify( value ) : ( value || '' ),
		placeholder: 'Form config',
		onInput: ( e ) => { try { onChange( JSON.parse( e.target.value ) ); } catch { onChange( e.target.value ); } },
	} );
}
