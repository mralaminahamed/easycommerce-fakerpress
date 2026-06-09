import React from "react";
import { useLocation, useNavigate } from "react-router-dom";

import { Icon } from "@/admin/lib/icons";
import { generators } from "@/admin/lib/generators";

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

export interface SidebarProps {
  collapsed: boolean;
  setCollapsed: (updater: (v: boolean) => boolean) => void;
  counts: Record<string, number>; // route → generated count
  openCmd: () => void;
}

// ---------------------------------------------------------------------------
// Group definitions (ordered: Core → Advanced → Enhanced)
// ---------------------------------------------------------------------------

const GROUPS: Array<{ id: string; label: string }> = [
  { id: "Core", label: "Core generators" },
  { id: "Advanced", label: "Advanced generators" },
  { id: "Enhanced", label: "Enhanced generators" },
];

// ---------------------------------------------------------------------------
// NavItem helper
// ---------------------------------------------------------------------------

interface NavItemProps {
  to: string;
  label: string;
  ic: string;
  count?: number;
  active: boolean;
  collapsed: boolean;
}

function NavItem({ to, label, ic, count, active, collapsed }: NavItemProps) {
  const navigate = useNavigate();

  return (
    <button
      className={"fp-nav-item" + (active ? " active" : "")}
      onClick={() => navigate(to)}
      title={collapsed ? label : undefined}
    >
      <Icon name={ic} size={17} className="fp-nav-ic" stroke={1.7} />
      <span className="fp-nav-text">{label}</span>
      {count != null && count > 0 && (
        <span className="fp-nav-count tnum">{count}</span>
      )}
    </button>
  );
}

// ---------------------------------------------------------------------------
// Sidebar
// ---------------------------------------------------------------------------

export function Sidebar({ collapsed, setCollapsed, counts, openCmd }: SidebarProps) {
  const { pathname } = useLocation();

  return (
    <nav className={"fp-nav" + (collapsed ? " collapsed" : "")}>
      {/* Brand */}
      <div className="fp-brand">
        <div className="fp-brand-mark">
          <Icon name="sparkles" size={17} />
        </div>
        {!collapsed && (
          <div className="fp-brand-text">
            <div className="fp-brand-name">FakerPress</div>
            <div className="fp-brand-sub">EasyCommerce</div>
          </div>
        )}
        <button
          className="fp-nav-collapse fp-focusable"
          onClick={() => setCollapsed((c) => !c)}
          title={collapsed ? "Expand sidebar" : "Collapse sidebar"}
          aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}
        >
          <Icon name={collapsed ? "chevright" : "arrowleft"} size={16} />
        </button>
      </div>

      {/* Command button */}
      <button
        className="fp-cmd-btn fp-focusable"
        onClick={openCmd}
        title={collapsed ? "Search (⌘K)" : undefined}
      >
        <Icon name="search" size={15} />
        <span className="fp-nav-text">Jump to…</span>
        <span className="kbd">
          <span className="kbd-key">⌘</span>
          <span className="kbd-key">K</span>
        </span>
      </button>

      {/* Scrollable nav area */}
      <div className="fp-nav-scroll">
        {/* Overview */}
        <NavItem
          to="/"
          label="Overview"
          ic="dashboard"
          active={pathname === "/"}
          collapsed={collapsed}
        />

        {/* Groups */}
        {GROUPS.map((grp) => {
          const grpGenerators = generators
            .filter((g) => g.category === grp.id)
            .sort((a, b) => a.order - b.order);

          if (grpGenerators.length === 0) return null;

          return (
            <div key={grp.id}>
              <div className="fp-nav-group-label">
                {collapsed ? (
                  <span className="fp-nav-group-rule" />
                ) : (
                  grp.label
                )}
              </div>
              {grpGenerators.map((g) => (
                <NavItem
                  key={g.route}
                  to={`/generator/${g.route}`}
                  label={g.name}
                  ic={g.iconName}
                  count={counts[g.route]}
                  active={pathname === `/generator/${g.route}`}
                  collapsed={collapsed}
                />
              ))}
            </div>
          );
        })}
      </div>

      {/* Footer */}
      <div className="fp-nav-foot">
        <NavItem
          to="/settings"
          label="Settings"
          ic="settings"
          active={pathname === "/settings"}
          collapsed={collapsed}
        />
        <NavItem
          to="/plugins"
          label="Our Plugins"
          ic="plug"
          active={pathname === "/plugins"}
          collapsed={collapsed}
        />
      </div>
    </nav>
  );
}

export default Sidebar;
