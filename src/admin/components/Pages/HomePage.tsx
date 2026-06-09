import React from "react";
import { useNavigate } from "react-router-dom";
import { __ } from "@wordpress/i18n";
import { Button } from "@/admin/components/ui/button";
import { SectionLabel } from "@/admin/components/ui/section-label";
import { StatCard } from "@/admin/components/dashboard/StatCard";
import { RecentActivity } from "@/admin/components/dashboard/RecentActivity";
import { GeneratorGrid } from "@/admin/components/home/GeneratorGrid";
import { useStats } from "@/admin/providers/StatsProvider";

export default function HomePage() {
  const navigate = useNavigate();
  const { counts, totalGenerated, recentRuns } = useStats();

  return (
    <div className="fp-page wide fp-enter">
      {/* Page header */}
      <div className="fp-page-head">
        <div>
          <h1 className="fp-h1">{__("FakerPress", "easycommerce-fakerpress")}</h1>
          <p className="fp-sub">
            {__("Generate realistic test data for your EasyCommerce store.", "easycommerce-fakerpress")}
          </p>
        </div>
        <Button
          variant="primary"
          icon="sparkles"
          onClick={() => navigate("/generator/products")}
        >
          {__("New generation", "easycommerce-fakerpress")}
        </Button>
      </div>

      {/* Stat cards */}
      <div className="fp-stat-row">
        <StatCard
          iconName="box"
          label={__("Products", "easycommerce-fakerpress")}
          value={counts.products ?? 0}
          empty={!counts.products}
          delta={counts.products ?? 0}
          spark={[4, 6, 5, 8, 7, 9, 10]}
          accentVar="var(--accent)"
        />
        <StatCard
          iconName="users"
          label={__("Customers", "easycommerce-fakerpress")}
          value={counts.customers ?? 0}
          empty={!counts.customers}
          delta={counts.customers ?? 0}
          spark={[2, 3, 3, 5, 6, 6, 8]}
          accentVar="var(--violet)"
        />
        <StatCard
          iconName="cart"
          label={__("Orders", "easycommerce-fakerpress")}
          value={counts.orders ?? 0}
          empty={!counts.orders}
          delta={counts.orders ?? 0}
          spark={[1, 2, 4, 3, 6, 7, 9]}
          accentVar="var(--sky)"
        />
        <StatCard
          iconName="database"
          label={__("Total Generated", "easycommerce-fakerpress")}
          value={totalGenerated}
          empty={!totalGenerated}
          delta={totalGenerated}
          spark={[3, 5, 8, 7, 11, 14, 18]}
          accentVar="var(--green)"
        />
      </div>

      {/* Recent activity */}
      <div className="fp-group-head">
        <SectionLabel>{__("Recent activity", "easycommerce-fakerpress")}</SectionLabel>
        <div className="fp-group-line" />
      </div>
      <RecentActivity runs={recentRuns} />

      {/* Generator grid */}
      <GeneratorGrid counts={counts} />
    </div>
  );
}
