import { MonitorSmartphone, Sparkles, Type } from "lucide-react";

const blocks = ["Tajuk", "Perenggan", "Gambar", "Butang"];

export function EditorPreview() {
  return (
    <div className="mx-auto w-full max-w-xl lg:max-w-none">
      <div className="relative lg:grid lg:grid-cols-[minmax(0,1fr)_11rem] lg:items-end lg:gap-5">
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -top-10 right-0 -z-10 h-64 w-64 rounded-full bg-[radial-gradient(circle,#c4b5fd_0%,rgba(79,70,229,0.18)_42%,transparent_70%)] blur-2xl"
      />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute -bottom-8 left-4 -z-10 h-48 w-48 rounded-full bg-[radial-gradient(circle,rgba(79,70,229,0.22),transparent_68%)]"
      />

      <div className="overflow-hidden rounded-[1.4rem] border border-fp-line bg-white shadow-[0_30px_70px_-36px_rgba(15,23,42,0.55)]">
        <div className="flex items-center gap-2 border-b border-fp-line bg-fp-indigo-soft/60 px-3 py-2.5">
          <span className="size-2.5 rounded-full bg-[#fecaca]" />
          <span className="size-2.5 rounded-full bg-[#fde68a]" />
          <span className="size-2.5 rounded-full bg-[#bbf7d0]" />
          <p className="ml-2 min-w-0 flex-1 truncate rounded-md bg-white px-3 py-1 text-[11px] text-fp-muted">
            konsep-editor.vividworks.my
          </p>
          <p className="rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold tracking-wide text-fp-indigo uppercase">
            Konsep
          </p>
        </div>

        <div className="grid min-h-[280px] grid-cols-1 sm:grid-cols-[7.5rem_minmax(0,1fr)]">
          <aside className="hidden border-r border-fp-line bg-[#f8fafc] p-3 sm:block" aria-hidden="true">
            <p className="text-[10px] font-semibold tracking-wide text-fp-muted uppercase">Blok</p>
            <ul className="mt-2 space-y-2">
              {blocks.map((block) => (
                <li key={block} className="rounded-lg border border-dashed border-fp-line bg-white px-2 py-2 text-[11px] font-semibold text-fp-ink">
                  {block}
                </li>
              ))}
            </ul>
          </aside>

          <div className="p-4 sm:p-5" aria-hidden="true">
            <div className="flex items-center justify-between gap-3">
              <p className="text-xs font-semibold text-fp-ink">Contoh Kedai</p>
              <p className="hidden gap-3 text-[10px] text-fp-muted sm:flex">
                <span>Menu</span>
                <span>Tentang</span>
                <span>Hubungi</span>
              </p>
            </div>
            <p className="mt-5 text-[11px] font-semibold tracking-[0.14em] text-fp-indigo uppercase">Pratonton kanvas</p>
            <p className="mt-2 max-w-[16rem] text-lg font-semibold leading-tight text-fp-ink sm:text-xl">
              Susun bahagian tanpa menulis kod.
            </p>
            <p className="mt-2 max-w-xs text-[11px] leading-5 text-fp-muted">
              Paparan konsep drag-and-drop. Blok tidak boleh diseret pada laman ini.
            </p>
            <div className="mt-4 flex flex-wrap gap-2">
              <span className="rounded-full bg-fp-indigo px-3 py-1.5 text-[10px] font-semibold text-white">Lihat contoh</span>
              <span className="inline-flex items-center gap-1 rounded-full border border-fp-line px-3 py-1.5 text-[10px] font-semibold text-fp-ink">
                <Sparkles size={11} />
                AI Content — Akan Datang
              </span>
            </div>
            <div className="mt-4 grid grid-cols-3 gap-2">
              {[
                { label: "Teks", icon: Type },
                { label: "Susun atur", icon: MonitorSmartphone },
                { label: "AI", icon: Sparkles },
              ].map((item) => (
                <div key={item.label} className="rounded-xl border border-fp-line bg-fp-indigo-soft/50 px-2 py-3">
                  <item.icon size={14} className="text-fp-indigo" />
                  <p className="mt-2 text-[10px] font-semibold text-fp-ink">{item.label}</p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      <div className="fp-float mx-auto mt-4 box-border w-[148px] overflow-hidden rounded-[1.5rem] border-[5px] border-fp-ink bg-white shadow-[0_18px_40px_-24px_rgba(15,23,42,0.65)] lg:mx-0 lg:mt-8 lg:w-full">
        <div className="mx-auto mt-1.5 h-1 w-10 rounded-full bg-fp-line" aria-hidden="true" />
        <div className="p-3" aria-hidden="true">
          <p className="text-[11px] font-semibold text-fp-ink">Contoh Kedai</p>
          <p className="mt-3 text-[10px] font-semibold tracking-wide text-fp-indigo uppercase">Telefon</p>
          <p className="mt-1 text-[13px] font-semibold leading-snug text-fp-ink">Pratonton mudah alih.</p>
          <span className="mt-3 inline-flex rounded-full bg-fp-indigo px-2.5 py-1 text-[10px] font-semibold text-white">
            Lihat
          </span>
        </div>
        <p className="sr-only">Pratonton konsep pada telefon, di sebelah paparan desktop.</p>
      </div>

      </div>
      <p className="mt-4 text-center text-xs leading-5 text-fp-muted sm:text-left">
        Visual konsep editor. Bukan tangkapan skrin sistem yang telah dilancarkan. Fungsi AI Content belum tersedia.
      </p>
    </div>
  );
}
