import { render, createElement } from '@wordpress/element';
import App from './components/App';

const root = document.getElementById('lz-builder-root');
if (root && window.LZBuilderData) {
	render(createElement(App, { data: window.LZBuilderData }), root);
}
