# F1 dan ujian staging — rekod 22 November 2026

Waktu Malaysia (UTC+8). Rekod ini ditulis pada 11:41 malam, 22 November 2026, apabila kerja diberhentikan untuk malam ini.

Fail ini merekod apa yang sudah dibina untuk F1 mock dan setiap ujian yang sudah dijalankan. Esok sambung dari seksyen "Berhenti di sini". Jangan ulang baris yang sudah ada.

## Berhenti di sini

Workflow n8n `sdbCW1olkS0QeMFf`, "VividAI - Design Agent (Async)":

- tidak diterbitkan: `active` false, `activeVersionId` null
- 9 nod, `versionId` `2e3b39d6-f2f2-4a8a-b4bb-981f44c60992`
- dikemas kini 11:37 malam, 22 November 2026 (Malaysia)
- stub berjaya Kedai Contoh dipulihkan
- nod jeda "Hold for Laravel record" dibuang
- `Build Callback Payload` disambung terus ke `Send Result to Laravel`
- `saveExecutionProgress` kembali ke lalai
- `Call Design Agent` kekal terputus dan dilumpuhkan, supaya W1 dan OpenRouter tidak dipanggil
- skrip perakam sementara sudah dipadam

Ujian seterusnya, hanya jika disambung: `item.error` yang ada `message` dan `description` sekali. Pembina sepatutnya menyimpan `message` dan mengabaikan `description`.

Jangan bina nod cadangan pemeriksaan 21 November: pintu duplikat, `callback_id`, hantar semula callback yang sama, salinan `media_allowlist`, atau nod retry yang mengakui callback. Jangan terbitkan W2. Jangan picu webhook produksi `vividai/design-agent-async`.

## Yang kekal terbuka

Sembilan keputusan pemilik kekal **OPEN**: D-FUTURE, D-AMB, D-TERM, D-NOTSENT, D-REJ-OTHER, D-S1, D-B8, D-CID, D-W3.

Tiada timeout, bilangan retry, selang retry, atau algoritma `callback_id` yang dicipta dalam ujian ini.

Laravel ialah pihak berkuasa kitaran attempt dan retry. `retryable` daripada n8n ialah petunjuk diagnostik sahaja. Ia tidak membuka attempt baharu.

F2 Live, M0–M5, dan A7 kekal **NOT AUTHORIZED**. Runtime W3 yang diterbitkan ialah 6 nod. Draf 35 nod tidak diterbitkan. Project Master Record v1.8 kekal **DRAFT**.

## F1 yang dibina

F1 di sini ialah API penjanaan Laravel sahaja. Ia bukan tapak pemasaran.

Laluan:

- `POST /api/ai/design-generations`
- `POST /api/ai/design-callback`

Pengikat staging ialah `UnknownDesignInvokeStub`. POST awam ke penjanaan tidak merekod `job_id` W2. Untuk ujian padanan, baris ditulis dengan `DesignGenerationService::persist` melalui reflection, kemudian `F1MockDispatcher::apply` dengan `InvokeClassification::accepted202(jobId)`.

Kelas penghantaran dalam `F1MockDispatcher`:

| Kelas | Kesan yang dikunci |
| --- | --- |
| `accepted_202` | Attempt `accepted`, `job_id` disimpan, HTTP 202, parent `accepted`, outbox `dead` / `accepted_202` |
| `unknown` | Attempt kekal ambiguous, parent `invoking`. Ini lalai stub staging |
| `confirmed_not_sent` | Perlu bukti positif. Tanpa bukti, ia diturunkan kepada `unknown`. Hantar semula kekal pada attempt yang sama |
| `rejected_400` | HTTP 400 `invalid_input`, gagal, `retryable` false, outbox `dead` |
| `rejected_other` | Bukan 400. Parent dan attempt kekal `invoking`, outbox `dead` |
| `recordOperationalUnconfirmed` | Parent `invoke_unconfirmed`, attempt ambiguous, tiada tempoh disimpan |
| `reopen` | Pelaku, sebab, dan `duplicate_risk_acknowledged`. Attempt lama `superseded`. Attempt baharu `pending_invoke`, `job_id` null |

Padanan callback ketat pada `job_id`.

- `project_id`, `request_id`, atau `attempt_id` tidak sepadan, atau `job_id` sudah dimiliki attempt lain: HTTP 409 `identity_conflict`
- `callback_id` atau hash kanonik SHA-256 yang sama: HTTP 200, `applied` false, sebab `duplicate`
- Attempt semasa bukan `accepted`: `applied` false, `current_attempt_non_accepted`
- Attempt semasa lapuk, diganti, atau null: `stale_attempt`
- Parent sudah terminal: attempt boleh selesai, parent kekal, sebab `generation_terminal`

Parse struktur (`DesignCallbackRules`) berlaku sebelum tulis inbox. Pintu semantik (`DesignCallbackSemanticGate`) menulis inbox, kemudian boleh membalas 422 `invalid_payload` dan meninggalkan attempt `accepted`.

Kontrak yang dikunci oleh kod:

- Sampul dan SiteSpec `schema_version` 1.1
- `prompt_version` `v1.1.0` untuk kejayaan
- `page_count` 1 memerlukan slug `home`, dalam turutan itu
- `error.details` mesti objek apabila ada
- `usage` tidak dibenarkan pada callback ralat
- Had saiz 20 KB membalas 413 sebelum controller
- PostgreSQL: satu attempt terbuka (`generation_attempts_one_open_unique`), foreign key komposit `generations_current_attempt_same_generation`, unik `(project_id, request_id)`
- Templat aktif: `minimal-clean`, `premium-luxury`, `modern-commerce`, `corporate-pro`, `food-restaurant`, `creative-tech`

## Ujian PHPUnit

Ujian ini berjalan pada SQLite dalam memori. Ia bukan panggilan n8n dan bukan baris staging.

`tests/Feature/F1MockLifecycleTest.php`

- `confirmed_not_sent` memerlukan bukti positif, dan hantar semula kekal pada attempt yang sama
- `invoke_unconfirmed` tidak meruntuhkan status attempt
- reopen manual tidak mencipta retry daripada `retryable`
- callback tanpa `attempt_id` padan pada `job_id` yang direkod, dan `retryable` tidak membuka attempt
- `callback_id` pendua tidak dipakai dua kali

`tests/Feature/DesignGenerationTest.php`

- `page_count` 1, 2, dan 3 diterima tanpa rangkaian
- `page_count` hilang, bukan integer, atau di luar julat ditolak
- templat rasmi dihantar, templat tidak dikenali ditolak
- kegagalan stub selepas commit kekal `unknown` dan mengekalkan attempt
- HTTP 400 `invalid_input` menggagalkan attempt tanpa attempt baharu
- status HTTP lain bukan kegagalan perniagaan dan tidak mencuba semula

`tests/Feature/DesignCallbackTest.php`

- kejayaan menyimpan SiteSpec dan menyelesaikan penjanaan
- kegagalan semantik ditolak sebelum stor
- `schema_version` sampul 1.2 ditolak sebelum stor
- `schema_version` SiteSpec selain 1.1 ditolak sebelum stor
- medan tambahan, enum tidak sah, dan jenis tidak sah ditolak sebelum stor
- ralat dengan `http_status` null menggagalkan penjanaan tanpa retry
- ralat tanpa `http_status` menyimpan null
- kejayaan mewajibkan `http_status`
- `http_status` 0 ditolak
- token hilang atau tidak sah membalas 401 tanpa carian
- attempt tidak dikenali membalas 404 dan tidak menulis
- `project_id` tidak sepadan membalas 409 tanpa perubahan
- `job_id` yang sudah pada attempt lain membalas 409
- attempt yang sama dengan `job_id` lain membalas 409
- callback tidak mengikat `job_id` yang tidak direkod
- callback sama ialah pendua, dan susunan kunci yang berbeza tetap sepadan
- muatan berbeza pada attempt terminal membalas 409
- callback lapuk hanya mengemas attempt lama
- callback attempt semasa mengemas hasil penjanaan
- hash null pada attempt terminal bukan pendua
- callback terlalu besar membalas JSON 413
- kegagalan tulis dipaksa menggulung balik attempt
- `current_attempt_id` null dibenarkan dan tidak menerima hasil
- foreign key komposit menolak attempt penjanaan lain
- attempt terbuka kedua untuk penjanaan yang sama ditolak
- callback merekod attempt tanpa mengubah penjanaan yang sudah `completed`
- callback tidak membuka semula penjanaan yang tamat tempoh
- callback tidak mengubah penjanaan yang sudah `failed`
- `job_id` milik penjanaan lain membalas 409 tanpa tulis separa

`tests/Unit/SiteSpecSemanticValidatorTest.php` meliputi pelan halaman 1–3, panjang selepas trim, warna hex, id seksyen, slug, allowlist imej, kiraan, templat, konteks runtime, sasaran CTA, dan tiada mutasi calon.

`tests/Unit/SiteSpecStructuralValidatorTest.php` meliputi hash skema kanonik, fixture sah, medan tambahan, enum, jenis, tatasusunan berbanding objek, dan kata kunci skema yang tidak disokong.

`tests/Unit/CallbackPayloadHasherTest.php` meliputi susunan kunci objek, susunan tatasusunan, dan `schema_version` 1.1.

`tests/Feature/InquiryApiTest.php` dan `tests/Unit/ContactRulesTest.php` ialah API hubungan sedia ada, bukan kitaran F1.

## Ujian F1 pada staging, sebelum rentetan W2

Semua kelas ini sudah dijalankan pada API staging. Baris sejarah tidak ditulis semula. Identiti baris tidak disalin semula ke fail ini.

- `confirmed_not_sent`
- penghantaran tidak disahkan (`invoke_unconfirmed`)
- `rejected_400`
- `rejected_other`
- attempt semasa bukan `accepted`
- pendua `callback_id`
- pendua hash, termasuk pembetulan cap masa tetap supaya hash tidak berubah
- 422 semantik
- 422 struktur
- attempt lapuk
- 409 identiti
- `job_id` dimiliki attempt lain
- parent terminal
- attempt semasa null
- hash null
- `http_status` hilang
- ralat dengan `http_status` null dan `retryable` true
- 413 sebelum controller
- unik satu attempt terbuka
- foreign key komposit

## Ujian W2 tidak diterbitkan

Mod ujian ialah pelaksanaan manual MCP. Webhook produksi tidak dipicu. Kredensial callback sudah dipasang pada `Send Result to Laravel`. Nod itu `neverError` true, jadi 422 atau 409 daripada Laravel tetap kelihatan sebagai kejayaan nod.

Untuk ujian yang sampai ke callback, baris `accepted_202` direkod semasa jeda 45 saat, kemudian callback dihantar. Selepas setiap kumpulan, stub Kedai Contoh dipulihkan dan jeda dibuang.

Pasangan `project_id` 64 aksara `a` dan `request_id` 100 aksara `b` sudah `completed`. Jangan guna semula. Jangan tulis semula penjanaan `5fb6e47c-e52e-4a05-96e7-90888e9f70aa`.

### Pelaksanaan 82 — callback tanpa baris

22 November 2026, 10:08 malam. `job_mwrwx7fx_i047sa`, permintaan `trial-w2-stub-20261122`. HTTP 200, `applied` false, `unmatched_pending_reconciliation`. Inbox klasifikasi itu. Penjanaan 0. Tiada `callback_id`.

### Pelaksanaan 83 — kejayaan padan

10:16–10:17 malam. `job_mwrx71b5_pd5mbz`, `trial-w2-match-20261122`. HTTP 200, `applied` true. Penjanaan `ffdeb6a3-5ec9-45be-8b10-4d0c616dd8d5` `completed`. Attempt `088c213f-d7b1-460f-b6ec-f78fef2f14ca` `completed`. Inbox `matched`. SiteSpec Kedai Contoh.

### Pelaksanaan 84 — ralat ejen

10:21–10:22 malam. `job_mwrxda2d_uc6uwq`, `trial-w2-error-20261122`. HTTP 200, `applied` true. Penjanaan `4eed47b9-1b13-4607-963f-a1bfcf54bbfa` `failed`. Satu attempt `5f3dd276-46f8-4fd3-923a-6dd63676d0f5`, HTTP 502, `agent_unreachable`, `retryable` true. Inbox `matched`. SiteSpec null. Tiada attempt kedua.

### Pelaksanaan 85 — pra-penerbangan

Badan tanpa `project_id` dan `request_id`. Berhenti di `Respond Rejected`. `invalid_input`, HTTP 400, `retryable` false, kedua-dua medan wajib. Stub, 202, dan `Send Result` tidak berjalan. Tiada baris Laravel.

### Pelaksanaan 86 — badan kosong

10:32–10:33 malam. Stub `{}`. `job_mwrxrqud_2bog7j`, `trial-w2-empty-20261122`. `invalid_agent_response`, HTTP 502, `retryable` true. Penjanaan `0f36c942-2912-453f-8ee0-789fcff4ce94` `failed`. Attempt `db8f1d8f-0784-42c8-a1d3-275a365dc008` `failed`. Inbox `matched`. Tiada attempt baharu.

### Pelaksanaan 87–91

- 87: `project_id` nombor. Mesej "must be a string" dan "required". Berhenti di tolakan. Tiada callback.
- 88: `project_id` 65 aksara. Melebihi 64. Tiada callback.
- 89: `project_id` ruang putih. Wajib. Tiada callback.
- 90: `request_id` 101 aksara. Melebihi 100. Tiada callback.
- 91: 10:36–10:37 malam. Ralat pengangkutan `{ error: { message: "connection reset" } }`. `job_mwrxwjh5_2o87su`, `trial-w2-transport-20261122`. Penjanaan `3145c199-4701-4c40-99ad-a03a3689461d` `failed`. Attempt `996b8a42-0890-40cf-b64d-8422f3dc786e`, HTTP 502, `agent_unreachable`, mesej "connection reset", `retryable` true. Inbox `matched`. Tiada attempt baharu.

### Pelaksanaan 92–97

- 92: `request_id` nombor. Tolakan. Tiada callback.
- 93: `request_id` kosong. Tolakan. Tiada callback.
- 94: `project_id` null. Tolakan. Tiada callback.
- 95: kedua-duanya melebihi had, 65 dan 101. Tolakan. Tiada callback.
- 96: tiada badan. Kedua-dua medan wajib. Bukan mesej "body must be a JSON object", kerana badan yang hilang dibaca sebagai objek kosong. Tiada callback.
- 97: 10:39–10:40 malam. Tepat 64 aksara `a` dan 100 aksara `b`. `job_mwry0wga_rsuqev`. Penjanaan `5fb6e47c-e52e-4a05-96e7-90888e9f70aa` `completed`. Attempt `065ccdd9-5bf1-4a2f-9ba4-cdae9fcda4ab` `completed`, HTTP 200. Inbox `matched`. SiteSpec Kedai Contoh. Jangan guna semula pasangan ini.

### Pelaksanaan 98–100

- 98: `request_id` null. Wajib sahaja. Tiada callback.
- 99: `project_id` objek. "must be a string" dan "required". Tiada callback.
- 100: 10:46–10:47 malam. Slug `about` dengan `page_count` 1. `job_mwry9pi2_jkibja`, `trial-w2-semantic-20261122`. Laravel HTTP 422 `invalid_payload`, `/pages: page_count 1 requires slugs home in that order.` Penjanaan `05e24153-6858-4960-a04b-26f8f6748184` kekal `accepted`. Attempt `78fc31e8-029e-4a82-a298-820acdebabec` kekal `accepted`, HTTP 202. Inbox `invalid_payload`. SiteSpec null. Nod n8n berjaya kerana `neverError`.

### Pelaksanaan 101–103

- 101: `request_id` tatasusunan. Tolakan. Tiada callback.
- 102: `project_id` boolean. Tolakan. Tiada callback.
- 103: 10:51–10:52 malam. SiteSpec `schema_version` 1.2. `job_mwryg9n3_smuqq2`, `trial-w2-struct-20261122`. HTTP 422, `sitespec/schema_version`: `"1.2" is not an allowed value (1.1).` Penjanaan `8188f903-20df-4f12-a735-7a1ce6f55f77` kekal `accepted`. Attempt `59e260bf-d3a0-4ef9-8977-17523a01737f` kekal `accepted`, HTTP 202. Inbox 0, kerana ini struktur, bukan semantik.

### Pelaksanaan 104–106

- 104: `project_id` berlapik masih melebihi 64 selepas trim. Tolakan. Respons memakai id yang sudah dipangkas.
- 105: `request_id` berlapik masih melebihi 100 selepas trim. Tolakan.
- 106: 10:54–10:55 malam. Tepat 64/100 selepas trim. `job_mwryjxqn_3z6zkw`. 202 dan callback memakai id yang dipangkas. `forward_body` menyimpan rentetan berlapik asal. Pasangan itu sudah `completed` pada pelaksanaan 97, jadi Laravel HTTP 200, `applied` false, `unmatched_pending_reconciliation`. Kiraan penjanaan untuk projek 64 aksara `a` kekal 1. Tiada penjanaan kedua.

### Pelaksanaan 107–109

- 107: `request_id` boolean. Tolakan. Tiada callback.
- 108: `project_id` tatasusunan. Tolakan. Tiada callback.
- 109: 11:00 malam. Stub `statusCode` 422, status ralat, `invalid_agent_response`, `details` objek `{ field: pages }`, `retryable` false. `job_mwryravb_99iady`, `trial-w2-details-20261122`. HTTP 200, `applied` true. Penjanaan `1b2bc94a-b5f5-433f-b03b-a1f2a2927c2e` `failed`. Satu attempt `aa97bc71-5dba-4214-8f36-442587ea5efe`, HTTP 422, `retryable` false, `error_details` `{ field: pages }`. Inbox `matched`. SiteSpec null. Tiada attempt baharu.

### Pelaksanaan 110 — HTTP 200 tanpa badan JSON

11:02:36–11:03:24 malam. `job_mwryudyy_lrdqjb`, `trial-w2-emptybody` / `trial-w2-emptybody-20261122`. Stub `{ statusCode: 200 }`. Callback: `invalid_agent_response`, mesej tiada badan JSON yang boleh dibaca, `retryable` true, `http_status` 200 kerana `statusCode` 200 wujud. Tiada `details`, tiada `usage`, tiada SiteSpec. Laravel HTTP 200, `applied` true. Penjanaan `5c79b29d-883f-490c-8851-a97e9c365d7f` `failed`. Attempt `f8d00e11-ce6b-4896-8fe4-2005f57a60e1` `failed`, HTTP 200. Inbox `matched`. Satu attempt.

### Pelaksanaan 111 — `description` tanpa `message`

11:05:16–11:06:05 malam. `job_mwryxtnq_86ika9`, `trial-w2-errdesc` / `trial-w2-errdesc-20261122`. `error.description` "socket hang up". Callback: `agent_unreachable`, mesej "socket hang up", HTTP 502, `retryable` true. Penjanaan `5dbcb666-a39e-4068-9010-eef8d4ef0123` `failed`. Attempt `ee4e915a-de15-44be-a985-14f5e62570c5`. Inbox `matched`. Satu attempt. `error_details` null.

### Pelaksanaan 112 — status bukan `ok` dan bukan `error`

11:10:36–11:11:25 malam. `job_mwrz4oi8_iglv61`, `trial-w2-agenterror` / `trial-w2-agenterror-20261122`. Badan `{ status: "pending" }`. Lalai: `agent_error`, mesej "The design job did not complete.", `retryable` false, HTTP 502. Penjanaan `6c6f3c25-8021-4d54-9ece-b9b8995c03ab` `failed`. Attempt `fae016fc-06b6-4a1b-9e9e-47c00b873ff8`. Inbox `matched`. `error_details` null. Satu attempt.

### Pelaksanaan 113 — ralat menang atas badan `ok`

11:15:11–11:16:00 malam. `job_mwrzal06_zdbm0n`, `trial-w2-errorwins` / `trial-w2-errorwins-20261122`. `error.message` "upstream timed out" bersama badan `status` `ok` dan SiteSpec. Tiada `statusCode`. Callback kekal `agent_unreachable`, HTTP 502, `retryable` true. SiteSpec dibuang. Penjanaan `4594cb94-52b3-44a6-9b01-15a6678d870d` `failed`. Attempt `bc56b153-86d7-4074-a5e7-0ab43a21af5c`. Inbox `matched`. Satu attempt.

### Pelaksanaan 114 — `details` tatasusunan

11:17:01–11:17:51 malam. `job_mwrzcxxo_c8lxmb`, `trial-w2-arraydetails` / `trial-w2-arraydetails-20261122`. `details` `["pages"]` dibuang. Callback: `invalid_agent_response`, "pages failed", `retryable` false, HTTP 422. Penjanaan `2b705389-a2a2-4200-976c-4647465ec2ac` `failed`. Attempt `a7d08f6c-8e4f-40aa-905d-198d52778128`. Inbox `matched`. `error_details` null. Satu attempt.

### Pelaksanaan 115 — `usage` pada ralat

11:19:52–11:20:41 malam. `job_mwrzglgk_ou5mhn`, `trial-w2-nousage` / `trial-w2-nousage-20261122`. `usage` dibuang. Jika dihantar, kontrak 1.1 menolak medan tambahan. Callback diterima: `invalid_agent_response`, HTTP 422, `retryable` false. Penjanaan `49860a66-4f58-4aec-b6a6-7a6d9b19d08d` `failed`. Attempt `0758666e-275d-4c83-8582-d4bed144da8c`. Inbox `matched`. Satu attempt.

### Pelaksanaan 116 — `retryable` rentetan

11:22:29–11:23:18 malam. `job_mwrzjyvy_iwh0q8`, `trial-w2-retrystr` / `trial-w2-retrystr-20261122`. Rentetan `"true"` menjadi boolean `false` kerana perbandingan ketat. HTTP 422, `invalid_agent_response`, mesej "retryable was a string". Penjanaan `ccda5995-e1b3-4b16-a77a-850da6b676dc` `failed`. Attempt `1a156525-d3d1-40a7-9c4b-99dd3b84896b`. Inbox `matched`. Satu attempt.

### Pelaksanaan 117 — `statusCode` 200 menang

11:32:12–11:33:00 malam. `job_mwrzwg9g_y1v2z3`, `trial-w2-statuswins` / `trial-w2-statuswins-20261122`. Badan `http_status` 422, `statusCode` 200. Callback `http_status` 200. Penjanaan `bb57c0bb-5fc2-4bdf-a509-b4268e23421d` `failed`. Attempt `95f6faa1-911a-4f2b-9006-13d174ffc752`, HTTP 200, `invalid_agent_response`, `retryable` false. Inbox `matched`. Satu attempt.

### Pelaksanaan 118 — `statusCode` 0 jatuh ke badan

11:36:21–11:37:10 malam. `job_mws01srt_9kw1q7`, `trial-w2-statuszero` / `trial-w2-statuszero-20261122`. `statusCode` 0 dan badan `http_status` 422. Callback `http_status` 422. Penjanaan `f2a01fa0-e6de-4cf4-b31e-d64861c4b5ac` `failed`. Attempt `76828ebd-4bb5-4a51-b4d3-3e2480af643e`, HTTP 422, `invalid_agent_response`, `retryable` false. Inbox `matched`. Satu attempt.

## Belum diuji

- `item.error` dengan `message` dan `description` sekali. `message` sepatutnya menang.
- `statusCode` berupa rentetan, bukan nombor. Itu belum diasingkan daripada `statusCode` 0.
- Laluan kejayaan yang menyalin `usage` daripada `revision_metadata.usage`.

Jangan tambah tolakan pra-penerbangan yang hampir sama dengan 87–108.
