import { NavLink } from "react-router-dom";
import { Button } from "../components/ui/Button.tsx";
import { cx } from "../lib/cx.ts";
import type { FutureTarget } from "../lib/actions.ts";

type Item = { id: string; label: string; href: string };

type MobileMenuProps = {
  id: string;
  items: readonly Item[];
  currentId: string;
  login: FutureTarget;
  start: FutureTarget;
};

export function MobileMenu({ id, items, currentId, login, start }: MobileMenuProps) {
  return (
    <div id={id} className="fixed inset-x-0 top-[4.25rem] bottom-0 z-40 overflow-y-auto border-t border-fp-line bg-white xl:hidden">
      <nav aria-label="Mudah alih" className="mx-auto flex w-full max-w-7xl flex-col gap-1 px-4 py-4">
        {items.map((item) => {
          const current = item.id === currentId;
          return (
            <NavLink
              key={item.id}
              to={item.href}
              aria-current={current ? "location" : undefined}
              className={cx(
                "rounded-xl px-3 py-3 text-base font-medium text-fp-ink hover:bg-fp-indigo-soft",
                current && "bg-fp-indigo-soft text-fp-indigo",
              )}
            >
              {item.label}
            </NavLink>
          );
        })}
        <div className="mt-3 grid gap-2">
          <Button href={login.href} external={login.external} variant="indigoSecondary" shape="full" className="w-full">
            Log Masuk
          </Button>
          <Button href={start.href} external={start.external} variant="indigo" shape="full" className="w-full">
            Bina Website Percuma
          </Button>
        </div>
      </nav>
    </div>
  );
}
