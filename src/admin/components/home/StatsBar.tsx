import { Package, ShoppingCart, Sparkles, Users } from "lucide-react";
import { StatCard } from "@/admin/components/ui/stat-card";
import { getStats, getTotalStats } from "@/admin/lib/storage";

export function StatsBar() {
  return (
    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard
        icon={Package}
        label="Products"
        value={getStats("products")}
        accentColor="blue"
      />
      <StatCard
        icon={Users}
        label="Customers"
        value={getStats("customers")}
        accentColor="purple"
      />
      <StatCard
        icon={ShoppingCart}
        label="Orders"
        value={getStats("orders")}
        accentColor="indigo"
      />
      <StatCard
        icon={Sparkles}
        label="Total Generated"
        value={getTotalStats()}
        accentColor="gray"
      />
    </div>
  );
}
