import React from 'react';
import { Outlet } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { GlobeAltIcon } from '@heroicons/react/24/outline';

export default function RootLayout() {
	// Get locale information from localized script
	const localeInfo = window.easycommerceFakerpressApi?.locale || {
		faker: 'en_US',
		label: 'English (United States)',
		wordpress: 'en_US',
	};

	return (
		<div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
			<div className="mb-8">
				<div className="flex items-start justify-between">
					<div className="flex-1">
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
					<div className="ml-6 flex-shrink-0">
						<div className="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
							<GlobeAltIcon className="h-5 w-5" aria-hidden="true" />
							<span className="font-semibold">
								{ __( 'Data Locale:', 'easycommerce-fakerpress' ) }
							</span>
							<span>{ localeInfo.label }</span>
						</div>
					</div>
				</div>
			</div>
			<Outlet />
		</div>
	);
}
