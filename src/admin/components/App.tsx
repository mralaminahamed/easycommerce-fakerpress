import React from 'react';
import { createHashRouter, RouterProvider } from 'react-router-dom';

import { ThemeProvider } from '@/admin/theme/ThemeProvider';
import { StatsProvider } from '@/admin/providers/StatsProvider';
import GeneratorPage from '@/admin/components/Pages/GeneratorPage';
import HomePage from '@/admin/components/Pages/HomePage';
import PluginsPage from '@/admin/components/Pages/PluginsPage';
import RootLayout from '@/admin/components/Pages/RootLayout';
import SettingsPage from '@/admin/components/Pages/SettingsPage';

const router = createHashRouter([
  {
    path: '/',
    element: <RootLayout />,
    children: [
      { index: true,              element: <HomePage />      },
      { path: 'generator/:type',  element: <GeneratorPage /> },
      { path: 'settings',         element: <SettingsPage />  },
      { path: 'plugins',          element: <PluginsPage />   },
    ],
  },
]);

export default function App() {
  return (
    <ThemeProvider>
      <StatsProvider>
        <RouterProvider router={router} />
      </StatsProvider>
    </ThemeProvider>
  );
}
