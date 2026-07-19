import { createElement, useCallback } from '@wordpress/element';
import { lzFetch } from '../api';
import ModuleList from './ModuleList';
import TemplateList from './TemplateList';
import SettingsPanel from './SettingsPanel';

export default function Sidebar( { state, dispatch, data, showNotice, postToIframe, updatePreview, refreshLayout } ) {
	const tabs = [
		{ key: 'modules', label: 'Modules' },
		{ key: 'templates', label: 'Templates' },
		{ key: 'settings', label: 'Settings' },
	];

	const handleTabClick = useCallback( ( e, tab ) => {
		e.preventDefault();
		dispatch( { type: 'SET_TAB', tab } );
	}, [ dispatch ] );

	const handleTabKeyDown = useCallback( ( e, tab, index ) => {
		let newIndex = index;
		if ( e.key === 'ArrowRight' ) {
			newIndex = ( index + 1 ) % tabs.length;
		} else if ( e.key === 'ArrowLeft' ) {
			newIndex = ( index - 1 + tabs.length ) % tabs.length;
		} else {
			return;
		}
		e.preventDefault();
		dispatch( { type: 'SET_TAB', tab: tabs[ newIndex ].key } );
		const els = e.currentTarget.parentElement.querySelectorAll( '.lz-tab' );
		if ( els[ newIndex ] ) els[ newIndex ].focus();
	}, [ dispatch, tabs ] );

	const handleDeleteNode = useCallback( () => {
		const nodeId = state.editingNodeId;
		if ( ! nodeId ) return;
		lzFetch( 'delete_node', { node_id: nodeId } ).then( ( r ) => {
			if ( r && r.success ) {
				if ( r.data && r.data.layout ) updatePreview( r.data.layout );
				showNotice( 'Node deleted.', 'success' );
				dispatch( { type: 'SET_UNSAVED', value: true } );
				dispatch( { type: 'BACK_TO_MODULES' } );
				refreshLayout();
			} else {
				showNotice( ( r && r.data && r.data.message ) || 'Could not delete node.', 'error' );
			}
		} );
	}, [ state.editingNodeId, showNotice, dispatch, refreshLayout, updatePreview ] );

	const handleDuplicateNode = useCallback( () => {
		const nodeId = state.editingNodeId;
		if ( ! nodeId ) return;
		lzFetch( 'duplicate_node', { node_id: nodeId } ).then( ( r ) => {
			if ( r && r.success ) {
				if ( r.data && r.data.layout ) updatePreview( r.data.layout );
				showNotice( 'Node duplicated.', 'success' );
				dispatch( { type: 'SET_UNSAVED', value: true } );
				refreshLayout();
			} else {
				showNotice( ( r && r.data && r.data.message ) || 'Could not duplicate.', 'error' );
			}
		} );
	}, [ state.editingNodeId, showNotice, dispatch, refreshLayout, updatePreview ] );

	const handleAddRow = useCallback( ( layout ) => {
		lzFetch( 'add_row', { layout } ).then( ( r ) => {
			if ( r && r.success ) {
				if ( r.data && r.data.layout ) updatePreview( r.data.layout );
				showNotice( 'Row added!', 'success' );
				dispatch( { type: 'SET_UNSAVED', value: true } );
				refreshLayout();
			} else {
				showNotice( ( r && r.data && r.data.message ) || 'Could not add row.', 'error' );
			}
		} );
	}, [ showNotice, dispatch, refreshLayout, updatePreview ] );

	const renderContent = () => {
		switch ( state.activeTab ) {
			case 'modules':
				return createElement( ModuleList, {
					modules: data.modules || [],
					lockedModules: data.locked_modules || [],
					showNotice,
					updatePreview,
					dispatch,
					refreshLayout,
					handleAddRow,
				} );
			case 'templates':
				return createElement( TemplateList, {
					showNotice,
					refreshLayout,
					postToIframe,
				} );
			case 'settings':
				return createElement( 'div', { className: 'lz-settings-panel-wrap' },
					state.editingNodeId &&
						createElement( 'div', { className: 'lz-node-actions' },
							createElement( 'button', {
								className: 'lz-btn lz-btn-danger',
								onClick: handleDeleteNode,
							}, 'Delete' ),
							createElement( 'button', {
								className: 'lz-btn lz-btn-save',
								onClick: handleDuplicateNode,
							}, 'Duplicate' ),
						),
					createElement( SettingsPanel, {
						nodeId: state.editingNodeId,
						showNotice,
						postToIframe,
						dispatch,
					} ),
				);
			default:
				return createElement( 'div', { className: 'lz-action-panel' },
					createElement( 'p', null, 'Select a module on the page to edit its settings.' ),
				);
		}
	};

	return createElement( 'div', { className: 'lz-builder-sidebar' },
		createElement( 'div', { className: 'lz-tab-bar', role: 'tablist' },
			...tabs.map( ( tab, index ) =>
				createElement( 'button', {
					key: tab.key,
					role: 'tab',
					'aria-selected': state.activeTab === tab.key,
					'aria-controls': 'lz-sidebar-content',
					tabIndex: state.activeTab === tab.key ? 0 : -1,
					className: 'lz-tab' + ( state.activeTab === tab.key ? ' lz-tab--active' : '' ),
					'data-tab': tab.key,
					onClick: ( e ) => handleTabClick( e, tab.key ),
					onKeyDown: ( e ) => handleTabKeyDown( e, tab.key, index ),
				}, tab.label )
			),
		),
		createElement( 'div', { className: 'lz-sidebar-content', id: 'lz-sidebar-content' },
			renderContent(),
		),
	);
}
