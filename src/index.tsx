import React from 'react';

import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';

import App from '@/admin/components/App';
import './styles.css';
import '@/admin/components.css';

domReady( () => {
	const container = document.getElementById( 'easycommerce-fakerpress-root' )!;
	if ( container ) {
		const root = createRoot( container );
		root.render( <App /> );
	}
} );
