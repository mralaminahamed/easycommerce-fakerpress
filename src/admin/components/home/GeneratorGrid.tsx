import { Link } from "react-router-dom";
import { __ } from "@wordpress/i18n";
import { Badge } from "@/admin/components/ui/badge";
import { cn } from "@/admin/lib/utils";
import { getStats } from "@/admin/lib/storage";
import type { Generator } from "@/admin/types";

interface GeneratorGridProps {
  generators: Generator[];
}

const CATEGORY_ORDER = [
  __("Core", "easycommerce-fakerpress"),
  __("Advanced", "easycommerce-fakerpress"),
  __("Enhanced", "easycommerce-fakerpress"),
];

export function GeneratorGrid({ generators }: GeneratorGridProps) {
  const categories = CATEGORY_ORDER.filter((cat) =>
    generators.some((g) => g.category === cat),
  );

  return (
    <div data-testid="generator-grid" className="space-y-10">
      {categories.map((category) => (
        <section key={category}>
          <h2 className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-4">
            {category} {__("Generators", "easycommerce-fakerpress")}
          </h2>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {generators
              .filter((g) => g.category === category)
              .sort((a, b) => a.order - b.order)
              .map((g) => (
                <GeneratorCard key={g.route} generator={g} />
              ))}
          </div>
        </section>
      ))}
    </div>
  );
}

function GeneratorCard({ generator: g }: { generator: Generator }) {
  const count = getStats(g.route);

  return (
    <Link
      to={`/generator/${g.route}`}
      data-testid={`generator-card-${g.route}`}
      className={cn(
        "group block rounded-xl border border-gray-200 bg-white p-5 shadow-sm",
        "hover:shadow-md hover:-translate-y-1 hover:border-l-4 hover:border-l-blue-500",
        "transition-all duration-200",
      )}
    >
      <div className="flex items-start justify-between mb-3">
        <div className="rounded-full bg-blue-50 p-2">
          <g.icon className="w-5 h-5 text-blue-600 group-hover:scale-105 transition-transform" />
        </div>
        {g.popular && (
          <Badge className="bg-orange-100 text-orange-700 border-orange-200 text-xs px-2 py-0.5">
            {__("Popular", "easycommerce-fakerpress")}
          </Badge>
        )}
      </div>
      <h3 className="text-base font-semibold text-gray-900 mb-1 group-hover:text-blue-700 transition-colors">
        {g.name}
      </h3>
      <p className="text-sm text-gray-500 line-clamp-2">{g.description}</p>
      {count > 0 && (
        <p className="text-xs text-gray-400 mt-3">
          {count.toLocaleString()} {__("generated", "easycommerce-fakerpress")}
        </p>
      )}
    </Link>
  );
}
