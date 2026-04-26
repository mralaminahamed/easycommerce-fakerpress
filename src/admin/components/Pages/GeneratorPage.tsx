import { useParams, useNavigate } from "react-router-dom";
import { useState } from "@wordpress/element";
import { Link } from "react-router-dom";
import { ArrowLeft, Globe } from "lucide-react";
import { __ } from "@wordpress/i18n";
import { generators } from "@/admin/lib/generators";
import { ParamsPanel } from "@/admin/components/generator/ParamsPanel";
import { ActionPanel } from "@/admin/components/generator/ActionPanel";
import { GeneratorSidebar } from "@/admin/components/generator/GeneratorSidebar";
import { getSettings } from "@/admin/lib/settings";
import type { GeneratorPageParams } from "@/admin/types";

export default function GeneratorPage() {
  const { type } = useParams<GeneratorPageParams>();
  const navigate = useNavigate();
  const generator = generators.find((g) => g.route === type);

  const [params, setParams] = useState<Record<string, any>>({});
  const [count, setCount] = useState(() => getSettings().defaultCount);
  const [locale, setLocale] = useState(() => getSettings().defaultLocale);
  const [seed, setSeed] = useState("");
  const [includeMeta, setIncludeMeta] = useState(true);

  if (!generator) {
    navigate("/");
    return null;
  }

  const handleParamChange = (paramName: string, value: any) => {
    setParams((prev) => {
      if (paramName.includes(".")) {
        const [obj, prop] = paramName.split(".");
        return {
          ...prev,
          [obj]: { ...(prev[obj] as Record<string, any> | undefined ?? {}), [prop]: value },
        };
      }
      return { ...prev, [paramName]: value };
    });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Top bar */}
      <div
        data-testid="generator-topbar"
        className="bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between sticky top-12 z-10"
      >
        <div className="flex items-center gap-3">
          <Link
            to="/"
            className="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-900 transition-colors"
          >
            <ArrowLeft className="w-4 h-4" />
            {__("Back", "easycommerce-fakerpress")}
          </Link>
          <span className="text-gray-300 select-none">/</span>
          <span className="text-sm font-medium text-gray-900">
            {generator.name}
          </span>
        </div>
        <div className="flex items-center gap-1.5 text-sm text-gray-500">
          <Globe className="w-4 h-4" />
          <span>
            {window.easycommerceFakerpressApi?.locale?.label ?? locale}
          </span>
        </div>
      </div>

      {/* Body */}
      <div className="flex gap-6 p-6">
        {/* Sidebar — desktop only */}
        <div className="hidden lg:block">
          <GeneratorSidebar current={generator} all={generators} />
        </div>

        {/* Params + Action two-panel */}
        <div className="flex-1 flex gap-6 min-w-0">
          {/* Left: params */}
          <div className="flex-1 min-w-0">
            <h1 className="text-2xl font-bold text-gray-900 mb-1">
              {generator.name}
            </h1>
            <p className="text-sm text-gray-500 mb-6">{generator.description}</p>
            <ParamsPanel
              parameterConfig={generator.parameterConfig ?? {}}
              params={params}
              disabled={false}
              onChange={handleParamChange}
            />
          </div>

          {/* Right: action — desktop */}
          <div className="w-72 shrink-0 hidden md:block">
            <ActionPanel
              generator={generator}
              count={count}
              locale={locale}
              seed={seed}
              includeMeta={includeMeta}
              onCountChange={setCount}
              onLocaleChange={setLocale}
              onSeedChange={setSeed}
              onIncludeMetaChange={setIncludeMeta}
              extraParams={params}
            />
          </div>
        </div>
      </div>

      {/* Action panel — mobile (below params) */}
      <div className="md:hidden p-6 pt-0">
        <ActionPanel
          generator={generator}
          count={count}
          locale={locale}
          seed={seed}
          includeMeta={includeMeta}
          onCountChange={setCount}
          onLocaleChange={setLocale}
          onSeedChange={setSeed}
          onIncludeMetaChange={setIncludeMeta}
          extraParams={params}
        />
      </div>
    </div>
  );
}
