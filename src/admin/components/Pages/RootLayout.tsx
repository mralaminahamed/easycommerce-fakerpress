import { Link, Outlet, useLocation } from "react-router-dom";
import { __ } from "@wordpress/i18n";
import { LayoutGrid, Puzzle, Settings } from "lucide-react";
import { cn } from "@/admin/lib/utils";

const NAV_LINKS = [
  { to: "/",        label: "Generators", icon: LayoutGrid },
  { to: "/settings",label: "Settings",   icon: Settings   },
  { to: "/plugins", label: "Our Plugins",icon: Puzzle      },
] as const;

export default function RootLayout() {
  const { pathname } = useLocation();

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Global nav */}
      <header className="sticky top-0 z-20 h-12 bg-white border-b border-gray-200 flex items-center px-6 gap-6">
        <span className="text-sm font-bold text-gray-900 mr-2">FakerPress</span>
        {NAV_LINKS.map(({ to, label, icon: Icon }) => {
          const active = to === "/" ? pathname === "/" : pathname.startsWith(to);
          return (
            <Link
              key={to}
              to={to}
              className={cn(
                "flex items-center gap-1.5 text-sm transition-colors",
                active
                  ? "text-blue-600 font-medium"
                  : "text-gray-500 hover:text-gray-900",
              )}
            >
              <Icon className="w-4 h-4" />
              {__(label, "easycommerce-fakerpress")}
            </Link>
          );
        })}
      </header>

      <Outlet />
    </div>
  );
}
