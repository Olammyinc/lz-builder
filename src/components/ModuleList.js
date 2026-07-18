import { createElement, useState, useCallback } from '@wordpress/element';
import { lzFetch } from '../api';

const ICONS = {
	heading: '\uD83D\uDD0D',
	'text-editor': '\uD83D\uDCDD',
	photo: '\uD83D\uDDBC',
	button: '\uD83D\uDD17',
	video: '\uD83C\uDFA5',
	row: '\u2B1C',
	column: '\u25AD',
};

export default function ModuleList( { modules, lockedModules, showNotice, updatePreview, dispatch, refreshLayout } ) {
	const [ search, setSearch ] = useState( '' );

	const handleAddModule = useCallback( ( slug ) => {
		lzFetch( 'add_module', { module: slug } ).then( ( r ) => {
			if ( r && r.success ) {
				if ( r.data && r.data.layout ) {
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
	}, [ showNotice, updatePreview, dispatch, refreshLayout ] );

	const handleDragStart = useCallback( ( e, slug ) => {
		e.dataTransfer.setData( 'text/plain', slug );
		e.dataTransfer.effectAllowed = 'copy';
	}, [] );

	const isLocked = ( slug ) => {
		return lockedModules.some( ( lm ) => lm.slug === slug );
	};

	const getLockedModule = ( slug ) => {
		return lockedModules.find( ( lm ) => lm.slug === slug );
	};

	const filtered = modules
		.map( ( cat ) => ( {
			...cat,
			modules: cat.modules.filter( ( mod ) => {
				if ( ! search ) return true;
				const q = search.toLowerCase();
				return (
					mod.name.toLowerCase().includes( q ) ||
					mod.slug.toLowerCase().includes( q )
				);
			} ),
		} ) )
		.filter( ( cat ) => cat.modules.length > 0 );

	return createElement( 'div', { className: 'lz-modules-panel' },
		createElement( 'div', { className: 'lz-search-bar' },
			createElement( 'input', {
				type: 'search',
				className: 'lz-search-input',
				placeholder: 'Search modules\u2026',
				value: search,
				onInput: ( e ) => setSearch( e.target.value ),
			} ),
		),
		createElement( 'div', { className: 'lz-module-list' },
			...filtered.map( ( cat ) =>
				createElement( 'div', { key: cat.slug, className: 'lz-module-category' },
					createElement( 'div', { className: 'lz-module-category-title' }, cat.name ),
					createElement( 'div', { className: 'lz-module-grid' },
						...cat.modules.map( ( mod ) => {
							const locked = isLocked( mod.slug );
							const icon = ICONS[ mod.slug ] || '\uD83D\uDCE6';
							const lockedData = getLockedModule( mod.slug );

							return createElement( 'div', {
								key: mod.slug,
								className: 'lz-module-card' + ( locked ? ' lz-module-card--locked' : '' ),
								draggable: ! locked,
								tabIndex: locked ? -1 : 0,
								role: 'button',
								onDragStart: ( e ) => ! locked && handleDragStart( e, mod.slug ),
								onClick: () => ! locked && handleAddModule( mod.slug ),
								onKeyDown: ( e ) => {
									if ( ! locked && ( e.key === 'Enter' || e.key === ' ' ) ) {
										e.preventDefault();
										handleAddModule( mod.slug );
									}
								},
							},
								createElement( 'div', { className: 'lz-module-card-icon' },
									locked ? '\uD83D\uDD12' : icon,
								),
								createElement( 'div', { className: 'lz-module-card-name' }, mod.name ),
								locked && createElement( 'div', { className: 'lz-locked-overlay' },
									createElement( 'span', null, 'Requires Upgrade' ),
									lockedData && lockedData.upgrade_url &&
										createElement( 'a', {
											href: lockedData.upgrade_url,
											className: 'lz-upgrade-btn',
											target: '_blank',
											rel: 'noopener noreferrer',
										}, 'Upgrade' ),
								),
							);
						} ),
					),
				)
			),
		),
	);
}
