import { createElement, useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { lzFetch } from '../api';
import registry from '../fields/registry';

const SERVER_RENDERED_TYPES = new Set( [ 'editor' ] );

const COMPOUND_TYPES = new Set( [
	'typography', 'border', 'dimension', 'spacing', 'link',
	'shadow', 'gradient', 'video', 'form',
] );

export default function SettingsPanel( { nodeId, showNotice, postToIframe, dispatch } ) {
	const [ schema, setSchema ] = useState( null );
	const [ values, setValues ] = useState( {} );
	const [ loading, setLoading ] = useState( false );
	const [ fallbackHtml, setFallbackHtml ] = useState( null );
	const autoSaveTimerRef = useRef( null );
	const pendingRef = useRef( null );
	const mountedRef = useRef( true );
	const fetchIdRef = useRef( 0 );
	const inFlightRef = useRef( false );

	const flushSave = useCallback( ( entry ) => {
		clearTimeout( autoSaveTimerRef.current );
		if ( ! entry || ! entry.target || inFlightRef.current ) return;
		inFlightRef.current = true;
		lzFetch( 'save_settings', { node_id: entry.target, settings: entry.values } ).then( ( r ) => {
			inFlightRef.current = false;
			if ( ! mountedRef.current ) return;
			if ( r && r.success && r.data && r.data.html ) {
				dispatch( { type: 'SET_UNSAVED', value: true } );
				postToIframe( {
					action: 'lz_replace_module',
					node_id: entry.target,
					html: r.data.html,
				} );
			}
		} ).catch( () => {
			inFlightRef.current = false;
		} );
		pendingRef.current = null;
	}, [ postToIframe, dispatch ] );

	const doAutoSave = useCallback( ( newValues ) => {
		pendingRef.current = { target: nodeId, values: newValues };
		clearTimeout( autoSaveTimerRef.current );
		autoSaveTimerRef.current = setTimeout( () => {
			if ( ! mountedRef.current ) return;
			if ( pendingRef.current ) flushSave( pendingRef.current );
		}, 300 );
	}, [ nodeId, flushSave ] );

	const handleChange = useCallback( ( change ) => {
		setValues( ( prev ) => {
			if ( typeof change === 'object' && change !== null ) {
				const next = { ...prev, ...change };
				doAutoSave( next );
				return next;
			}
			return prev;
		} );
	}, [ doAutoSave ] );

	const handleSingleChange = useCallback( ( key, value ) => {
		setValues( ( prev ) => {
			const next = { ...prev, [ key ]: value };
			doAutoSave( next );
			return next;
		} );
	}, [ doAutoSave ] );

	const needsServerRender = useCallback( ( schemaData ) => {
		if ( ! schemaData || ! schemaData.tabs ) return false;
		for ( const tab of schemaData.tabs ) {
			for ( const section of tab.sections || [] ) {
				for ( const field of Object.values( section.fields || {} ) ) {
					if ( SERVER_RENDERED_TYPES.has( field.type ) ) return true;
				}
			}
		}
		return false;
	}, [] );

	useEffect( () => {
		if ( ! nodeId ) {
			setSchema( null );
			setValues( {} );
			setFallbackHtml( null );
			pendingRef.current = null;
			return;
		}
		const fetchId = ++fetchIdRef.current;
		setLoading( true );
		setSchema( null );
		setFallbackHtml( null );
		pendingRef.current = null;
		lzFetch( 'get_settings_schema', { node_id: nodeId } ).then( ( r ) => {
			if ( ! mountedRef.current || fetchId !== fetchIdRef.current ) return;
			setLoading( false );
			if ( r && r.success && r.data ) {
				if ( needsServerRender( r.data ) ) {
					const capturedId = fetchId;
					lzFetch( 'render_settings_form', { node_id: nodeId } ).then( ( formR ) => {
						if ( ! mountedRef.current || capturedId !== fetchIdRef.current ) return;
						if ( formR && formR.success && formR.data && formR.data.html ) {
							setFallbackHtml( formR.data.html );
						}
					} );
				} else {
					setSchema( r.data );
					setValues( r.data.values || {} );
				}
			} else {
				const msg = ( r && r.data && r.data.message ) || 'Could not load settings.';
				showNotice( msg, 'error' );
				setSchema( {} );
				setValues( {} );
			}
		} );
	}, [ nodeId, needsServerRender ] );

	useEffect( () => {
		mountedRef.current = true;
		return () => {
			if ( pendingRef.current && ! inFlightRef.current ) {
				flushSave( pendingRef.current );
			}
			mountedRef.current = false;
		};
	}, [ nodeId, flushSave ] );

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

	if ( fallbackHtml ) {
		return createElement( 'div', {
			className: 'lz-settings-panel',
			dangerouslySetInnerHTML: { __html: fallbackHtml },
		} );
	}

	if ( ! schema ) {
		return createElement( 'div', { className: 'lz-action-panel' },
			createElement( 'p', null, 'Loading settings\u2026' ),
		);
	}

	return createElement( 'div', { className: 'lz-settings-panel' },
		createElement( 'div', { className: 'lz-settings-header' },
			createElement( 'h3', { className: 'lz-settings-title' }, schema.title ),
			createElement( 'button', {
				type: 'button',
				className: 'lz-btn lz-btn-back',
				onClick: ( e ) => {
					e.preventDefault();
					if ( pendingRef.current ) flushSave( pendingRef.current );
					dispatch( { type: 'BACK_TO_MODULES' } );
				},
			}, '\u2190 Back' ),
		),
		...schema.tabs.map( ( tab, ti ) =>
			createElement( 'div', { key: ti, className: 'lz-settings-tab' },
				tab.title && createElement( 'h4', { className: 'lz-settings-tab-title' }, tab.title ),
				...tab.sections.map( ( section, si ) =>
					createElement( 'div', { key: si },
						section.title && createElement( 'h5', { className: 'lz-settings-section-title' }, section.title ),
						createElement( 'div', { className: 'lz-settings-fields' },
							...Object.entries( section.fields || {} ).map( ( [ fieldKey, field ] ) => {
								const FieldComponent = registry[ field.type ];
								const isCompound = COMPOUND_TYPES.has( field.type );
								const fieldValue = isCompound
									? values
									: ( values[ fieldKey ] !== undefined ? values[ fieldKey ] : ( field.default ?? '' ) );

								const changeHandler = ( val ) => {
									if ( typeof val === 'object' && val !== null ) {
										handleChange( val );
									} else {
										handleSingleChange( fieldKey, val );
									}
								};

								return createElement( 'div', {
									key: fieldKey,
									className: 'lz-field lz-field-' + ( field.type || 'text' ),
								},
									createElement( 'label', { className: 'lz-field-label' }, field.label || fieldKey ),
									FieldComponent
										? createElement( FieldComponent, {
												field: { ...field, key: fieldKey },
												value: fieldValue !== undefined ? fieldValue : '',
												onChange: changeHandler,
										  } )
										: createElement( 'div', { className: 'lz-field-unknown' },
												'Unknown field type: ' + ( field.type || 'unknown' ),
										  ),
								);
							} ),
						),
					)
				),
			)
		),
		createElement( 'div', { className: 'lz-settings-actions' },
			createElement( 'button', {
				type: 'button',
				className: 'lz-btn lz-btn-primary lz-btn-save-settings',
				onClick: () => {
					if ( pendingRef.current ) flushSave( pendingRef.current );
					setTimeout( () => dispatch( { type: 'BACK_TO_MODULES' } ), 100 );
				},
			}, 'Save' ),
		),
	);
}
