import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import React from 'react';

import App from './components/App';
import './styles.css';

domReady( () => {
	const container = document.getElementById( 'easycommerce-fakerpress-root' );
	if ( container ) {
		const root = createRoot( container );
		root.render( <App /> );
	}
} );