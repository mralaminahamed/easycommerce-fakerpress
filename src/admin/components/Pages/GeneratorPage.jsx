import { Link, useParams, useNavigate } from "react-router-dom";
import { ChevronRightIcon, ArrowLeftIcon } from "@heroicons/react/24/outline";

import { __ } from "@wordpress/i18n";

import { generators } from "./HomePage";

export default function GeneratorPage() {
  const { type } = useParams();
  const navigate = useNavigate();
  const generator = generators.find((gen) => gen.route === type);

  if (!generator) {
    navigate("/");
    return null;
  }

  const groupedGenerators = generators.reduce((acc, gen) => {
    if (!acc[gen.category]) {
      acc[gen.category] = [];
    }
    acc[gen.category].push(gen);
    return acc;
  }, {});

  const sortedCategories = Object.keys(groupedGenerators).sort();
  const sortedGenerators = sortedCategories.flatMap((category) =>
    groupedGenerators[category].sort((a, b) => a.order - b.order),
  );

  return (
    <div className="flex gap-6">
      {/* Sidebar for other generators */}
      <aside className="w-64 hidden lg:block">
        <h2 className="text-sm font-semibold text-gray-900 mb-4">
          {__("Other Generators", "easycommerce-fakerpress")}
        </h2>
        <ul className="space-y-2">
          {sortedGenerators
            .filter((gen) => gen.route !== generator.route)
            .map((gen) => (
              <li key={gen.name}>
                <Link
                  to={`/generator/${gen.route}`}
                  className="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 rounded-md transition-colors"
                >
                  {gen.name}
                </Link>
              </li>
            ))}
        </ul>
      </aside>

      {/* Main content with back button and breadcrumb */}
      <main className="flex-1">
        <div className="flex flex-row-reverse justify-between mb-6">
          <Link
            to="/"
            className="inline-flex items-center px-4 py-2 rounded-md bg-gray-100 text-gray-700 font-medium text-sm hover:bg-gray-200 transition-colors"
          >
            <ArrowLeftIcon className="h-5 w-5 mr-2" aria-hidden="true" />
            {__("Back to Generators", "easycommerce-fakerpress")}
          </Link>
          <nav aria-label="Breadcrumb" className="flex">
            <ol className="flex items-center space-x-2">
              <li>
                <Link
                  to="/"
                  className="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                >
                  {__("Home", "easycommerce-fakerpress")}
                </Link>
              </li>
              <li>
                <ChevronRightIcon
                  className="h-4 w-4 text-gray-400"
                  aria-hidden="true"
                />
              </li>
              <li>
                <span className="text-sm text-gray-500">{generator.name}</span>
              </li>
            </ol>
          </nav>
        </div>
        <div className="rounded-xl bg-white p-6 shadow-sm transition-all">
          <generator.component />
        </div>
      </main>
    </div>
  );
}
