import assert from "node:assert/strict";
import test from "node:test";
import { contactSummary, normalizeContact, validateContact } from "./contact.mjs";

const valid = {
  name: "Aina",
  email: "aina@example.com",
  business: "Kedai Aina",
  plan: "free",
  message: "Saya ingin tahu tentang pakej Starter untuk laman statik.",
};

test("accepts a complete inquiry", () => {
  assert.deepEqual(validateContact(valid), {});
});

test("rejects a short message and an unknown plan", () => {
  const errors = validateContact({ ...valid, plan: "enterprise", message: "pendek" });
  assert.equal(errors.plan, "Pakej tidak dikenali.");
  assert.match(errors.message, /20 aksara/);
});

test("normalizes surrounding space and builds a readable summary", () => {
  const contact = normalizeContact({ ...valid, name: "  Aina  ", business: "" });
  assert.equal(contact.name, "Aina");
  const summary = contactSummary(contact);
  assert.match(summary, /Perniagaan: —/);
  assert.match(summary, /Pakej: Starter/);
});
