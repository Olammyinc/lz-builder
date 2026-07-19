import { createElement } from '@wordpress/element';

export default function Notices( { notices, dispatch } ) {
	if ( ! notices || notices.length === 0 ) return null;

	return createElement( 'div', { className: 'lz-notices' },
		...notices.map( ( notice ) =>
			createElement( 'div', {
				key: notice.id,
				className: 'lz-notice lz-notice--' + ( notice.type || 'success' ),
			},
				createElement( 'span', { className: 'lz-notice-text' }, notice.message ),
				createElement( 'button', {
					type: 'button',
					className: 'lz-notice-dismiss',
					'aria-label': 'Dismiss',
					onClick: () => dispatch( { type: 'REMOVE_NOTICE', id: notice.id } ),
				}, '\u00D7' ),
			)
		),
	);
}
