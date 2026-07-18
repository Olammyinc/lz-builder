export function lzFetch( action, formData = {} ) {
	const data = window.LZBuilderData;
	const fd = new FormData();
	fd.append( 'action', 'lz_builder_' + action );
	fd.append( 'nonce', data.nonce );
	fd.append( 'post_id', data.post_id );

	Object.keys( formData ).forEach( ( k ) => {
		const v = formData[ k ];
		fd.append( k, typeof v === 'object' ? JSON.stringify( v ) : v );
	} );

	return fetch( data.ajax_url, {
		method: 'POST',
		credentials: 'same-origin',
		body: fd,
	} )
		.then( ( r ) =>
			r.text().then( ( t ) => {
				try {
					return JSON.parse( t );
				} catch {
					return {
						success: false,
						data: { message: 'Invalid server response.' },
					};
				}
			} )
		)
		.catch( () => ( { success: false, data: { message: 'Network error.' } } ) );
}

export function getPreviewUrl() {
	const url = new URL( window.location.href );
	url.searchParams.delete( 'lz_builder' );
	url.searchParams.set( 'lz_builder_preview', '1' );
	return url.toString();
}
