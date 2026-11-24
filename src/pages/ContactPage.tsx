import { useState } from "react";
import type { FormEvent } from "react";
import { useSearchParams } from "react-router-dom";
import { pricingPlans, planById } from "../data/pricing.ts";
import { site } from "../data/site.ts";
import { contactSummary, mailtoHref, validateContact } from "../lib/contact.ts";
import type { ContactDraft, ContactErrors } from "../lib/contact.ts";
import { breadcrumbNode, graph, organizationNodes } from "../lib/schema.ts";
import { AreaField, SelectField, TextField } from "../components/ui/Fields.tsx";
import { Button } from "../components/ui/Button.tsx";
import { Container } from "../components/layout/Container.tsx";
import { PageHeader } from "../components/layout/PageHeader.tsx";
import { Seo } from "../components/seo/Seo.tsx";

const description =
  "Hantar pertanyaan melalui borang ini. Mesej disimpan pada pelayan; tiada e-mel automatik dihantar. E-mel, telefon dan alamat rasmi masih belum disahkan.";

const emptyDraft: ContactDraft = {
  name: "",
  email: "",
  business: "",
  plan: "",
  message: "",
};

type SubmitState =
  | { kind: "idle" }
  | { kind: "saving" }
  | { kind: "saved"; message: string }
  | { kind: "error"; message: string };

export default function ContactPage() {
  const [params] = useSearchParams();
  const requestedPlan = planById(params.get("pakej"))?.id ?? "";
  const [draft, setDraft] = useState<ContactDraft>({ ...emptyDraft, plan: requestedPlan });
  const [syncedPlan, setSyncedPlan] = useState(requestedPlan);
  const [errors, setErrors] = useState<ContactErrors>({});
  const [submit, setSubmit] = useState<SubmitState>({ kind: "idle" });
  const [copied, setCopied] = useState(false);
  const [copyFailed, setCopyFailed] = useState(false);

  if (syncedPlan !== requestedPlan) {
    setSyncedPlan(requestedPlan);
    setDraft((current) => ({ ...current, plan: requestedPlan }));
    setSubmit({ kind: "idle" });
    setCopied(false);
    setCopyFailed(false);
  }

  function update<K extends keyof ContactDraft>(key: K, value: ContactDraft[K]) {
    setDraft((current) => ({ ...current, [key]: value }));
    setSubmit((current) => (current.kind === "saving" ? current : { kind: "idle" }));
    setCopied(false);
    setCopyFailed(false);
  }

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const nextErrors = validateContact(draft);
    setErrors(nextErrors);
    setCopied(false);
    setCopyFailed(false);
    if (Object.keys(nextErrors).length > 0) {
      setSubmit({ kind: "idle" });
      return;
    }

    setSubmit({ kind: "saving" });
    try {
      const response = await fetch("/api/contact", {
        method: "POST",
        headers: { "content-type": "application/json" },
        body: JSON.stringify(draft),
      });
      const body = (await response.json().catch(() => null)) as {
        saved?: boolean;
        message?: string;
        errors?: ContactErrors;
      } | null;

      if (response.status === 400 && body?.errors) {
        setErrors(body.errors);
        setSubmit({ kind: "error", message: "Semak medan yang ditanda, kemudian cuba lagi." });
        return;
      }

      if (!response.ok || !body?.saved) {
        setSubmit({
          kind: "error",
          message: body?.message || "Mesej tidak disimpan.",
        });
        return;
      }

      setSubmit({
        kind: "saved",
        message: body.message || "Mesej telah disimpan. E-mel automatik belum diaktifkan.",
      });
    } catch {
      setSubmit({ kind: "error", message: "Mesej tidak disimpan. Sambungan ke pelayan gagal." });
    }
  }

  async function copyMessage() {
    try {
      await navigator.clipboard.writeText(contactSummary(draft));
      setCopied(true);
      setCopyFailed(false);
    } catch {
      setCopied(false);
      setCopyFailed(true);
    }
  }

  const channels = [
    { label: "E-mel", value: site.contact.email },
    { label: "Telefon", value: site.contact.phone },
    { label: "Alamat", value: site.contact.address },
  ];

  return (
    <>
      <Seo
        title="Hubungi Kami"
        description={description}
        path="/contact"
        jsonLd={graph([
          ...organizationNodes("/contact"),
          breadcrumbNode([
            { name: "Utama", path: "/" },
            { name: "Hubungi Kami", path: "/contact" },
          ]),
        ])}
      />
      <PageHeader eyebrow="Hubungi" title="Hubungi Kami" description={description} />
      <Container className="grid gap-10 py-14 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] sm:py-16">
        <aside className="rounded-2xl border border-line bg-surface p-5 sm:p-6">
          <h2 className="text-lg font-semibold text-navy">Saluran rasmi</h2>
          <p className="mt-2 text-sm leading-6 text-muted">
            Maklumat di bawah menggunakan pemegang tempat sehingga butiran syarikat diberikan.
          </p>
          <dl className="mt-5 space-y-4">
            {channels.map((channel) => (
              <div key={channel.label}>
                <dt className="text-xs font-semibold tracking-wide text-muted uppercase">{channel.label}</dt>
                <dd className="mt-1 text-sm font-medium text-navy">{channel.value || "Belum disahkan"}</dd>
              </div>
            ))}
          </dl>
          {requestedPlan ? (
            <p className="mt-5 text-sm leading-6 text-ink">
              Anda membuka halaman ini berkaitan pakej {planById(requestedPlan)?.name}. Butiran pakej itu mungkin masih menunggu pengesahan.
            </p>
          ) : null}
        </aside>

        <div>
          <form className="rounded-2xl border border-line bg-white p-5 sm:p-6" onSubmit={(event) => void onSubmit(event)} noValidate>
            <p className="rounded-xl bg-surface px-4 py-3 text-sm leading-6 text-ink">
              Mesej disimpan pada pelayan laman ini. Tiada e-mel dihantar secara automatik.
            </p>
            <div className="mt-5 grid gap-4">
              <TextField
                id="nama"
                name="name"
                label="Nama"
                autoComplete="name"
                value={draft.name}
                error={errors.name}
                onChange={(event) => update("name", event.target.value)}
              />
              <TextField
                id="emel"
                name="email"
                type="email"
                label="E-mel"
                autoComplete="email"
                value={draft.email}
                error={errors.email}
                onChange={(event) => update("email", event.target.value)}
              />
              <TextField
                id="perniagaan"
                name="organization"
                label="Nama perniagaan"
                autoComplete="organization"
                hint="Pilihan."
                value={draft.business}
                error={errors.business}
                onChange={(event) => update("business", event.target.value)}
              />
              <SelectField
                id="pakej-hubungan"
                name="plan"
                label="Pakej"
                value={draft.plan}
                onChange={(event) => update("plan", event.target.value)}
              >
                <option value="">Belum pasti</option>
                {pricingPlans.map((plan) => (
                  <option key={plan.id} value={plan.id}>
                    {plan.name}
                  </option>
                ))}
              </SelectField>
              <AreaField
                id="mesej"
                name="message"
                label="Mesej"
                value={draft.message}
                error={errors.message}
                onChange={(event) => update("message", event.target.value)}
              />
            </div>
            <div className="mt-5">
              <Button type="submit" disabled={submit.kind === "saving"}>
                {submit.kind === "saving" ? "Sedang menyimpan…" : "Hantar mesej"}
              </Button>
            </div>
          </form>

          {submit.kind === "saved" ? (
            <div className="mt-4 rounded-2xl border border-line bg-white p-5" role="status">
              <h2 className="text-lg font-semibold text-navy">Mesej telah disimpan</h2>
              <p className="mt-2 text-sm leading-6 text-muted">{submit.message}</p>
              <pre className="mt-4 overflow-x-auto rounded-xl bg-surface p-4 text-sm leading-6 whitespace-pre-wrap text-ink">
                {contactSummary(draft)}
              </pre>
              <div className="mt-4 flex flex-col gap-2 sm:flex-row">
                <Button type="button" variant="secondary" onClick={() => void copyMessage()}>
                  {copied ? "Disalin" : "Salin mesej"}
                </Button>
                {site.contact.email ? (
                  <Button href={mailtoHref(site.contact.email, draft)} variant="primary">
                    Buka aplikasi e-mel
                  </Button>
                ) : null}
              </div>
              {copyFailed ? (
                <p className="mt-3 text-sm text-[#9f1239]" role="alert">
                  Tidak dapat menyalin secara automatik. Pilih teks di atas dan salin secara manual.
                </p>
              ) : null}
            </div>
          ) : null}

          {submit.kind === "error" ? (
            <p className="mt-4 rounded-xl border border-[#fecdd3] bg-[#fff1f2] px-4 py-3 text-sm leading-6 text-[#9f1239]" role="alert">
              {submit.message}
            </p>
          ) : null}
        </div>
      </Container>
    </>
  );
}
