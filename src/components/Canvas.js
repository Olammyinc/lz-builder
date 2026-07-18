import { createElement, useCallback, useState } from '@wordpress/element';
import { getPreviewUrl, lzFetch } from '../api';

export default function Canvas( { data, updatePreview, showNotice, iframeRef, dispatch, refreshLayout } ) {
	const [ dragOver, setDragOver ] = useState( false );
	const previewUrl = getPreviewUrl();

	const handleDrop = useCallback( ( e ) => {
		e.preventDefault();
		setDragOver( false );
		const slug = e.dataTransfer.getData( 'text/plain' );
		if ( slug ) {
			lzFetch( 'add_module', { module: slug } ).then( ( r ) => {
				if ( r && r.success ) {
					if ( r.data && r.data.layout ) updatePreview( r.data.layout );
					dispatch( { type: 'SET_UNSAVED', value: true } );
					showNotice( 'Module added!', 'success' );
					refreshLayout();
				} else {
					showNotice(
						( r && r.data && r.data.message ) || 'Could not add module.',
						'error'
					);
				}
			} );
		}
	}, [ updatePreview, showNotice, dispatch, refreshLayout ] );

	const handleDragOver = useCallback( ( e ) => {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
	}, [] );

	const handleDragEnter = useCallback( () => {
		setDragOver( true );
	}, [] );

	const handleDragLeave = useCallback( ( e ) => {
		if ( ! e.currentTarget.contains( e.relatedTarget ) ) {
			setDragOver( false );
		}
	}, [] );

	return createElement( 'div', {
		className: 'lz-builder-canvas' + ( dragOver ? ' lz-builder-canvas--drag' : '' ),
		onDrop: handleDrop,
		onDragOver: handleDragOver,
		onDragEnter: handleDragEnter,
		onDragLeave: handleDragLeave,
	},
		createElement( 'div', {
			className: 'lz-drop-zone' + ( dragOver ? ' lz-drop-zone--active' : '' ),
		},
			createElement( 'div', { className: 'lz-drop-zone-text' }, 'Drop module here' ),
		),
		createElement( 'iframe', {
			ref: iframeRef,
			id: 'lz-builder-iframe',
			className: 'lz-builder-frame',
			src: previewUrl,
		} ),
	);
}
