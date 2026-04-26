import { Link } from "react-router-dom";
import { __ } from "@wordpress/i18n";
import { cn } from "@/admin/lib/utils";
import { getRuns } from "@/admin/lib/storage";
import type { Generator } from "@/admin/types";

interface GeneratorSidebarProps {
  current: Generator;
  all: Generator[];
}

const CATEGORY_ORDER = ["Core", "Advanced", "Enhanced"];

function timeAgo(timestamp: number): string {
  const seconds = Math.floor((Date.now() - timestamp) / 1000);
  if (seconds < 60) return "just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
  return `${Math.floor(seconds / 86400)}d ago`;
}

export function GeneratorSidebar({ current, all }: GeneratorSidebarProps) {
  const others = all.filter((g) => g.route !== current.route);
  const recentRuns = getRuns(current.route).slice(0, 3);

  const categorised = CATEGORY_ORDER.map((cat) => ({
    label: cat,
    items: others
      .filter((g) => g.category === cat)
      .sort((a, b) => a.order - b.order),
  })).filter((c) => c.items.length > 0);

  return (
    <aside className="w-56 shrink-0 space-y-6">
      {/* Current generator */}
      <div>
        <p className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
          {__("Current", "easycommerce-fakerpress")}
        </p>
        <div className="flex items-center gap-2 px-3 py-2 rounded-md bg-blue-50 border-l-2 border-blue-600">
          <current.icon className="w-4 h-4 text-blue-600 shrink-0" />
          <span className="text-sm font-medium text-blue-700 truncate">
            {current.name}
          </span>
        </div>
      </div>

      {/* Other generators */}
      {categorised.map(({ label, items }) => (
        <div key={label}>
          <p className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
            {label}
          </p>
          <nav className="space-y-0.5">
            {items.map((g) => (
              <Link
                key={g.route}
                to={`/generator/${g.route}`}
                className="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors"
              >
                <g.icon className="w-4 h-4 shrink-0 text-gray-400" />
                <span className="truncate">{g.name}</span>
              </Link>
            ))}
          </nav>
        </div>
      ))}

      {/* Recent runs */}
      {recentRuns.length > 0 && (
        <div>
          <p className="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
            {__("Recent Runs", "easycommerce-fakerpress")}
          </p>
          <div className="space-y-1.5">
            {recentRuns.map((run, i) => (
              <div key={i} className="flex items-center gap-2 px-1 text-xs">
                <span
                  className={cn(
                    "shrink-0",
                    run.success ? "text-green-500" : "text-red-500",
                  )}
                >
                  {run.success ? "✓" : "✗"}
                </span>
                <span className="text-gray-700 truncate">
                  {run.success ? `${run.count} items` : "Error"}
                </span>
                <span className="text-gray-400 ml-auto shrink-0">
                  {timeAgo(run.timestamp)}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </aside>
  );
}
