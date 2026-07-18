import { createElement, useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { lzFetch } from '../api';

function bindColorFields( container ) {
	const colorFields = container.querySelectorAll( '.lz-color-field' );
	colorFields.forEach( ( cf ) => {
		const swatch = cf.querySelector( '.lz-color-swatch' );
		const textInput = cf.querySelector( '.lz-field-color-text' );
		const nativeInput = cf.querySelector( '.lz-field-color-native' );

		function updateColor( val ) {
			const v = val || 'transparent';
			if ( swatch ) swatch.style.backgroundColor = v;
			if ( textInput ) { textInput.value = v; }
			if ( nativeInput && nativeInput.value !== v ) nativeInput.value = v;
		}

		if ( swatch && nativeInput ) {
			swatch.addEventListener( 'click', () => nativeInput.click() );
		}
		if ( nativeInput ) {
			nativeInput.addEventListener( 'input', ( e ) => updateColor( e.target.value ) );
		}
		if ( textInput ) {
			textInput.addEventListener( 'input', ( e ) => updateColor( e.target.value ) );
		}
	} );
}

function bindButtonGroups( container, doAutoSave ) {
	const btnGroups = container.querySelectorAll( '.lz-field-btn-group' );
	btnGroups.forEach( ( group ) => {
		const options = group.querySelectorAll( '.lz-btn-group-option' );
		const hiddenInput = group.querySelector( 'input[type="hidden"]' );
		options.forEach( ( opt ) => {
			opt.addEventListener( 'click', function () {
				options.forEach( ( o ) => o.classList.remove( 'lz-btn-group-active' ) );
				this.classList.add( 'lz-btn-group-active' );
				if ( hiddenInput ) hiddenInput.value = this.getAttribute( 'data-value' );
				doAutoSave();
			} );
		} );
	} );
}

export default function SettingsPanel( { nodeId, showNotice, postToIframe, dispatch } ) {
	const [ html, setHtml ] = useState( '' );
	const [ loading, setLoading ] = useState( false );
	const formRef = useRef( null );
	const autoSaveTimerRef = useRef( null );
	const boundRef = useRef( false );
	const handlersRef = useRef( [] );

	const doAutoSave = useCallback( () => {
		clearTimeout( autoSaveTimerRef.current );
		if ( ! formRef.current ) return;
		autoSaveTimerRef.current = setTimeout( () => {
			if ( ! formRef.current ) return;
			const form = formRef.current.querySelector( '#lz-settings-form' );
			if ( ! form ) return;
			const inputs = form.querySelectorAll( 'input[name], select[name], textarea[name]' );
			const settings = {};
			inputs.forEach( ( inp ) => {
				const name = inp.getAttribute( 'name' );
				if ( ! name ) return;
				settings[ name ] = inp.type === 'checkbox' ? inp.checked : inp.value;
			} );
			lzFetch( 'save_settings', { node_id: nodeId, settings } ).then( ( r ) => {
				if ( r && r.success && r.data && r.data.html ) {
					dispatch( { type: 'SET_UNSAVED', value: true } );
					postToIframe( {
						action: 'lz_replace_module',
						node_id: nodeId,
						html: r.data.html,
					} );
				}
			} );
		}, 120 );
	}, [ nodeId, postToIframe, dispatch ] );

	useEffect( () => {
		if ( ! nodeId ) {
			setHtml( '' );
			return;
		}
		setLoading( true );
		boundRef.current = false;
		lzFetch( 'render_settings_form', { node_id: nodeId } ).then( ( r ) => {
			setLoading( false );
			if ( r && r.success && r.data && r.data.html ) {
				setHtml( r.data.html );
			} else {
				const msg = ( r && r.data && r.data.message ) || 'Could not load settings.';
				setHtml( '' );
				showNotice( msg, 'error' );
			}
		} );
	}, [ nodeId ] );

	useEffect( () => {
		if ( ! html || ! formRef.current || boundRef.current ) return;
		boundRef.current = true;
		const container = formRef.current;
		const h = [];

		bindColorFields( container );
		bindButtonGroups( container, doAutoSave );

		const inputs = container.querySelectorAll(
			'input[name], select[name], textarea[name]'
		);
		inputs.forEach( ( inp ) => {
			const handler = () => doAutoSave();
			inp.addEventListener( 'input', handler );
			inp.addEventListener( 'change', handler );
			h.push( { el: inp, type: 'input', handler } );
			h.push( { el: inp, type: 'change', handler } );
		} );

		const backBtn = container.querySelector( '#lz-settings-back' );
		if ( backBtn ) {
			const handler = ( e ) => {
				e.preventDefault();
				clearTimeout( autoSaveTimerRef.current );
				dispatch( { type: 'BACK_TO_MODULES' } );
			};
			backBtn.addEventListener( 'click', handler );
			h.push( { el: backBtn, type: 'click', handler } );
		}

		const form = container.querySelector( '#lz-settings-form' );
		if ( form ) {
			const handler = ( e ) => {
				e.preventDefault();
				doAutoSave();
				setTimeout( () => dispatch( { type: 'BACK_TO_MODULES' } ), 400 );
			};
			form.addEventListener( 'submit', handler );
			h.push( { el: form, type: 'submit', handler } );
		}

		handlersRef.current = h;

		return () => {
			h.forEach( ( { el, type, handler } ) => {
				el.removeEventListener( type, handler );
			} );
			boundRef.current = false;
		};
	}, [ html, doAutoSave ] );

	if ( ! nodeId ) {
		return createElement( 'div', { className: 'lz-action-panel' },
			createElement( 'p', null, 'Select a module on the page to edit its settings.' ),
		);
	}

	if ( loading ) {
		return createElement( 'div', { className: 'lz-action-panel' },
			createElement( 'p', null, 'Loading settings\u2026' ),
		);
	}

	return createElement( 'div', {
		ref: formRef,
		className: 'lz-settings-panel',
		dangerouslySetInnerHTML: { __html: html },
	} );
}
