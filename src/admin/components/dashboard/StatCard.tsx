import React from "react";
import { __ } from "@wordpress/i18n";
import { Icon } from "@/admin/lib/icons";
import { Sparkline } from "./Sparkline";

interface StatCardProps {
  iconName: string;
  label: string;
  value: number;
  empty: boolean;
  delta: number;
  spark: number[];
  accentVar?: string;
  testId?: string;
}

export function StatCard({ iconName, label, value, empty, delta, spark, accentVar, testId }: StatCardProps) {
  const chipStyle = accentVar
    ? {
        background: `color-mix(in oklch, ${accentVar} 14%, var(--surface))`,
        color: accentVar,
      }
    : undefined;

  return (
    <div className="fp-card fp-stat" data-testid={testId}>
      <div className="fp-stat-top">
        <span className="fp-stat-ic" style={chipStyle}>
          <Icon name={iconName} size={17} />
        </span>
        {label}
      </div>

      <div
        className="fp-stat-num tnum"
        style={empty ? { color: "var(--text-faint)" } : undefined}
      >
        {empty ? "—" : value.toLocaleString()}
      </div>

      <div className="fp-stat-foot">
        {empty ? (
          <span className="fp-stat-empty">
            {__("Nothing generated yet", "easycommerce-fakerpress")}
          </span>
        ) : (
          <span className={`fp-stat-delta ${delta > 0 ? "up" : "flat"}`}>
            {delta > 0 && <Icon name="chart" size={13} />}
            {delta > 0 ? `+${delta} this week` : __("steady", "easycommerce-fakerpress")}
          </span>
        )}
        {spark && (
          <Sparkline data={spark} color={accentVar ?? "var(--accent)"} />
        )}
      </div>
    </div>
  );
}
