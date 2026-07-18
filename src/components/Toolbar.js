import { createElement, useCallback } from '@wordpress/element';
import { lzFetch } from '../api';

export default function Toolbar( { state, dispatch, data, showNotice } ) {
	const handleSave = useCallback( () => {
		lzFetch( 'save_draft', {} ).then( ( r ) => {
			if ( r && r.success ) {
				showNotice( 'Draft saved!', 'success' );
				dispatch( { type: 'SET_UNSAVED', value: false } );
			} else {
				showNotice(
					( r && r.data && r.data.message ) || 'Could not save.',
					'error'
				);
			}
		} );
	}, [ showNotice, dispatch ] );

	const handlePublish = useCallback( () => {
		lzFetch( 'save_layout', {} ).then( ( r ) => {
			if ( r && r.success ) {
				showNotice( 'Page published!', 'success' );
				dispatch( { type: 'SET_UNSAVED', value: false } );
			} else {
				showNotice(
					( r && r.data && r.data.message ) || 'Could not publish.',
					'error'
				);
			}
		} );
	}, [ showNotice, dispatch ] );

	return createElement( 'div', { className: 'lz-toolbar' },
		createElement( 'div', { className: 'lz-toolbar-left' },
			createElement( 'span', { className: 'lz-toolbar-brand' }, 'Lz Builder' ),
			state.hasUnsaved && createElement( 'span', { className: 'lz-unsaved-badge' }, 'Unsaved' ),
		),
		createElement( 'div', { className: 'lz-toolbar-center' } ),
		createElement( 'div', { className: 'lz-toolbar-right' },
			createElement( 'button', {
				className: 'lz-btn lz-btn-save',
				onClick: handleSave,
				disabled: state.loadingLayout,
			}, 'Save Draft' ),
			createElement( 'button', {
				className: 'lz-btn lz-btn-primary',
				onClick: handlePublish,
				disabled: state.loadingLayout,
			}, 'Publish' ),
			createElement( 'a', {
				className: 'lz-btn lz-btn-exit',
				href: data.exit_url || '#',
			}, 'Exit Builder' ),
		),
	);
}
