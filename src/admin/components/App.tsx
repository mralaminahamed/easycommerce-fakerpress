import React from 'react';
import { createHashRouter, RouterProvider } from 'react-router-dom';

import GeneratorPage from '@/admin/components/Pages/GeneratorPage';
import HomePage from '@/admin/components/Pages/HomePage';
import RootLayout from '@/admin/components/Pages/RootLayout';

// Create router using createHashRouter with route objects
const router = createHashRouter( [
	{
		path: '/',
		element: <RootLayout />,
		children: [
			{
				index: true,
				element: <HomePage />,
			},
			{
				path: 'generator/:type',
				element: <GeneratorPage />,
			},
		],
	},
] );

export default function App() {
	return <RouterProvider router={ router } />;
}
