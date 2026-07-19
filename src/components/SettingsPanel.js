import { createElement, useState, useEffect, useCallback, useRef } from '@wordpress/element';
import { lzFetch } from '../api';
import registry from '../fields/registry';

const COMPOUND_TYPES = new Set( [
	'typography', 'border', 'dimension', 'spacing', 'link',
	'shadow', 'gradient', 'video', 'form',
] );

export default function SettingsPanel( { nodeId, showNotice, postToIframe, dispatch } ) {
	const [ schema, setSchema ] = useState( null );
	const [ values, setValues ] = useState( {} );
	const [ loading, setLoading ] = useState( false );
	const autoSaveTimerRef = useRef( null );
	const mountedRef = useRef( true );

	const doAutoSave = useCallback( ( newValues ) => {
		clearTimeout( autoSaveTimerRef.current );
		autoSaveTimerRef.current = setTimeout( () => {
			if ( ! mountedRef.current ) return;
			lzFetch( 'save_settings', { node_id: nodeId, settings: newValues } ).then( ( r ) => {
				if ( r && r.success && r.data && r.data.html ) {
					dispatch( { type: 'SET_UNSAVED', value: true } );
					postToIframe( {
						action: 'lz_replace_module',
						node_id: nodeId,
						html: r.data.html,
					} );
				}
			} );
		}, 300 );
	}, [ nodeId, postToIframe, dispatch ] );

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

	const fetchIdRef = useRef( 0 );

	useEffect( () => {
		if ( ! nodeId ) {
			setSchema( null );
			setValues( {} );
			return;
		}
		const fetchId = ++fetchIdRef.current;
		setLoading( true );
		setSchema( null );
		lzFetch( 'get_settings_schema', { node_id: nodeId } ).then( ( r ) => {
			if ( ! mountedRef.current || fetchId !== fetchIdRef.current ) return;
			setLoading( false );
			if ( r && r.success && r.data ) {
				setSchema( r.data );
				setValues( r.data.values || {} );
			} else {
				const msg = ( r && r.data && r.data.message ) || 'Could not load settings.';
				showNotice( msg, 'error' );
				setSchema( {} );
				setValues( {} );
			}
		} );
	}, [ nodeId ] );

	useEffect( () => {
		mountedRef.current = true;
		return () => {
			mountedRef.current = false;
			clearTimeout( autoSaveTimerRef.current );
		};
	}, [] );

	if ( ! nodeId ) {
		return createElement( 'div', { className: 'lz-action-panel' },
			createElement( 'p', null, 'Select a module on the page to edit its settings.' ),
		);
	}

	if ( loading || ! schema ) {
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
					clearTimeout( autoSaveTimerRef.current );
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
					doAutoSave( values );
					setTimeout( () => dispatch( { type: 'BACK_TO_MODULES' } ), 400 );
				},
			}, 'Save' ),
		),
	);
}
