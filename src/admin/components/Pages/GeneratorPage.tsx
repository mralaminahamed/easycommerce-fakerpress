import { motion, AnimatePresence } from "framer-motion";
import { ChevronRight, ArrowLeft, Menu, X, Globe } from "lucide-react";
import { Link, useParams, useNavigate } from "react-router-dom";

import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import { Button } from "@/admin/components/ui/button";
import { Card, CardContent } from "@/admin/components/ui/card";
import { generators } from "@/admin/lib/generators";
import type { GeneratorPageParams } from "@/admin/types";

export default function GeneratorPage() {
  const { type } = useParams<GeneratorPageParams>();
  const navigate = useNavigate();
  const generator = generators.find((gen) => gen.route === type);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Get locale information from localized script
  const localeInfo = (window as any).easycommerceFakerpressApi?.locale || {
    faker: "en_US",
    label: "English (United States)",
    wordpress: "en_US",
  };

  if (!generator) {
    navigate("/");
    return null;
  }

  const IconComponent = generator.icon;

  const groupedGenerators = generators.reduce(
    (acc, gen) => {
      if (!acc[gen.category]) {
        acc[gen.category] = [];
      }
      acc[gen.category].push(gen);
      return acc;
    },
    {} as Record<string, typeof generators>,
  );

  const sortedCategories = Object.keys(groupedGenerators).sort();
  const sortedGenerators = sortedCategories.flatMap((category) =>
    groupedGenerators[category].sort((a, b) => a.order - b.order),
  );

  return (
    <motion.div
      className="relative flex gap-6 lg:gap-8"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.6 }}
    >
      {/* Sidebar for generators */}
      <motion.aside
        className="w-72 hidden lg:block"
        initial={{ opacity: 0, x: -20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ delay: 0.2, duration: 0.5 }}
      >
        <div className="sticky top-4 space-y-4">
          {/* Current Generator Card */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay: 0.3, duration: 0.4 }}
          >
            <Card className="border-2 border-blue-200 bg-linear-to-br from-blue-50 to-purple-50/50 shadow-md">
              <CardContent className="p-4">
                <div className="flex items-center space-x-3">
                  <div className="shrink-0">
                    <div className="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                      <IconComponent className="w-5 h-5 text-white" />
                    </div>
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-blue-900">
                      {__("Current Generator", "easycommerce-fakerpress")}
                    </p>
                    <p className="text-lg font-semibold text-blue-800 truncate">
                      {generator.name}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </motion.div>

          {/* Other Generators Card */}
          <Card className="shadow-xs">
            <CardContent className="p-6">
              <h2 className="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <span className="w-2 h-2 bg-blue-500 rounded-full mr-3"></span>
                {__("Other Generators", "easycommerce-fakerpress")}
              </h2>
              <ul className="space-y-2">
                {sortedGenerators
                  .filter((gen) => gen.route !== generator.route)
                  .map((gen, index) => {
                    const GenIconComponent = gen.icon;
                    return (
                      <motion.li
                        key={gen.name}
                        initial={{ opacity: 0, x: -10 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.1 + index * 0.05 }}
                      >
                        <Link
                          to={`/generator/${gen.route}`}
                          className="group flex items-center w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-linear-to-r hover:from-blue-50 hover:to-purple-50 hover:text-blue-700 rounded-lg transition-all duration-200 border border-transparent hover:border-blue-200 hover:shadow-xs"
                        >
                          <GenIconComponent className="w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500 transition-colors" />
                          <span className="font-medium">{gen.name}</span>
                          {gen.popular && (
                            <span className="ml-auto text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                              Popular
                            </span>
                          )}
                        </Link>
                      </motion.li>
                    );
                  })}
              </ul>
            </CardContent>
          </Card>
        </div>
      </motion.aside>

      {/* Main content with navigation and generator */}
      <motion.main
        className="flex-1 min-w-0"
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.3, duration: 0.6 }}
      >
        {/* Visual connection line for desktop */}
        <div className="hidden lg:block absolute left-0 top-12 bottom-12 w-px bg-linear-to-b from-blue-200 via-blue-100 to-transparent ml-2"></div>
        {/* Navigation Header */}
        <div className="mb-8 space-y-4">
          {/* Mobile Header */}
          <div className="lg:hidden space-y-4">
            {/* Back Button and Data Locale */}
            <div className="flex items-center justify-between">
              <motion.div
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
              >
                <Button asChild variant="outline" size="sm">
                  <Link to="/" className="inline-flex items-center">
                    <ArrowLeft className="h-4 w-4 mr-2" aria-hidden="true" />
                    {__("Back", "easycommerce-fakerpress")}
                  </Link>
                </Button>
              </motion.div>

              {/* Data Locale - Mobile */}
              <motion.div
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                transition={{ delay: 0.4, duration: 0.5 }}
              >
                <div className="inline-flex items-center gap-2 rounded-lg bg-linear-to-r from-blue-50 to-purple-50 px-3 py-2 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200/50 shadow-xs">
                  <motion.div
                    whileHover={{ rotate: 15 }}
                    transition={{ type: "spring", stiffness: 300 }}
                  >
                    <Globe className="h-4 w-4" aria-hidden="true" />
                  </motion.div>
                  <span className="font-medium">{localeInfo.label}</span>
                </div>
              </motion.div>
            </div>

            {/* Mobile Menu Button */}
            <div className="flex justify-start">
              <motion.button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-hidden focus:ring-2 focus:ring-inset focus:ring-blue-500"
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
              >
                {mobileMenuOpen ? (
                  <X className="h-6 w-6" aria-hidden="true" />
                ) : (
                  <Menu className="h-6 w-6" aria-hidden="true" />
                )}
              </motion.button>
            </div>
          </div>

          {/* Breadcrumb and Data Locale */}
          <div className="flex items-center justify-between">
            <div className="flex items-center space-x-4">
              {/* Back Button */}
              <motion.div
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
              >
                <Button asChild variant="outline" size="sm">
                  <Link to="/" className="inline-flex items-center">
                    <ArrowLeft className="h-4 w-4 mr-2" aria-hidden="true" />
                    {__("Back", "easycommerce-fakerpress")}
                  </Link>
                </Button>
              </motion.div>

              {/* Breadcrumb */}
              <motion.nav
                aria-label="Breadcrumb"
                initial={{ opacity: 0, x: -20 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: 0.4 }}
              >
                <ol className="flex items-center space-x-3 bg-white px-4 py-2 rounded-lg shadow-xs border border-gray-100 list-none">
                  <li>
                    <Link
                      to="/"
                      className="text-sm text-blue-600 hover:text-blue-800 transition-colors font-medium"
                    >
                      {__("Home", "easycommerce-fakerpress")}
                    </Link>
                  </li>
                  <li>
                    <ChevronRight
                      className="h-4 w-4 text-gray-400"
                      aria-hidden="true"
                    />
                  </li>
                  <li>
                    <span className="text-sm text-gray-900 font-semibold flex items-center">
                      <IconComponent className="w-4 h-4 mr-2 text-blue-500" />
                      {generator.name}
                    </span>
                  </li>
                </ol>
              </motion.nav>
            </div>

            {/* Data Locale - Desktop */}
            <motion.div
              className="hidden lg:block"
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
        </div>

        {/* Mobile Menu */}
        <AnimatePresence>
          {mobileMenuOpen && (
            <motion.div
              className="lg:hidden mb-6"
              initial={{ opacity: 0, height: 0 }}
              animate={{ opacity: 1, height: "auto" }}
              exit={{ opacity: 0, height: 0 }}
              transition={{ duration: 0.3 }}
            >
              <div className="space-y-4">
                {/* Current Generator - Mobile */}
                <motion.div
                  initial={{ opacity: 0, scale: 0.95 }}
                  animate={{ opacity: 1, scale: 1 }}
                  transition={{ delay: 0.1, duration: 0.3 }}
                >
                  <Card className="border-2 border-blue-200 bg-linear-to-br from-blue-50 to-purple-50/50">
                    <CardContent className="p-4">
                      <div className="flex items-center space-x-3">
                        <div className="shrink-0">
                          <div className="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <IconComponent className="w-4 h-4 text-white" />
                          </div>
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className="text-xs font-medium text-blue-900 uppercase tracking-wide">
                            {__("Current", "easycommerce-fakerpress")}
                          </p>
                          <p className="text-sm font-semibold text-blue-800">
                            {generator.name}
                          </p>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </motion.div>

                {/* Other Generators - Mobile */}
                <Card className="border border-gray-200">
                  <CardContent className="p-4">
                    <h3 className="text-sm font-semibold text-gray-900 mb-4 flex items-center">
                      <span className="w-2 h-2 bg-blue-500 rounded-full mr-2"></span>
                      {__("Other Generators", "easycommerce-fakerpress")}
                    </h3>
                    <ul className="space-y-2">
                      {sortedGenerators
                        .filter((gen) => gen.route !== generator.route)
                        .map((gen, index) => {
                          const GenIconComponent = gen.icon;
                          return (
                            <motion.li
                              key={gen.name}
                              initial={{ opacity: 0, x: -10 }}
                              animate={{ opacity: 1, x: 0 }}
                              transition={{ delay: index * 0.05 }}
                            >
                              <Link
                                to={`/generator/${gen.route}`}
                                onClick={() => setMobileMenuOpen(false)}
                                className="group flex items-center w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-linear-to-r hover:from-blue-50 hover:to-purple-50 hover:text-blue-700 rounded-md transition-all duration-200"
                              >
                                <GenIconComponent className="w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500 transition-colors" />
                                <span className="font-medium">{gen.name}</span>
                                {gen.popular && (
                                  <span className="ml-auto text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-medium">
                                    Popular
                                  </span>
                                )}
                              </Link>
                            </motion.li>
                          );
                        })}
                    </ul>
                  </CardContent>
                </Card>
              </div>
            </motion.div>
          )}
        </AnimatePresence>

        {/* Generator Content */}
        <motion.div
          className="relative"
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ delay: 0.5, duration: 0.6 }}
        >
          {/* Subtle background for content area */}
          <div className="absolute inset-0 bg-linear-to-r from-transparent via-gray-50/50 to-transparent rounded-lg -mx-4 -my-2"></div>

          {/* Content */}
          <div className="relative z-10">
            <generator.component />
          </div>
        </motion.div>
      </motion.main>
    </motion.div>
  );
}
