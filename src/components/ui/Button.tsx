import type { ReactNode } from "react";
import { Link } from "react-router-dom";
import { cx } from "../../lib/cx.ts";

type ButtonVariant =
  | "primary"
  | "secondary"
  | "ghost"
  | "light"
  | "outlineLight"
  | "indigo"
  | "indigoSecondary"
  | "indigoGhost"
  | "onIndigo";
type ButtonSize = "md" | "lg";

type ButtonProps = {
  children: ReactNode;
  href?: string;
  external?: boolean;
  variant?: ButtonVariant;
  size?: ButtonSize;
  shape?: "xl" | "full";
  className?: string;
  type?: "button" | "submit";
  onClick?: () => void;
  disabled?: boolean;
  ariaExpanded?: boolean;
  ariaControls?: string;
};

const variants: Record<ButtonVariant, string> = {
  primary: "bg-brand text-white hover:bg-brand-dark",
  secondary: "border border-line bg-white text-navy hover:border-[#c9d4e6] hover:bg-surface",
  ghost: "bg-transparent text-navy hover:bg-surface",
  light: "bg-white text-brand hover:bg-surface",
  outlineLight: "border border-white/45 bg-transparent text-white hover:bg-white/10",
  indigo: "bg-fp-indigo text-white shadow-sm hover:bg-fp-indigo-dark",
  indigoSecondary: "border border-fp-line bg-white text-fp-ink hover:border-fp-indigo/30 hover:bg-fp-indigo-soft",
  indigoGhost: "bg-transparent text-fp-ink hover:bg-fp-indigo-soft",
  onIndigo: "bg-white text-fp-indigo hover:bg-fp-indigo-soft",
};

const sizes: Record<ButtonSize, string> = {
  md: "h-11 px-4 text-sm",
  lg: "h-12 px-5 text-[15px]",
};

export function Button({
  children,
  href,
  external = false,
  variant = "primary",
  size = "md",
  shape = "xl",
  className,
  type = "button",
  onClick,
  disabled = false,
  ariaExpanded,
  ariaControls,
}: ButtonProps) {
  const classes = cx(
    "inline-flex items-center justify-center gap-2 text-center font-semibold transition-colors duration-200 disabled:cursor-not-allowed disabled:opacity-60",
    shape === "full" ? "rounded-full" : "rounded-xl",
    variants[variant],
    sizes[size],
    className,
  );

  if (href && !disabled) {
    if (external || href.startsWith("http://") || href.startsWith("https://") || href.startsWith("mailto:")) {
      return (
        <a href={href} className={classes} onClick={onClick}>
          {children}
          {external ? <span className="sr-only"> (pautan luaran)</span> : null}
        </a>
      );
    }
    if (href.startsWith("#")) {
      return (
        <a href={href} className={classes} onClick={onClick}>
          {children}
        </a>
      );
    }
    return (
      <Link to={href} className={classes} onClick={onClick}>
        {children}
      </Link>
    );
  }

  return (
    <button
      type={type}
      className={classes}
      onClick={onClick}
      disabled={disabled}
      aria-expanded={ariaExpanded}
      aria-controls={ariaControls}
    >
      {children}
    </button>
  );
}
