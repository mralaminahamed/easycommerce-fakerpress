import React from "react";
import { Outlet, useLocation } from "react-router-dom";
import { motion } from "framer-motion";
import { __ } from "@wordpress/i18n";
import { Globe } from "lucide-react";

// Extend window object for WordPress API
declare global {
  interface Window {
    easycommerceFakerpressApi?: {
      locale?: {
        faker?: string;
        label?: string;
        wordpress?: string;
        allLocales?: Record<string, string>;
      };
    };
  }
}

export default function RootLayout() {
  const location = useLocation();
  const isHomePage = location.pathname === "/";

  // Get locale information from localized script
  const localeInfo = window.easycommerceFakerpressApi?.locale || {
    faker: "en_US",
    label: "English (United States)",
    wordpress: "en_US",
  };

  return (
    <motion.div
      className="min-h-screen bg-linear-to-br from-gray-50 via-white to-blue-50/30"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.6 }}
    >
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        {/* Hero Section - Only show on home page */}
        {isHomePage && (
          <motion.div
            className="mb-12"
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: 0.2, duration: 0.6 }}
          >
            <div className="flex items-start justify-between">
              <div className="flex-1">
                <motion.h1
                  className="text-4xl font-bold text-gray-900 tracking-tight mb-3"
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  transition={{ delay: 0.3, duration: 0.6 }}
                >
                  {__("EasyCommerce FakerPress", "easycommerce-fakerpress")}
                </motion.h1>
                <motion.p
                  className="text-lg text-gray-600 leading-relaxed max-w-2xl"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ delay: 0.5, duration: 0.6 }}
                >
                  {__(
                    "Comprehensive EasyCommerce test data generator with 11 specialized generators, real-time validation, and modern interface.",
                    "easycommerce-fakerpress",
                  )}
                </motion.p>
                <motion.div
                  className="mt-4 h-1 w-32 bg-linear-to-r from-blue-500 to-purple-600 rounded-full"
                  initial={{ width: 0 }}
                  animate={{ width: 128 }}
                  transition={{ delay: 0.7, duration: 0.8 }}
                />
              </div>
              <motion.div
                className="ml-6 shrink-0"
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.4, duration: 0.5 }}
              >
                <div className="inline-flex items-center gap-3 rounded-xl bg-linear-to-r from-blue-50 to-purple-50 px-4 py-3 text-sm font-medium text-blue-700 ring-1 ring-inset ring-blue-200/50 shadow-xs">
                  <motion.div
                    whileHover={{ rotate: 15 }}
                    transition={{ type: "spring", stiffness: 300 }}
                  >
                    <Globe className="h-5 w-5" aria-hidden="true" />
                  </motion.div>
                  <div className="flex flex-col">
                    <span className="text-xs text-blue-600 font-semibold uppercase tracking-wide">
                      {__("Data Locale", "easycommerce-fakerpress")}
                    </span>
                    <span className="text-blue-800 font-medium">
                      {localeInfo.label}
                    </span>
                  </div>
                </div>
              </motion.div>
            </div>
          </motion.div>
        )}

        {/* Page Content */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: isHomePage ? 0.6 : 0.2, duration: 0.6 }}
        >
          <Outlet />
        </motion.div>
      </div>
    </motion.div>
  );
}
