/**
 * Canonical vividworks.my brand colours.
 * The same hex values are mirrored in src/styles/index.css so the first paint
 * matches Tailwind utilities. scripts/check-tokens.mjs keeps the two in sync.
 */
export const brandColors = {
  navy: "#0A1730",
  ink: "#102033",
  brand: "#1554E0",
  brandDark: "#1146BE",
  ai: "#6558E0",
  surface: "#F4F7FB",
  muted: "#46566C",
  line: "#E5EBF3",
  white: "#FFFFFF",
  /* Frontpage palette. Other pages keep the colours above. */
  fpIndigo: "#4F46E5",
  fpIndigoDark: "#4338CA",
  fpIndigoSoft: "#EEF2FF",
  fpInk: "#0F172A",
  fpMuted: "#475569",
  fpLine: "#E2E8F0",
  fpViolet: "#7C3AED",
} as const;

export const brandFont = {
  family: "Plus Jakarta Sans Variable",
  fallback: "ui-sans-serif, system-ui, sans-serif",
} as const;
