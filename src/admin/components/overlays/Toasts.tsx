import React from "react";
import { __ } from "@wordpress/i18n";

import { useToast } from "@/admin/providers/ToastProvider";
import { Icon } from "@/admin/lib/icons";

export function Toasts() {
  const { toasts, dismiss } = useToast();

  if (toasts.length === 0) return null;

  return (
    <div className="fp-toast-wrap" data-testid="toasts">
      {toasts.map((t) => (
        <div key={t.id} className="fp-toast">
          <span className="fp-toast-ic">
            <Icon name="check2" size={17} />
          </span>
          <div>
            <div className="fp-toast-title">{t.title}</div>
            {t.sub && <div className="fp-toast-sub">{t.sub}</div>}
          </div>
          <button
            type="button"
            className="fp-toast-x"
            aria-label={__("Dismiss", "easycommerce-fakerpress")}
            onClick={() => dismiss(t.id)}
          >
            <Icon name="x" size={15} />
          </button>
        </div>
      ))}
    </div>
  );
}
