import { createElement, useState, useEffect, useCallback } from '@wordpress/element';
import { lzFetch } from '../api';

export default function TemplateList( { showNotice, refreshLayout, postToIframe } ) {
	const [ templates, setTemplates ] = useState( [] );
	const [ loading, setLoading ] = useState( true );
	const [ search, setSearch ] = useState( '' );

	useEffect( () => {
		lzFetch( 'get_templates', {} ).then( ( r ) => {
			setLoading( false );
			if ( r && r.success && r.data && r.data.templates ) {
				setTemplates( r.data.templates );
			}
		} );
	}, [] );

	const handleApply = useCallback( ( templateId ) => {
		showNotice( 'Applying template\u2026', 'success' );
		lzFetch( 'apply_template', { template_id: templateId } ).then( ( r ) => {
			if ( r && r.success ) {
				showNotice( 'Template applied!', 'success' );
				if ( refreshLayout ) refreshLayout();
				if ( postToIframe ) {
					postToIframe( { action: 'lz_render_layout', html: '' } );
				}
			} else {
				showNotice(
					( r && r.data && r.data.message ) || 'Could not apply template.',
					'error'
				);
			}
		} );
	}, [ showNotice, refreshLayout, postToIframe ] );

	const filtered = search
		? templates.filter( ( tmpl ) =>
				tmpl.title.toLowerCase().includes( search.toLowerCase() )
		  )
		: templates;

	if ( loading ) {
		return createElement( 'div', { className: 'lz-empty-state' },
			createElement( 'p', null, 'Loading templates\u2026' ),
		);
	}

	if ( templates.length === 0 ) {
		return createElement( 'div', { className: 'lz-empty-state' },
			createElement( 'p', null, 'No templates available.' ),
		);
	}

	return createElement( 'div', { className: 'lz-templates-panel' },
		createElement( 'div', { className: 'lz-search-bar' },
			createElement( 'input', {
				type: 'search',
				className: 'lz-search-input',
				placeholder: 'Search templates\u2026',
				value: search,
				onInput: ( e ) => setSearch( e.target.value ),
			} ),
		),
		createElement( 'div', { className: 'lz-template-list' },
			...filtered.map( ( tmpl ) =>
				createElement( 'div', { key: tmpl.id, className: 'lz-template-item' },
					createElement( 'div', { className: 'lz-template-info' },
						createElement( 'strong', null, tmpl.title ),
					),
					createElement( 'button', {
						className: 'lz-btn lz-btn-primary',
						onClick: () => handleApply( tmpl.id ),
					}, 'Apply' ),
				)
			),
		),
	);
}
