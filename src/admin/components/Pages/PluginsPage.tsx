import { useEffect, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { ExternalLink, Star } from "lucide-react";

interface WPPlugin {
  name: string;
  slug: string;
  version: string;
  short_description: string;
  icons?: { "1x"?: string; "2x"?: string; svg?: string };
  rating: number;
  num_ratings: number;
  active_installs: number;
}

export default function PluginsPage() {
  const [plugins, setPlugins] = useState<WPPlugin[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetch(
      "https://api.wordpress.org/plugins/info/1.2/?action=query_plugins&request[author]=mralaminahamed&request[per_page]=20",
    )
      .then((r) => r.json())
      .then((data: { plugins?: WPPlugin[] }) => {
        setPlugins(data.plugins ?? []);
        setLoading(false);
      })
      .catch(() => {
        setError(
          __(
            "Could not load plugins. Check your internet connection.",
            "easycommerce-fakerpress",
          ),
        );
        setLoading(false);
      });
  }, []);

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">
          {__("Our Plugins", "easycommerce-fakerpress")}
        </h1>
        <p className="text-sm text-gray-500 mt-1">
          {__(
            "Other plugins by the same author on WordPress.org.",
            "easycommerce-fakerpress",
          )}
        </p>
      </div>

      {loading && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <div
              key={i}
              className="bg-white rounded-xl border border-gray-200 p-5 animate-pulse"
            >
              <div className="flex items-center gap-3 mb-3">
                <div className="w-10 h-10 rounded-lg bg-gray-100" />
                <div className="flex-1 space-y-1.5">
                  <div className="h-3 bg-gray-100 rounded w-3/4" />
                  <div className="h-2.5 bg-gray-100 rounded w-1/4" />
                </div>
              </div>
              <div className="space-y-1.5">
                <div className="h-2.5 bg-gray-100 rounded" />
                <div className="h-2.5 bg-gray-100 rounded w-5/6" />
              </div>
            </div>
          ))}
        </div>
      )}

      {error && (
        <div className="rounded-md bg-red-50 border border-red-200 p-4 text-sm text-red-700">
          {error}
        </div>
      )}

      {!loading && !error && plugins.length === 0 && (
        <p className="text-sm text-gray-400 italic">
          {__("No plugins found.", "easycommerce-fakerpress")}
        </p>
      )}

      {!loading && !error && plugins.length > 0 && (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {plugins.map((plugin) => (
            <PluginCard key={plugin.slug} plugin={plugin} />
          ))}
        </div>
      )}
    </div>
  );
}

function PluginCard({ plugin }: { plugin: WPPlugin }) {
  const icon =
    plugin.icons?.svg ?? plugin.icons?.["2x"] ?? plugin.icons?.["1x"];
  const stars = Math.round(plugin.rating / 20);

  return (
    <div className="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">
      <div className="flex items-center gap-3">
        {icon ? (
          <img
            src={icon}
            alt={plugin.name}
            className="w-10 h-10 rounded-lg object-cover"
          />
        ) : (
          <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
            {plugin.name.charAt(0)}
          </div>
        )}
        <div className="min-w-0">
          <h3 className="text-sm font-semibold text-gray-900 truncate">
            {plugin.name}
          </h3>
          <p className="text-xs text-gray-400">v{plugin.version}</p>
        </div>
      </div>

      <p className="text-sm text-gray-600 line-clamp-3 flex-1">
        {plugin.short_description}
      </p>

      <div className="flex items-center justify-between">
        <div className="flex items-center gap-0.5">
          {Array.from({ length: 5 }).map((_, i) => (
            <Star
              key={i}
              className={`w-3.5 h-3.5 ${
                i < stars
                  ? "text-yellow-400 fill-yellow-400"
                  : "text-gray-200 fill-gray-200"
              }`}
            />
          ))}
          <span className="text-xs text-gray-400 ml-1">
            ({plugin.num_ratings})
          </span>
        </div>
        <span className="text-xs text-gray-400">
          {plugin.active_installs.toLocaleString()}+ {__("active", "easycommerce-fakerpress")}
        </span>
      </div>

      <a
        href={`https://wordpress.org/plugins/${plugin.slug}/`}
        target="_blank"
        rel="noopener noreferrer"
        className="flex items-center justify-center gap-1.5 w-full text-sm font-medium text-blue-600 hover:text-blue-700 py-2 px-3 rounded-lg border border-blue-200 hover:bg-blue-50 transition-colors"
      >
        <ExternalLink className="w-3.5 h-3.5" />
        {__("View on WordPress.org", "easycommerce-fakerpress")}
      </a>
    </div>
  );
}
