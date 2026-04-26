import type { LucideIcon } from "lucide-react";

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
  format?: string;
}

export interface Generator {
  name: string;
  category: string;
  order: number;
  icon: LucideIcon;
  description: string;
  useCase?: string;
  route: string;
  popular?: boolean;
  parameterConfig?: Record<string, ParameterConfig>;
}

export interface GeneratorResult {
  message: string;
  generated?: number;
  [key: string]: any;
}

export interface StoredRun {
  count: number;
  timestamp: number;
  success: boolean;
  message: string;
}

export interface GeneratorPageParams extends Record<string, string | undefined> {
  type: string;
}

