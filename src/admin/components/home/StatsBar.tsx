import { Package, ShoppingCart, Sparkles, Users } from "lucide-react";
import { StatCard } from "@/admin/components/ui/stat-card";
import { getStats, getTotalStats } from "@/admin/lib/storage";

export function StatsBar() {
  return (
    <div data-testid="stats-bar" className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard testId="stat-products"  icon={Package}      label="Products"        value={getStats("products")}  accentColor="blue"   />
      <StatCard testId="stat-customers" icon={Users}        label="Customers"       value={getStats("customers")} accentColor="purple" />
      <StatCard testId="stat-orders"    icon={ShoppingCart} label="Orders"          value={getStats("orders")}    accentColor="indigo" />
      <StatCard testId="stat-total"     icon={Sparkles}     label="Total Generated" value={getTotalStats()}        accentColor="gray"   />
    </div>
  );
}
