import type { LucideIcon } from "lucide-react";
import type React from "react";

// Window interface extension for WordPress API
declare global {
  interface Window {
    easycommerceFakerpressApi?: {
      restUrl?: string;
      restNonce?: string;
      ajaxUrl?: string;
      adminColors?: Record<string, string>;
      colorScheme?: string;
      locale?: {
        faker?: string;
        label?: string;
        wordpress?: string;
        allLocales?: Record<string, string>;
      };
    };
  }
}

// Generator types
export interface Generator {
  name: string;
  component: React.ComponentType<any>;
  category: string;
  order: number;
  icon: LucideIcon;
  description: string;
  useCase?: string;
  route: string;
  popular?: boolean;
}

// Generator result type
export interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

// Parameter configuration types
export interface ParameterConfig {
  type: string;
  title?: string;
  description?: string;
  default?: any;
  enum?: string[];
  minimum?: number;
  maximum?: number;
  items?: {
    type?: string;
    enum?: string[];
    default?: string[];
  };
  properties?: Record<string, ParameterConfig>;
  dependsOn?: Record<string, any>;
}

// Generator base props
export interface GeneratorBaseProps {
  title: string;
  description: string;
  type: string;
  onGenerate: (params: Record<string, any>) => void;
  isLoading: boolean;
  result?: GeneratorResult | null;
  error?: string | null;
  parameterConfig?: Record<string, ParameterConfig>;
  children?: React.ReactNode;
}

// Generator page params
export interface GeneratorPageParams extends Record<
  string,
  string | undefined
> {
  type: string;
}

export {};
