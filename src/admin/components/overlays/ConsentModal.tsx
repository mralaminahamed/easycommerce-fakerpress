import React from "react";
import { useState, useEffect, useRef, useCallback } from "@wordpress/element";
import { __ } from "@wordpress/i18n";

import { Button } from "@/admin/components/ui/button";
import { Icon } from "@/admin/lib/icons";
import { useToast } from "@/admin/providers/ToastProvider";

const REPO_URL =
  "https://github.com/mralaminahamed/easycommerce-fakerpress-sample-data";

interface SampleStatus {
  exists: boolean;
  last_synced: string | null;
  repo_url: string;
  consent: "granted" | "declined" | null;
}

/**
 * Consent gate for the optional GitHub sample-data download.
 *
 * Mounted once at the app shell. Because the React app mounts only on the
 * plugin admin page, this overlay can never appear on any other admin screen.
 * Self-gating: shows only when the site-wide decision is undecided.
 */
export function ConsentModal() {
  const { toast } = useToast();
  const [open, setOpen] = useState(false);
  const [busy, setBusy] = useState(false);
  const ranRef = useRef(false);

  const api = window.easycommerceFakerpressApi;
  const restUrl = api?.restUrl ?? "";
  const nonce = api?.restNonce ?? "";

  useEffect(() => {
    if (ranRef.current || !restUrl) return;
    ranRef.current = true;
    let cancelled = false;

    fetch(`${restUrl}download-sample`, { headers: { "X-WP-Nonce": nonce } })
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((s: SampleStatus) => {
        if (cancelled) return;
        if (s.consent == null) {
          // Undecided: only prompt if the data isn't already present — a
          // pre-existing install that synced before consent existed should
          // not be nagged.
          if (!s.exists) {
            setOpen(true);
          }
        } else if (s.consent === "granted" && !s.exists) {
          // Consent already given but files are missing — re-fetch silently.
          void fetch(`${restUrl}download-sample`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-WP-Nonce": nonce,
            },
            body: JSON.stringify({ force: false }),
          }).catch(() => {});
        }
      })
      .catch(() => {
        /* Fail closed: on a status error, show no modal and download nothing. */
      });

    return () => {
      cancelled = true;
    };
  }, [restUrl, nonce]);

  const allow = useCallback(async () => {
    setBusy(true);
    try {
      const res = await fetch(`${restUrl}download-sample`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": nonce },
        body: JSON.stringify({ force: false }),
      });
      const json = await res.json();
      if (!res.ok) throw new Error(json?.message ?? `HTTP ${res.status}`);
      toast(__("Sample data downloaded", "easycommerce-fakerpress"));
      setOpen(false);
    } catch {
      toast(
        __(
          "Download failed — you can retry from Settings.",
          "easycommerce-fakerpress",
        ),
      );
      setBusy(false);
    }
  }, [restUrl, nonce, toast]);

  const decline = useCallback(async () => {
    setBusy(true);
    try {
      await fetch(`${restUrl}download-sample/consent`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-WP-Nonce": nonce },
        body: JSON.stringify({ granted: false }),
      });
    } catch {
      /* Best effort — declining is a local preference. */
    }
    setOpen(false);
  }, [restUrl, nonce]);

  if (!open) return null;

  return (
    <div
      className="fp-overlay"
      style={{ alignItems: "center", justifyContent: "center" }}
    >
      <div
        className="fp-consent"
        role="dialog"
        aria-modal="true"
        aria-labelledby="fp-consent-title"
        data-testid="consent-modal"
      >
        <span className="fp-consent-ic">
          <Icon name="database" size={22} />
        </span>
        <h2 id="fp-consent-title" className="fp-consent-title">
          {__("Download sample data?", "easycommerce-fakerpress")}
        </h2>
        <p className="fp-consent-text">
          {__(
            "EasyCommerce FakerPress can download locale-specific reference data (product names, addresses and customer tags for 75+ locales) from GitHub to make generated content more realistic. No data about your site is sent.",
            "easycommerce-fakerpress",
          )}
        </p>
        <p className="fp-consent-text">
          {__(
            "You can decline and still generate data using built-in defaults, or change this later in Settings.",
            "easycommerce-fakerpress",
          )}
        </p>
        <a
          className="fp-consent-link"
          href={REPO_URL}
          target="_blank"
          rel="noopener noreferrer"
        >
          <Icon name="external" size={14} />
          {__("View the sample data repository", "easycommerce-fakerpress")}
        </a>
        <div className="fp-consent-actions">
          <Button
            variant="outline"
            onClick={decline}
            disabled={busy}
            data-testid="consent-decline"
          >
            {__("Not now", "easycommerce-fakerpress")}
          </Button>
          <Button
            variant="primary"
            icon="download"
            onClick={allow}
            disabled={busy}
            data-testid="consent-allow"
          >
            {busy
              ? __("Downloading…", "easycommerce-fakerpress")
              : __("Allow & download", "easycommerce-fakerpress")}
          </Button>
        </div>
      </div>
    </div>
  );
}
