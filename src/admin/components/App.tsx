import React from "react";
import { createHashRouter, RouterProvider } from "react-router-dom";

import RootLayout from "./Pages/RootLayout";
import HomePage from "./Pages/HomePage";
import GeneratorPage from "./Pages/GeneratorPage";

// Create router using createHashRouter with route objects
const router = createHashRouter([
  {
    path: "/",
    element: <RootLayout />,
    children: [
      {
        index: true,
        element: <HomePage />,
      },
      {
        path: "generator/:type",
        element: <GeneratorPage />,
      },
    ],
  },
]);

export default function App() {
  return <RouterProvider router={router} />;
}
