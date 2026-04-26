import { __ } from "@wordpress/i18n";
import { StatsBar } from "@/admin/components/home/StatsBar";
import { GeneratorGrid } from "@/admin/components/home/GeneratorGrid";
import { generators } from "@/admin/lib/generators";

export default function HomePage() {
  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900">
          {__("FakerPress", "easycommerce-fakerpress")}
        </h1>
        <p className="text-sm text-gray-500 mt-1">
          {__("Generate realistic test data for your EasyCommerce store.", "easycommerce-fakerpress")}
        </p>
      </div>
      <StatsBar />
      <GeneratorGrid generators={generators} />
    </div>
  );
}
