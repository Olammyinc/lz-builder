import { createElement, useReducer, useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { reducer, initialState } from '../store';
import { lzFetch } from '../api';
import Toolbar from './Toolbar';
import Sidebar from './Sidebar';
import Canvas from './Canvas';
import Notices from './Notices';

export default function App( { data } ) {
	const [ state, dispatch ] = useReducer( reducer, initialState );
	const [ isDragging, setIsDragging ] = useState( false );
	const iframeRef = useRef( null );
	const dragCounter = useRef( 0 );

	const showNotice = useCallback( ( message, type = 'success', textOnly = false ) => {
		const id = Date.now() + ( Math.random() * 1000 );
		dispatch( { type: 'ADD_NOTICE', id, message, noticeType: type, textOnly } );
		setTimeout( () => {
			dispatch( { type: 'REMOVE_NOTICE', id } );
		}, type === 'success' ? 3000 : 5000 );
	}, [] );

	const postToIframe = useCallback( ( msg ) => {
		if ( iframeRef.current && iframeRef.current.contentWindow ) {
			iframeRef.current.contentWindow.postMessage( msg, window.location.origin );
		}
	}, [] );

	const updatePreview = useCallback( ( html ) => {
		postToIframe( { action: 'lz_render_layout', html } );
	}, [ postToIframe ] );

	const refreshLayout = useCallback( () => {
		lzFetch( 'get_layout', { status: 'draft' } ).then( ( r ) => {
			const layout = ( r && r.success && r.data && r.data.data ) || [];
			dispatch( { type: 'SET_LAYOUT', layout } );
			dispatch( { type: 'SET_LAYOUT_LOADED' } );
		} );
	}, [] );

	// Global drag-state tracking so the iframe drop overlay works reliably
	// across Chrome and Firefox.
	useEffect( () => {
		function handleDragStart() {
			dragCounter.current += 1;
			setIsDragging( true );
		}
		function handleDragEnd() {
			dragCounter.current = Math.max( 0, dragCounter.current - 1 );
			if ( dragCounter.current === 0 ) {
				setIsDragging( false );
			}
		}
		document.addEventListener( 'dragstart', handleDragStart );
		document.addEventListener( 'dragend', handleDragEnd );
		return () => {
			document.removeEventListener( 'dragstart', handleDragStart );
			document.removeEventListener( 'dragend', handleDragEnd );
		};
	}, [] );

	useEffect( () => {
		refreshLayout();
	}, [ refreshLayout ] );

	useEffect( () => {
		function handleMessage( event ) {
			if ( event.origin !== window.location.origin ) return;
			if ( ! event.data || ! event.data.action ) return;
			if ( event.data.action === 'lz_open_settings' && event.data.node_id ) {
				dispatch( { type: 'EDIT_NODE', nodeId: event.data.node_id } );
			}
			if ( event.data.action === 'lz_column_drop' && event.data.module ) {
				lzFetch( 'add_module', { module: event.data.module, parent_id: event.data.parent_id } ).then( ( r ) => {
					if ( r && r.success ) {
						if ( r.data && r.data.layout ) updatePreview( r.data.layout );
						dispatch( { type: 'SET_UNSAVED', value: true } );
						showNotice( 'Module added!', 'success' );
						refreshLayout();
					} else {
						showNotice( ( r && r.data && r.data.message ) || 'Could not add module.', 'error' );
					}
				} );
			}
		}
		window.addEventListener( 'message', handleMessage );
		return () => window.removeEventListener( 'message', handleMessage );
	}, [ updatePreview, refreshLayout, showNotice ] );

	return createElement( 'div', { className: 'lz-builder-root-inner' },
		createElement( Notices, { notices: state.notices, dispatch } ),
		createElement( Toolbar, { state, dispatch, data, showNotice } ),
		createElement( 'div', { className: 'lz-builder-workspace' },
			createElement( Sidebar, { state, dispatch, data, showNotice, postToIframe, updatePreview, refreshLayout } ),
			createElement( Canvas, { data, updatePreview, showNotice, iframeRef, dispatch, refreshLayout, isDragging } ),
		),
	);
}
