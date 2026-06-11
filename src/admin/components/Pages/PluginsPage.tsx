import React from "react";
import { useEffect, useState } from "@wordpress/element";
import { decodeEntities } from "@wordpress/html-entities";
import { __, sprintf } from "@wordpress/i18n";

import { Button } from "@/admin/components/ui/button";

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
        setPlugins(
          (data.plugins ?? []).filter(
            (p) => p.slug !== "easycommerce-fakerpress",
          ),
        );
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
    <div className="fp-page wide fp-enter">
      <div className="fp-page-head">
        <div>
          <h1 className="fp-h1">
            {__("Our Plugins", "easycommerce-fakerpress")}
          </h1>
          <p className="fp-sub">
            {__(
              "Other plugins by the same author on WordPress.org.",
              "easycommerce-fakerpress",
            )}
          </p>
        </div>
      </div>

      {loading && (
        <div className="fp-plugins-grid">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="fp-card fp-plugin-card" aria-hidden>
              <div className="fp-plugin-head">
                <span
                  className="fp-plugin-ic"
                  style={{ background: "var(--surface-inset)" }}
                />
                <div>
                  <div
                    className="fp-plugin-name"
                    style={{
                      width: 120,
                      height: 12,
                      background: "var(--surface-inset)",
                      borderRadius: 4,
                    }}
                  />
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {error && (
        <p className="fp-set-hint" style={{ color: "var(--red)" }}>
          {error}
        </p>
      )}

      {!loading && !error && plugins.length === 0 && (
        <p className="fp-sub">
          {__("No plugins found.", "easycommerce-fakerpress")}
        </p>
      )}

      {!loading && !error && plugins.length > 0 && (
        <div className="fp-plugins-grid">
          {plugins.map((plugin) => (
            <PluginCard key={plugin.slug} plugin={plugin} />
          ))}
        </div>
      )}
    </div>
  );
}

function Rating({ r, rc }: { r: number; rc: number }) {
  const full = "★★★★★".slice(0, r);
  const empty = "☆☆☆☆☆".slice(0, 5 - r);
  return (
    <span className="fp-rating">
      <span>
        <span className="fp-rating-stars">{full}</span>
        <span className="fp-rating-empty">{empty}</span>
      </span>
      <span className="fp-rating-count">({rc || 0})</span>
    </span>
  );
}

function PluginCard({ plugin }: { plugin: WPPlugin }) {
  const icon =
    plugin.icons?.svg ?? plugin.icons?.["2x"] ?? plugin.icons?.["1x"];
  const stars = Math.round(plugin.rating / 20);

  return (
    <div className="fp-card fp-plugin-card">
      <div className="fp-plugin-head">
        {icon ? (
          <img
            src={icon}
            alt={decodeEntities(plugin.name)}
            className="fp-plugin-ic"
            style={{ objectFit: "cover" }}
          />
        ) : (
          <span className="fp-plugin-ic" style={{ background: "var(--accent)" }}>
            {plugin.name.charAt(0)}
          </span>
        )}
        <div style={{ minWidth: 0 }}>
          <div className="fp-plugin-name">{decodeEntities(plugin.name)}</div>
          <div className="fp-plugin-ver">v{plugin.version}</div>
        </div>
      </div>

      <p className="fp-plugin-desc">
        {decodeEntities(plugin.short_description)}
      </p>

      <div className="fp-plugin-foot">
        <Rating r={stars} rc={plugin.num_ratings} />
        <span className="fp-plugin-active">
          {sprintf(
            /* translators: %s: formatted install count */
            __("%s+ active", "easycommerce-fakerpress"),
            plugin.active_installs.toLocaleString(),
          )}
        </span>
      </div>

      <a
        href={`https://wordpress.org/plugins/${plugin.slug}/`}
        target="_blank"
        rel="noopener noreferrer"
        className="full-w"
        style={{ display: "block" }}
      >
        <Button variant="outline" size="sm" icon="external" className="full-w" type="button">
          {__("View on WordPress.org", "easycommerce-fakerpress")}
        </Button>
      </a>
    </div>
  );
}
