import { createElement, useCallback } from '@wordpress/element';
import { getPreviewUrl, lzFetch } from '../api';

export default function Canvas( { data, updatePreview, showNotice, postToIframe, iframeRef, dispatch, refreshLayout, isDragging } ) {
	const previewUrl = getPreviewUrl();

	const handleDrop = useCallback( ( e ) => {
		e.preventDefault();
		const slug = e.dataTransfer.getData( 'text/plain' );
		if ( slug ) {
			lzFetch( 'add_module', { module: slug } ).then( ( r ) => {
				if ( r && r.success ) {
					if ( r.data && r.data.html && r.data.parent_id ) {
						postToIframe( { action: 'lz_append_to_column', column_id: r.data.parent_id, html: r.data.html, layout: r.data.layout } );
					} else if ( r.data && r.data.layout ) {
						updatePreview( r.data.layout );
					}
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
	}, [ updatePreview, showNotice, postToIframe, dispatch, refreshLayout ] );

	const handleDragOver = useCallback( ( e ) => {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
	}, [] );

	return createElement( 'div', {
		className: 'lz-builder-canvas',
		onDrop: handleDrop,
		onDragOver: handleDragOver,
	},
		createElement( 'div', {
			className: 'lz-drop-zone' + ( isDragging ? ' lz-drop-zone--active' : '' ),
		},
			createElement( 'div', { className: 'lz-drop-zone-text' }, 'Drop module here' ),
		),
		createElement( 'iframe', {
			ref: iframeRef,
			id: 'lz-builder-iframe',
			className: 'lz-builder-frame',
			title: 'Lz Builder Preview',
			src: previewUrl,
			style: isDragging ? { pointerEvents: 'none' } : undefined,
		} ),
	);
}
