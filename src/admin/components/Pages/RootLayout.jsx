import React from 'react';
import { Outlet } from 'react-router-dom';
import { __ } from '@wordpress/i18n';

export default function RootLayout() {
	return (
		<div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
			<div className="mb-8">
				<h1 className="text-3xl font-bold text-gray-900 tracking-tight">
					{ __( 'EasyCommerce FakerPress', 'easycommerce-fakerpress' ) }
				</h1>
				<p className="mt-2 text-sm text-gray-500 leading-6">
					{ __(
						'Comprehensive EasyCommerce test data generator with 10 specialized generators, real-time validation, and modern interface.',
						'easycommerce-fakerpress',
					) }
				</p>
			</div>
			<Outlet />
		</div>
	);
}
