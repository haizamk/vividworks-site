# VividWorks F1 — A7 Isolated n8n Runtime Test Plan v12

Tarikh v1–v7: 19 November 2026
Tarikh v8: 19 November 2026, 12:25 PM (Asia/Kuala Lumpur)
Tarikh v9: 19 November 2026, 12:45 PM (Asia/Kuala Lumpur)
Tarikh v10: 19 November 2026, 1:00 PM (Asia/Kuala Lumpur)
Tarikh v11: 19 November 2026, 1:31 PM (Asia/Kuala Lumpur)
Tarikh v12: 19 November 2026, 2:13 PM (Asia/Kuala Lumpur)
Jenis: **PELAN DAN PEMERIKSAAN SAHAJA**. Tiada ujian A7 dijalankan. Tiada persekitaran operasi diubah.
Status A7: **PROPOSED — NOT AUTHORIZED FOR EXECUTION.**

v8 membetulkan ketidaktepatan dalam v7: pemisahan peringkat verifikasi, kiraan perbandingan, kod mati, pengesahan manifest, dan model hasil. v9 menambah pengambilan blok marker secara grammar penuh, kontrak kanonikalisasi eksplisit, dan medan manifest yang benar-benar ketat. v1–v8 tidak diubah.

## 0x. Ringkasan pembetulan v12 — penunjuk versi workflow secara langsung

v12 ialah pembetulan **dokumentasi sahaja**. Ia tidak menjalankan A7, tidak menyentuh workflow n8n, dan tidak mengubah satu pun daripada sepuluh nilai hash yang diluluskan. v11 kekal sebagai baseline dokumentasi yang diterima dan **tidak diubah**.

**Punca.** Rekod terdahulu menyatakan bahawa rekod execution #80 **tiada** penunjuk versi workflow, dan atribusi versi hanya boleh dibuat melalui cap masa dan `activeVersionId`. Pemilik telah membekalkan semula bacaan API n8n secara langsung yang menunjukkan medan peringkat akar `workflowVersionId` hadir dalam rekod execution itu sendiri.

**Bukti baharu yang dibekalkan pemilik.** Bacaan API n8n (Assistant) melalui `GET /executions/80`:

| Medan | Nilai |
| --- | --- |
| `id` | `80` |
| `workflowId` | `fg1kGKB6ciYADxu9` |
| `workflowName` | `VividAI - Design Agent` |
| `workflowVersionId` | `c413686f-5ef0-452f-9b55-f1d9075455de` |
| `status` | `success` |
| `mode` | `webhook` |
| `startedAt` | `2026-11-13T04:17:54.989Z` |
| `finishedAt` | `2026-11-13T04:18:11.423Z` |
| `lastNodeExecuted` | `Respond Success` |

Laluan JSON bagi medan itu: **`$.workflowVersionId`**. Pemilik menyatakan ia medan intrinsik dalam rekod execution, bukan perkaitan yang diinferens atau metadata yang disuntik dari luar.

**Atribusi sumber bukti — penting.**

| Dakwaan | Sumber | Status |
| --- | --- | --- |
| `workflowVersionId` hadir dalam rekod execution #80 | Bacaan API n8n oleh Assistant, dibekalkan pemilik | **VERIFIED** — berdasarkan rekod yang dibekalkan pemilik |
| Cursor memeriksa medan itu secara bebas melalui sambungannya sendiri | Cursor, `get_workflow_execution` + `search_workflow_executions`, 19 Nov 2026 | **TIDAK DISOKONG** — lihat 0x.1 |

**0x.1 Pemeriksaan bebas oleh Cursor — keputusan dan hadnya.** Cursor membuat dua bacaan baca-sahaja melalui sambungan n8n yang dibenarkan pada 19 November 2026: `get_workflow_execution(workflowId=fg1kGKB6ciYADxu9, executionId=80, includeData=false)` dan `search_workflow_executions(workflowId=fg1kGKB6ciYADxu9, limit=5)`. Kedua-duanya memulangkan **subset medan yang dinormalkan**:

```text
{"id":"80","workflowId":"fg1kGKB6ciYADxu9","mode":"webhook","status":"success",
 "startedAt":"2026-11-13T04:17:54.989Z","stoppedAt":"2026-11-13T04:18:11.423Z",
 "retryOf":null,"retrySuccessId":null,"waitTill":null}
```

Medan `workflowVersionId`, `workflowName`, `finishedAt` dan `lastNodeExecuted` **tidak hadir** dalam unjuran itu. Perhatikan juga bahawa medan masa yang sama dilaporkan sebagai `stoppedAt`, bukan `finishedAt`, dan `id` sebagai rentetan `"80"`, bukan nombor. Ini menunjukkan unjuran medan yang berbeza, bukan percanggahan fakta. Oleh itu:

* Pemeriksaan Cursor **mengesahkan** id, workflowId, mod, status dan kedua-dua cap masa bagi execution #80.
* Pemeriksaan Cursor **tidak dapat mengesahkan atau menyangkal** kewujudan `workflowVersionId`, kerana medan itu tidak dimasukkan dalam unjuran alat tersebut.

Kesimpulan: bukti penunjuk versi langsung **bergantung kepada rekod yang dibekalkan pemilik**. Ia **tidak** disahkan secara bebas oleh Cursor. Ini mesti kekal dinyatakan dalam setiap rujukan kepada atribusi versi.

**Apa yang berubah dalam klasifikasi.**

| Dakwaan | v11 | v12 |
| --- | --- | --- |
| Rekod execution mengandungi penunjuk versi workflow | **ABSENT** (dinyatakan tiada) | **VERIFIED** — `$.workflowVersionId` dalam rekod yang dibekalkan pemilik |
| Atribusi versi hanya melalui cap masa / `activeVersionId` | Satu-satunya asas | **Tidak lagi** — penunjuk langsung kini tersedia; cap masa kekal sebagai sokongan konsisten |
| Versi tepat yang digunakan oleh execution #80 tidak dapat dikenal pasti | Dinyatakan tidak dapat | **Dibatalkan** — versi ialah `c413686f-5ef0-452f-9b55-f1d9075455de` |

**Apa yang TIDAK berubah.** Kesepuluh-sepuluh nilai hash kekal. Definisi Lapisan 1/Lapisan 2 kekal. `OFFICIAL_W1_FIXTURE` kekal **NOT VERIFIED**. Manifest kekal **PENDING_OWNER_CONFIRMATION**. Execution #80 **tidak** dilantik sebagai fixtur rasmi W1.

## 0w. Ringkasan pembetulan v11 — takrifan Lapisan 1 dan Lapisan 2

v11 ialah pembetulan **dokumentasi sahaja**. Ia tidak menjalankan A7, tidak menyentuh n8n, Docker, pangkalan data, kredensial atau deployment, dan tidak mengubah satu pun daripada sepuluh nilai hash yang diluluskan. v1–v10 tidak diubah.

**Punca.** Semakan provenance v10 (`docs/vividworks-f1-a7-v10-source-provenance-review.md`) mendapati bahawa bagi empat fixtur, nilai yang dilabel "Lapisan 1" **bukan** hash bait fail asal. Ia bersamaan tepat dengan SHA-256 bagi serialisasi JSON kanonik **dengan satu baris baru akhir**:

```text
sha256( json.dumps(obj, indent=2, ensure_ascii=False) + "\n" )   -> nilai "Lapisan 1" fixtur
sha256( json.dumps(obj, indent=2, ensure_ascii=False) )           -> nilai "Lapisan 2" fixtur
```

Bagi schema, "Lapisan 1" memang hash bait fail sebenar (`canonical-sitespec-schema-v1.1.json`, 3,566 bait), dan "Lapisan 2" ialah serialisasi kanonik tanpa baris baru. Untuk schema, dua nilai itu memang dua ukuran yang berbeza.

**Keputusan pemilik: Pilihan A.** Kekalkan kesepuluh-sepuluh nilai hash tanpa perubahan, dan betulkan terminologi supaya semantik schema dan fixtur dibezakan dengan tepat. Tiada fixtur sumber dicipta.

| Perkara | v11 |
| --- | --- |
| Sepuluh nilai hash diluluskan | **Tidak berubah, bait demi bait** |
| Schema L1 | Kekal: SHA-256 bait fail sebenar |
| Fixtur L1 | Kini didokumenkan sebagai serialisasi kanonik + `\n`, bukan hash fail |
| Fixtur L2 | Serialisasi kanonik tanpa `\n` |
| Manifest | **PENDING_OWNER_CONFIRMATION**; tiada manifest berdiri sendiri dicipta |
| Identiti W1 | **NOT VERIFIED** |
| A7 | **PROPOSED — NOT AUTHORIZED FOR EXECUTION** |
| Blocker 3 | **OPEN / NOT VERIFIED** |

**Batasan `ensure_ascii`.** Kelima-lima objek yang dibina semula mengandungi sifar aksara bukan ASCII. Oleh itu nilai hash yang diluluskan **tidak dapat membezakan** `ensure_ascii=True` daripada `ensure_ascii=False`. `ensure_ascii=False` dikekalkan sebagai konvensyen dokumentasi v11, **bukan** didakwa sebagai terbukti secara sejarah oleh hash.

## 0v. Ringkasan pembetulan v10 — pengesahan tempatan dan dua kecacatan v9

v10 ialah pembetulan dokumentasi dan pengesahan tempatan sahaja. v1–v9 tidak diubah. Tiada A7, n8n, Docker, VPS, Coolify, pangkalan data, kredensial atau konfigurasi dijalankan atau diubah.

**Apa yang berlaku.** Kod sebenar `build-a7-node.py (v9)` dan `extract-and-verify.py (v9)` diekstrak daripada seksyen 6.4.2 dan 6.4.4 ke direktori sementara `/tmp`, disusun, kemudian diuji dengan harness tempatan. Harness memanggil fungsi sebenar (`parse_schema`, `parse_fixture`, `as_json`, `block_bounds`, `check_no_overlap`, `canon_sha`, `load_manifest`) dan CLI kedua-dua skrip pada data mock. Ia tidak menggantikan pengurai atau pengesah dengan versi ringkas.

**Keputusan ujian v9 (kod asal).** 130 daripada 132 kes lulus. Dua kes gagal, dan kedua-duanya ialah kecacatan sebenar:

| Kes | Dakwaan v9 | Tingkah laku sebenar v9 |
| --- | --- | --- |
| V4-extract | Medan tambahan dalam entri objek ditolak | `extract-and-verify.py` **menerima** `objects.t1.extra` (ACCEPTED) |
| V8-extract | Medan `source` hilang ditolak dengan FATAL exit 2 | `KeyError: 'source'`, traceback, exit 1 |

Punca V4 ialah pembetulan `ENTRY_KEYS` dalam v9 tidak pernah sampai ke kod: penggantian teks dalam langkah penjanaan v9 tidak sepadan dengan blok sasaran dan gagal senyap. Pada v9, hanya kompilasi sintaks dilaporkan; ujian fungsi tidak dijalankan. Kesilapan ini dibetulkan dalam v10 dan dikesan oleh ujian v10 ini.

**Keputusan ujian v10 (kod dibetulkan).** 132 daripada 132 kes lulus. Kod v10 ialah sumber yang dinyatakan dalam seksyen 6.4.2 dan 6.4.4 v10. Perincian setiap kes ada dalam laporan dalaman `internal/a7-v10-local-test-results.md`.

**Had.** Lulus harness ialah bukti bahawa kod sebenar berkelakuan seperti yang dinyatakan terhadap data mock dan nilai yang diluluskan. Ia **bukan** bukti bahawa n8n mengeksport `jsCode` dengan penanda kekal, bahawa Ajv menerima skema atau fixtur, bahawa allowlist task-runner betul, atau bahawa Blocker 3 ditutup. Tiga andaian export kekal **UNVERIFIED**. Manifest kekal **PENDING_OWNER_CONFIRMATION**.

## 0. Ringkasan pembetulan v8 — sembilan pembetulan

### 0.A Dua peringkat verifikasi, bukan satu

**Kecacatan dalam v7.** v7 mendakwa pengekstrak melakukan lima perbandingan Lapisan 1 bebas terhadap workflow yang dieksport. Itu tidak benar: workflow hanya mengandungi `SCHEMA_RAW_SHA`, bukan hash mentah untuk empat fixtur.

**Pembetulan dalam v8.** Verifikasi dipisahkan kepada dua peringkat, dan setiap dakwaan dipetakan kepada peringkat yang boleh membuktikannya.

| Dakwaan | Stage A (build-time) | Stage B (post-export) |
| --- | --- | --- |
| Fail sumber schema sepadan hash yang diluluskan | **MATCH** jika fail dibaca | **NOT APPLICABLE** — fail tidak dibaca |
| Fail sumber baseline sepadan hash | **MATCH** jika fail dibaca | **NOT VERIFIED** — workflow tidak mengandungi hash mentah |
| Fail sumber t1, t2, t3 sepadan hash | **MATCH** jika fail dibaca | **NOT VERIFIED** |
| Objek schema dalam workflow sepadan serialisasi yang diluluskan | NOT APPLICABLE | **MATCH** |
| Objek fixtur dalam workflow sepadan serialisasi | NOT APPLICABLE | **MATCH** |
| `SCHEMA_RAW_SHA` dalam workflow sepadan hash fail sebenar | NOT APPLICABLE | **MATCH** (deklarasi lawan manifest) |
| Pemalar dalam workflow konsisten dengan datanya | NOT APPLICABLE | **MATCH** (konsistensi dalaman sahaja) |

Status yang digunakan: `MATCH`, `MISMATCH`, `NOT VERIFIED`, `NOT APPLICABLE`. `MATCH` hanya digunakan apabila perbandingan benar-benar dijalankan.

### 0.B Kiraan perbandingan yang betul

v7 mendakwa "sepuluh perbandingan" tetapi pengekstrak sebenarnya melakukan sebelas. v8 memisahkan kiraan mengikut peringkat.

**Stage A — build-time (5 perbandingan):**

| # | Perbandingan | Sumber jangkaan | Sumber sebenar |
| --- | --- | --- | --- |
| 1 | Schema raw | Manifest L1 | `sha256(bytes fail)` |
| 2 | Baseline raw | Manifest L1 | `sha256(bytes fail)` |
| 3 | t1 raw | Manifest L1 | `sha256(bytes fail)` |
| 4 | t2 raw | Manifest L1 | `sha256(bytes fail)` |
| 5 | t3 raw | Manifest L1 | `sha256(bytes fail)` |

**Stage B — post-export (11 perbandingan):**

| # | Kategori | Bilangan |
| --- | --- | --- |
| 1 | Lapisan 2 kanonik bebas (manifest) | 5 |
| 2 | Lapisan 1 schema (deklarasi lawan manifest) | 1 |
| 3 | Pemalar workflow lawan nilai dikira semula | 5 |
| | **Jumlah Stage B** | **11** |

**Jumlah keseluruhan: 16 perbandingan** (5 build-time + 11 post-export).

v7 menyebut "sepuluh" dan menggabungkan kategori. v8 tidak menggabungkan.

### 0.C Kod mati `declared` dibuang

**Kecacatan dalam v7.** Pengekstrak membina set `declared` di baris 239–243 dan **tidak pernah membacanya semula**. Komen mendakwa ia "menolak manifest yang bersumber daripada workflow", tetapi tiada kod yang berbuat demikian.

**Pembetulan dalam v8.** Set itu dibuang sepenuhnya. Tiada dakwaan bahawa kod boleh mewujudkan provenance manifest melalui perbandingan nilai atau laluan fail.

**Penjelasan yang betul:** independence diwujudkan melalui **kustodi, kelulusan dan provenance yang berasingan**, bukan melalui ketidaksamaan hash. Manifest yang sah **akan** mempunyai nilai yang sama dengan pemalar workflow — itu dijangka, bukan tanda masalah. Jika kod menolak manifest kerana nilainya sepadan, ia akan menolak manifest yang betul.

### 0.D Pengesahan manifest yang ketat

v8 menolak, dengan exit 2 dan mesej eksplisit:

| # | Keadaan | Mesej |
| --- | --- | --- |
| 1 | Fail tiada | `FATAL manifest missing` |
| 2 | Bukan JSON | `FATAL manifest is not valid JSON` |
| 3 | `manifest_version` bukan 1 | `FATAL manifest_version must be 1` |
| 4 | `canonicalization` hilang medan | `FATAL manifest.canonicalization missing <k>` |
| 5 | `convention` salah | `FATAL unexpected canonicalization.convention` |
| 6 | `encoding` salah | `FATAL unexpected canonicalization.encoding` |
| 7 | `trailing_newline` bukan `false` | `FATAL canonicalization.trailing_newline must be false` |
| 8 | Objek hilang | `FATAL manifest missing objects: [...]` |
| 9 | Objek tambahan | `FATAL manifest has unexpected objects: [...]` |
| 10 | Medan entri hilang | `FATAL manifest.objects.<n> missing <k>` |
| 11 | Hash bukan 64 hex | `FATAL ... is not 64 lowercase hex` |
| 12 | Penanda salah | `FATAL manifest.objects.<n>.marker must be <m>` |
| 13 | Hash duplikat | `FATAL manifest duplicate <layer> for ...` |

**Diuji dalam sesi ini:** 12 daripada 13 laluan dijalankan dan menolak dengan betul (lihat 6.4.3d).

### 0.E Nilai hash tidak berubah

Kesepuluh nilai dalam manifest kekal sama seperti v7. Tiada satu pun diubah untuk membuat ujian lulus.

### 0.F Pemetaan penanda dikuatkuasakan

Manifest mesti mempunyai penanda tepat:

| Objek | Penanda wajib |
| --- | --- |
| `schema` | `SCHEMA` |
| `baseline` | `FIXTURE_baseline` |
| `t1` | `FIXTURE_t1` |
| `t2` | `FIXTURE_t2` |
| `t3` | `FIXTURE_t3` |

Pengekstrak juga menguatkuasakan: setiap BEGIN tepat sekali, setiap END tepat sekali, susunan betul, tiada pertindihan, semua blok dihurai.

**Amaran skop:** integriti penanda membuktikan kandungan bertanda diekstrak secara **tidak kabur**. Ia **tidak** membuktikan workflow berasal daripada sumber yang dipercayai.

### 0.G Ujian pemalsuan bersepadu dikekalkan

Kedua-dua ujian v7 dikekalkan dan dijalankan semula terhadap v8. Hasilnya sama: `RESULT FAILED`, exit 1.

### 0.H Provenance tidak dinaik taraf

Manifest kekal `PENDING_OWNER_CONFIRMATION`. Menyimpan nilai dalam fail JSON luaran **tidak** mewujudkan provenance bebas — ia hanya memindahkan kebergantungan.

### 0.I Model hasil eksplisit

| Peringkat | Label |
| --- | --- |
| Build-time | `SOURCE_RAW_MATCH`, `SOURCE_RAW_MISMATCH`, `SOURCE_RAW_NOT_VERIFIED` |
| Post-export Lapisan 2 | `LAYER2_CANONICAL_MATCH`, `LAYER2_CANONICAL_MISMATCH` |
| Post-export Lapisan 1 schema | `LAYER1_SCHEMA_MATCH`, `LAYER1_SCHEMA_MISMATCH`, `LAYER1_SCHEMA_NOT_VERIFIED` |
| Post-export konsistensi dalaman | `WORKFLOW_DECLARED_HASH_MATCH`, `WORKFLOW_DECLARED_HASH_MISMATCH` |
| Keseluruhan | `RESULT VERIFIED`, `RESULT FAILED`, `RESULT NOT VERIFIED` |

`RESULT VERIFIED` memerlukan **kedua-dua** Stage A dan Stage B lengkap dan lulus. Jika Stage A tidak dijalankan dalam sesi yang sama, hasil Stage B sahaja tidak boleh menjadi `RESULT VERIFIED` untuk rantaian penuh.



### 0.1 Kecacatan dalam v6

Pengekstrak v6 membandingkan hash yang dikira semula dengan pemalar hash yang tertanam dalam **workflow yang sama** yang sedang diperiksa. Ia membuktikan konsistensi dalaman sahaja.

**Dibuktikan dalam sesi ini.** Saya ubah satu nilai dalam literal schema, kemudian tukar pemalar `SCHEMA_CANON_SHA` kepada hash data baharu. Pengekstrak v6 melaporkan:

```text
SCHEMA  recomputed=90b905b7c0036067 declared=90b905b7c0036067 self_consistent=True
RESULT SELF_CONSISTENT
```

Pemalsuan bersepadu **lulus** v6. Ini kecacatan sebenar, bukan teori.

### 0.2 Pembetulan: manifest bebas

v7 memerlukan **manifest hash jangkaan luaran** yang tidak pernah dibaca daripada workflow yang diperiksa.

| Perkara | v6 | v7 |
| --- | --- | --- |
| Sumber nilai jangkaan | Pemalar dalam workflow | Manifest luaran |
| Perbandingan bebas | Tiada | 5 lapisan-2 + 1 lapisan-1 |
| Pemalsuan bersepadu | **Lulus** | **Gagal** |
| Hasil keseluruhan | `SELF_CONSISTENT` | `RESULT VERIFIED` / `RESULT FAILED` |
| Argumen | `--expect-schema-raw`, `--expect-file` (tidak digunakan) | `--expected-manifest` (wajib) |

**Diuji dalam sesi ini.** Serangan yang sama terhadap v7:

```text
LAYER2_CANONICAL_MISMATCH baseline expected=84a1f232... actual=91955e0c...
WORKFLOW_DECLARED_HASH_MATCH baseline
RESULT FAILED reasons=['layer2:baseline']
```

Perhatikan dua baris pertama bersama: konsistensi dalaman **lulus** (`WORKFLOW_DECLARED_HASH_MATCH`), tetapi perbandingan bebas **menangkap** pemalsuan. Itu tepat jurang yang v6 tidak tutup.

### 0.3 Tiga pembetulan lain

| # | Perkara | Rujukan |
| --- | --- | --- |
| B | Pengekstrak membandingkan terhadap manifest bebas, 10 perbandingan berasingan | 7.4.4b |
| C | Hasil eksplisit: `LAYER1_RAW_MATCH`, `LAYER2_CANONICAL_MATCH`, `WORKFLOW_DECLARED_HASH_MATCH`, `RESULT VERIFIED`/`FAILED` | 7.4.4c |
| D | Ujian negatif pemalsuan bersepadu, wajib | 7.4.4g |
| E | Provenance dan batasan manifest | 7.5 |



### 0.1 Penanda pengekstrakan tidak pernah dijana

**Kecacatan dalam v5.** Prosedur 7.4.4 memerlukan penanda `/* A7:LITERAL:<NAME>:START */` untuk mengekstrak literal daripada workflow yang dieksport. Tetapi templat penjana v5 **tidak mengeluarkan penanda itu**. Ia hanya keluar `const SCHEMA_OBJECT = ...` tanpa sebarang penanda.

Kesan: prosedur pengekstrakan v5 **tidak boleh dilaksanakan**. Ia merujuk penanda yang tidak wujud dalam output.

**Pembetulan dalam v6.** Templat kini mengeluarkan penanda sebenar, dan penjana mengesahkannya secara struktur.

Format penanda yang tepat:

```text
/* A7:LITERAL:<NAME>:BEGIN */
<literal>
/* A7:LITERAL:<NAME>:END */
```

`<NAME>` ialah salah satu daripada lima, dalam susunan tetap:

| # | Nama penanda | Kandungan |
| --- | --- | --- |
| 1 | `SCHEMA` | Objek `SCHEMA_OBJECT` |
| 2 | `FIXTURE_baseline` | Entri fixtur baseline |
| 3 | `FIXTURE_t1` | Entri fixtur A3-T1 |
| 4 | `FIXTURE_t2` | Entri fixtur A3-T2 |
| 5 | `FIXTURE_t3` | Entri fixtur A3-T3 |

**Pengesahan struktur oleh penjana** (`check_markers`, exit 5 jika gagal):

1. Setiap `BEGIN` muncul **tepat sekali**.
2. Setiap `END` muncul **tepat sekali**.
3. `END` sentiasa selepas `BEGIN` untuk penanda yang sama.
4. Susunan kedudukan `BEGIN` mengikut `MARKER_NAMES`.
5. Tiada blok mengandungi penanda blok lain (tiada pertindihan).

**Pengesahan oleh pengekstrak** (`extract_block`, exit 1 jika gagal):

1. Kiraan `BEGIN` dan `END` mesti 1 setiap satu.
2. `END` selepas `BEGIN`.
3. Blok mesti mengandungi literal JSON yang sah selepas penyingkiran pembalut.

**Diuji dalam sesi ini:**

| Ujian | Keputusan |
| --- | --- |
| Penjanaan berjaya | `MARKERS_OK 5`, `SENTINELS_REMAINING 0`, 23,408 bait, `node --check` lulus |
| Penanda hilang | Pengekstrak gagal: `count BEGIN=1 END=0` |
| Penanda berganda | Pengekstrak gagal: `count BEGIN=2 END=1` |
| Literal diubah | `self_consistent=False`, `RESULT SELF_INCONSISTENT` |

### 0.2 Dua lapisan hash dicampur

**Kecacatan dalam v5.** v5 menyenaraikan:

- hash bait fail schema `0c184dce…`
- hash JSON kanonik schema `ce403cb9…`

Tetapi untuk fixtur, v5 hanya mempunyai hash **bait fail** (`68a816c8…` dan seterusnya). Ia tidak mempunyai hash JSON kanonik untuk fixtur. Prosedur pasca-larian kemudian membandingkan hash kanonik yang dikira semula terhadap hash bait fail — perbandingan yang **tidak boleh sepadan**.

**Pembetulan dalam v6.** Dua lapisan dipisahkan dengan jelas, dan **kedua-dua nilai untuk kelima-lima objek** dikira dan direkod.

**Lapisan 1 — hash bait fail asal:**

| Objek | Bait | SHA-256 |
| --- | --- | --- |
| schema | 3,566 | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` |
| baseline | 3,257 | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` |
| t1 | 3,293 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` |
| t2 | 3,256 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` |
| t3 | 3,243 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` |

**Lapisan 2 — hash JSON kanonik** (`json.dumps(obj, indent=2, ensure_ascii=False)`, **tanpa** baris baru akhir):

| Objek | Bait | SHA-256 |
| --- | --- | --- |
| schema | 4,713 | `ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51` |
| baseline | 3,256 | `84a1f232d513e41eb59c1c34ed73743bae26f4be2822b059ff8a08e40cce029d` |
| t1 | 3,292 | `9fc9ba234d3123a5ae3ba8c2fae87677ded42d9ff84c310258d7359762b1afb3` |
| t2 | 3,255 | `6f6a7cbd4afa004a8bb946bf01e91fa3c637b55606ffb85fe347d7b2b6ab91c9` |
| t3 | 3,242 | `e0fbc1bcc5779cd01da17b2f9fae57e714e521db7a7a17fbe4e96573a471a54c` |

**Nota penting tentang konvensyen baris baru.** Lapisan 2 **tidak** termasuk baris baru akhir. Jika baris baru ditambah, hash berubah:

| Bentuk | SHA-256 |
| --- | --- |
| schema canon tanpa `\n` | `ce403cb9…` |
| schema canon dengan `\n` | `e6e8dea871ddf2712becb154151630b068436be4f2e898afb2f50bbb880bca2b` |

Ini sebabnya konvensyen mesti ditetapkan dan digunakan secara konsisten.

**Apa yang dibuktikan setiap lapisan:**

| Lapisan | Membuktikan | **Tidak** membuktikan |
| --- | --- | --- |
| L1 bait fail | Fail di cakera tidak berubah daripada yang diluluskan | Bahawa nod menggunakan data itu |
| L2 JSON kanonik | Objek yang diekstrak daripada workflow sepadan dengan objek yang dijana | Bahawa fail asal tidak berubah |

Kedua-duanya diperlukan. Ia menjawab soalan berbeza.

### 0.3 Percanggahan kaedah eksport

**Kecacatan dalam v5.** v5 memilih `--output=-` ke stdout sebagai kaedah utama, tetapi dalam seksyen yang sama menawarkan eksport ke fail dalam container + `docker cp` sebagai "alternatif". Ia tidak menyatakan bila hendak guna yang mana, dan senarai semak tidak konsisten.

**Pembetulan dalam v6.** Satu kaedah kanonik sahaja, digunakan di seluruh dokumen:

**Kaedah kanonik: `--output=-` ke stdout, ditangkap oleh hos.**

```bash
docker exec a7-probe n8n export:workflow --id=<ID> --output=- > /tmp/a7-export.json
```

Kaedah kedua **dilabel NON-CANONICAL / NOT AUTHORIZED** dan hanya boleh digunakan dengan kelulusan pemilik berasingan. Lihat 7.4.4c.

**Asumsi yang kekal UNVERIFIED:**

| # | Asumsi | Status |
| --- | --- | --- |
| 1 | `--output=-` bermaksud stdout dalam n8n 2.43.8 | **UNVERIFIED** — flag wujud, tingkah laku `-` tidak diuji |
| 2 | Struktur eksport mengandungi `nodes[].parameters.jsCode` | **UNVERIFIED** untuk 2.43.8 |
| 3 | Penanda komen kekal melalui import dan eksport | **UNVERIFIED** |

Kaedah eksport **tidak** disahkan secara eksperimen. Ia cadangan berdasarkan kewujudan flag, bukan tingkah laku yang diperhatikan.



### A. Semakan placeholder yang berkesan

**Kecacatan dalam v4.** Penjana v4 menyemak `node.count('{{')` selepas `str.format()`. Semakan itu tidak bermakna, kerana `str.format()` sudah menukar `{{` kepada `{` dalam output. Ia sentiasa melaporkan `0`.

Dibuktikan dalam sesi ini:

```text
template : 'const A = {{ x: 1 }};\nconst B = @@MARK@@;'
output   : 'const A = { x: 1 };\nconst B = @@MARK@@;'
count('{{') pada output: 0
```

Marker yang tidak selesai (`@@MARK@@`) masih ada, tetapi semakan melaporkan `0`. Semakan itu tidak pernah boleh gagal.

**Pembetulan dalam v5.** Penjana v5 menggunakan **token sentinel eksplisit** `__A7_SLOT_<NAME>__` dan penggantian literal, bukan `str.format()`. Selepas penggantian ia mengesahkan:

| Semakan | Kod |
| --- | --- |
| Tiada sentinel tinggal | `SENTINEL_RE.findall(out)` mesti kosong |
| Tiada serpihan separa | `"__A7_SLOT" in out` mesti palsu |
| Setiap slot diganti | gelung atas `SLOTS` |
| Slot wujud dalam templat | `token not in out` → exit 4 |

Jika mana-mana gagal, penjana **keluar dengan kod 4** dan **tidak menulis fail output**.

**Diuji dalam sesi ini — semua empat laluan keluar:**

| Ujian | Input | Exit | Fail output |
| --- | --- | --- | --- |
| Berjaya | semua sah | **0** | dicipta, `SENTINELS_REMAINING 0` |
| Slot hilang daripada templat | templat diubah | **4** | tidak dicipta |
| Sentinel tinggal dalam nilai slot | slot diisi sentinel | **4** | tidak dicipta |
| Hash tidak sepadan | fail diubah | **2** | tidak dicipta |
| Input tiada | laluan salah | **3** | tidak dicipta |

Output berjaya yang mewakili:

```text
VERIFY schema    0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21 MATCH
VERIFY baseline  68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a MATCH
VERIFY t1        52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3 MATCH
VERIFY t2        45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011 MATCH
VERIFY t3        0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051 MATCH
WROTE /tmp/a7-v5.js
OUTPUT_BYTES 22536
OUTPUT_SHA 0a279ade0a348ecfbd24f195c47c2a01dce45adc44f769e5fa037ddad4734f58
SENTINELS_REMAINING 0
SLOTS_SUBSTITUTED 3
```

`node --check` pada output itu: **lulus**.

### B. Pembetulan dakwaan bukti serialisasi

**Kecacatan dalam v4.** v4 menyebut "kelima-lima objek" tetapi jadual buktinya hanya memaparkan empat fixtur. Objek schema tidak ditunjukkan.

**Pembetulan dalam v5.** Bukti untuk kelima-lima objek kini disediakan, termasuk schema. Diuji dalam sesi ini:

| Objek | Panjang JSON inden-2 | Python SHA-256 | Node reserialize identik |
| --- | --- | --- | --- |
| **schema** | 4,713 | `ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51` | **ya** |
| baseline | 3,257 | `68a816c8…ca4a` | **ya** |
| t1 | 3,293 | `52b80727…43f3` | **ya** |
| t2 | 3,256 | `45379ba2…8011` | **ya** |
| t3 | 3,243 | `09488320…3051` | **ya** |

Kelima-lima: `JSON.stringify(obj, null, 2)` dalam Node menghasilkan output **identik bait demi bait** dengan Python `json.dumps(obj, indent=2, ensure_ascii=False)`.

**Pemisahan yang mesti dikekalkan:**

| Jenis bukti | Apa yang dibuktikan |
| --- | --- |
| Ujian serialisasi | Dua bahasa menghasilkan JSON yang sama untuk objek ini |
| Validasi Ajv sebenar | **TIDAK diuji.** Tiada Ajv pada mesin ini. |

Bukti serialisasi **bukan** bukti bahawa Ajv menerima atau menolak fixtur.

### C. Prosedur eksport dan hash yang boleh dilaksanakan

**Kecacatan dalam v4.** Dua kaedah bercanggah: 7.4.4 menulis ke `/tmp/a7-export.json` **di dalam container**, manakala senarai semak menggunakan `--output=-`. Tiada langkah pemindahan ke hos, dan pengekstrakan literal tidak diperincikan.

**Pembetulan dalam v5.** Satu kaedah dipilih: **`--output=-` ke stdout**, ditangkap oleh hos. Tiada fail dicipta dalam container, jadi tiada langkah pemindahan fail. Prosedur penuh dalam 7.4.4.

### D. Rujukan seksyen dan arahan restart

**Kecacatan dalam v4.** Senarai semak merujuk `7.2.1`, `7.2.2`, `7.2.4` yang merupakan nombor seksyen lama selepas v4 menyusun semula kepada 7.3/7.4.

**Pembetulan dalam v5.** Semua rujukan disemak terhadap heading sebenar v5. Arahan restart juga diselaraskan: kerana container dicipta **dengan** pemboleh ubah sudah ditetapkan, tiada restart diperlukan. Lihat 8.1.1.



### 0.1 Isu 1 — SHA-256 yang tidak sepadan

Pengiraan sebenar dalam sesi ini:

```text
$ sha256sum vividworks-f1-a7-isolated-n8n-runtime-test-plan-v3.md
4017a3a4989079b30d4ac0a42aa7724ab4a5f3747b62ae2274bb87efcd03cdb5
```

| Perkara | Nilai |
| --- | --- |
| Hash dilaporkan dalam respons sebelumnya | `4017a3a4989079b30d4ac0aa7724ab4a5f3747b62ae2274bb87efcd03cdb5` |
| Hash sebenar fail | `4017a3a4989079b30d4ac0a42aa7724ab4a5f3747b62ae2274bb87efcd03cdb5` |
| Panjang dilaporkan | **61** aksara |
| Panjang sebenar | **64** aksara |

**Punca:** hash dilaporkan **terpotong dua aksara**. Empat aksara `42` hilang pada kedudukan 23.

```text
dilaporkan: 4017a3a4989079b30d4ac0aa7724ab4...
sebenar   : 4017a3a4989079b30d4ac0a42aa7724ab4...
                                ^^ hilang
```

Hash sebenar v3 ialah `4017a3a4989079b30d4ac0a42aa7724ab4a5f3747b62ae2274bb87efcd03cdb5`, 64 aksara, 53,124 bait, 939 baris. Nilai dalam respons sebelumnya tidak sah dan tidak boleh digunakan sebagai rujukan.

**Semua hash v1–v3 disahkan semula dalam sesi ini:**

| Fail | SHA-256 sebenar | Bait | Baris |
| --- | --- | --- | --- |
| v1 | `9a06ad719d636ce60c4cdd35b0559e6c82e9a687c7deb448824daa0d202dfaab` | 21,270 | 393 |
| v2 | `b9f653b0db1c9d0b5831be3444e9a7f53aef6fd7ad4f7486cffde7b2bb5b7235` | 37,928 | 659 |
| v3 | `4017a3a4989079b30d4ac0a42aa7724ab4a5f3747b62ae2274bb87efcd03cdb5` | 53,124 | 939 |

### 0.2 Isu 2 — kod 6.4 tidak lengkap

v3 masih mengandungi `{ /* ... */ }` dalam `SCHEMA_OBJECT` dan `FIXTURES`. Kod itu tidak boleh dilaksanakan.

v4 menggantikannya dengan **penjana mekanikal**: satu skrip Python yang membaca fail yang disahkan, mengesahkan setiap hash, dan mengeluarkan nod JavaScript lengkap tanpa placeholder. Skrip dan templat disertakan penuh dalam seksyen 6.4. Penjana telah **diuji dalam sesi ini**: ia menghasilkan nod 22,529 bait dengan `PLACEHOLDERS_LEFT 0`, dan `node --check` lulus.

### 0.3 Isu 3 — kaedah pembersihan bercanggah

v3 menyebut pemadaman workflow melalui UI/API dalam seksyen 5.3.4 dan pemadaman container dalam 11.1. v4 menggunakan **satu** kaedah sahaja: padam container ujian dan volume SQLite khususnya. UI/API tidak digunakan untuk apa-apa. Urutan tepat dan pengesahan nama volume sebelum pemadaman dinyatakan dalam 11.1.

### 0.4 Isu 4 — nombor langkah probe import

v3 menyatakan "berhenti selepas langkah 6" tetapi langkah 6 ialah **import workflow**, bukan pelaksanaan Code node. Probe `require()` hanya berlaku apabila Code node **dijalankan**. v4 memisahkan kedua-duanya dan membetulkan semua rujukan silang dalam 10.2 dan 10.3.

### 0.5 Isu 5 — kaedah pengesahan hash

v3 mencetak `fx.sha` dan mendakwa ia bukti. v4 membezakan **hash bait fail asal** daripada **hash representasi JSON yang dikanonkan**, dan membuktikan kedua-duanya menggunakan kaedah serialisasi yang sama.

Bukti yang diperoleh dalam sesi ini: `JSON.stringify(obj, null, 2)` dalam JavaScript menghasilkan output **identik bait demi bait** dengan Python `json.dumps(obj, indent=2, ensure_ascii=False)` untuk kelima-lima objek (schema dan empat fixtur). Ini **VERIFIED**, bukan diandaikan. Butiran dalam 7.3.

## 0a. Ringkasan pembetulan v3 (dikekalkan)

| # | Pembetulan | Kesan |
| --- | --- | --- |
| 1 | `--network none` lawan operasi UI/API | Diselesaikan dengan laluan CLI dalam container |
| 2 | Mekanisme allowlist runner | **VERIFIED** daripada `task-runner-process-js.js`: diwarisi daripada `process.env` induk dalam mod `internal` |
| 3 | Pengenal kod tidak ditentukan | Diganti (tetapi masih tidak lengkap — lihat Isu 2) |
| 4 | Hash fixtur tidak dibuktikan | Dipisahkan kepada peringkat bukti |

## 0b. Ringkasan pembetulan v2 (dikekalkan)

| # | Pembetulan | Kesan |
| --- | --- | --- |
| 1 | `require('fs')` dibuang | `NODE_FUNCTION_ALLOW_BUILTIN` **NOT REQUIRED** |
| 2 | Peringkat dipisahkan | Kejayaan satu import bukan bukti ketiga-tiga |
| 3 | Arkitektur task runner | PID 1/7/19 didokumenkan |
| 4 | Isolasi rangkaian | `--network none`, batas diakui |

Satu andaian v1 diuji dan **ditolak**: `NODE_PATH` proses runner tidak menyelesaikan `require('ajv')`.

SD-01 **ACCEPTED** · SD-02 **DEFERRED** · Blocker 3 **OPEN / NOT VERIFIED** · M0–M5 **NOT AUTHORIZED**

## 1. Objektif dan skop

### 1.1 Objektif

Menentukan sama ada Ajv 8.20.0 boleh **diimport dan digunakan dari nod Code n8n yang sebenar**, dan sama ada ia mengesahkan schema SiteSpec v1.1 kanonik serta fixtur yang diluluskan dengan betul.

### 1.2 Apa yang A7 buat

Pelan dan pemeriksaan sahaja. Ia menetapkan persekitaran terasing, spesifikasi ujian, keperluan konfigurasi, dan senarai semak pelaksanaan.

### 1.3 Apa yang A7 **tidak** buat

Tiada ujian dijalankan. Tiada workflow dicipta. Tiada persekitaran diubah. Tiada pakej dipasang. Lihat seksyen 12.

## 2. Evidence inventory

### 2.1 Dokumen yang digunakan

| Dokumen | Versi | SHA-256 | Saiz |
| --- | --- | --- | --- |
| Consolidated status register | v1 | `b960fef6b8a36ac9b5206ed055e656eceff0d7e301a899ce4deca01da41f80e4` | 22,582 |
| Mock-only planning | **v1.2.11** | `f89bd614761f899299b07f5b9b2ef4fab0cd49ca3fb244191bfbec997a8186be` | 125,850 |
| Design proposal | **v1.1.2** | `6cc042c41fb8ab631ca13bdeae28d2fff03f4d18dc231f7f52b93ebeaa86b883` | 40,684 |
| Canonical schema | v1.1 | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` | 3,566 |
| Dialect and validator audit | **v1.1** | `46c8662d79bd0c898258eaac8f4edad4b84cb77e0993b618d83fbd22cd2d8f8b` | 16,301 |
| A4 n8n inspection | **v2** | `d1f2567d3854f6ca59dfc4f459842ef98b9e61d68aa7dd17a2339885b624cf4d` | 12,544 |
| A6 Ajv verification | v1 | `7ed4154dcba976e21e823c064c003d1c38c0f21000e0f1aab9d5c77a0a5cee6e` | 12,700 |
| Two-server technical record | **v1.4 (.md)** | `649ee078ab7f694a232ce13775baf1ddf7c39d5306730d91c422a19bdba32268` | 14,513 |
| A3 negative test results | v1 | `6972cc64c1b6e045efba0829c3a32a8709cdf544774663b09dc132c12caac497` | 8,725 |

Semua dokumen wujud. Tiada satu pun direkonstruksi daripada ingatan.

### 2.2 Nota versi

Ketiga-tiga versi terkini digunakan. Versi lapuk (`planning v1.2.1`, `proposal v1.1.1`, `audit.md` tanpa `-v1.1`) tidak digunakan. `Rekod_Teknikal...v1.4.pdf` tidak wujud; yang ada ialah `vividworks-server-technical-record-v1.4.md` dan PDF operator v1.3.

## 3. Fakta runtime semasa

Semua **VERIFIED** melalui pemeriksaan langsung dalam sesi ini atau sesi A4/A6.

| Fakta | Nilai | Sumber | Status |
| --- | --- | --- | --- |
| Hos n8n | `vps-c05526e1`, `51.79.221.111` | SSH | VERIFIED |
| Container produksi | `n8n-n8n-1`, ID `ddd40cc3ff5b` | `docker ps` | VERIFIED |
| Imej | `docker.n8n.io/n8nio/n8n:latest` | `docker inspect` | VERIFIED |
| **Image digest** | `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c` | `docker image inspect` | VERIFIED |
| Saiz imej | 1.8 GB | `docker images` | VERIFIED |
| n8n | `2.43.8` | `n8n --version` dalam container | VERIFIED |
| Node.js | `v26.7.0` | `node -v` | VERIFIED |
| Ajv | `8.20.0`, satu versi sahaja | `package.json` melalui laluan penuh | VERIFIED |
| Laluan Ajv | `/usr/local/lib/node_modules/n8n/node_modules/.pnpm/ajv@8.20.0/node_modules/ajv` | `require.resolve` | VERIFIED |
| `require('ajv')` | **GAGAL** `MODULE_NOT_FOUND` | A4, disahkan semula A6 | VERIFIED |
| Symlink `ajv` di `n8n/node_modules` | **TIADA** | A4 | VERIFIED |
| `/etc/n8n-task-runners.json` | **TIDAK WUJUD** | A4 | VERIFIED |
| `NODE_FUNCTION_ALLOW_EXTERNAL` | **TIDAK DITETAPKAN** | A4 | VERIFIED |
| `NODE_FUNCTION_ALLOW_BUILTIN` | **TIDAK DITETAPKAN** | A4 | VERIFIED |
| `N8N_RUNNERS_ENABLED` / `MODE` | Tidak ditetapkan | A4 | VERIFIED |
| Task runner | Proses berasingan, PID 19 | `ps` dalam container | VERIFIED |
| 3 konstruktor Ajv | `Ajv`, `Ajv2019`, `Ajv2020` berfungsi melalui laluan mutlak | A6 | VERIFIED |
| Setiap konstruktor | Menerima **satu** URI dialek sahaja | A6 | VERIFIED |
| Fail kanonik compile | Berjaya dalam ketiga-tiga konstruktor | A6 | VERIFIED |

### 3.1 Sumber hos — relevan kepada pilihan isolasi

| Hos | vCPU | RAM total | RAM tersedia | Disk bebas | Container aktif | Imej n8n |
| --- | --- | --- | --- | --- | --- | --- |
| n8n produksi (`51.79.221.111`) | **2** | 3,814 MB | **2,458 MB** | 30 GB | 1 | **ADA**, 1.8 GB |
| Coolify (`51.79.159.241`) | 4 | 7,751 MB | 6,228 MB | 61 GB | 10 | **TIADA** |

Ini VERIFIED melalui `nproc`, `free -m`, `df -h`, `docker ps` dan `docker images`.

Fakta ini penting: hos produksi hanya mempunyai 2 vCPU dan kira-kira 2.4 GB RAM tersedia. Container n8n kedua di situ akan bersaing dengan container produksi untuk CPU dan I/O.

## 4. Perbandingan pilihan isolasi

### 4.1 Pilihan A — container n8n sekali guna pada hos produksi

| Kriteria | Penilaian |
| --- | --- |
| Kekuatan isolasi | **Lemah.** Kongsi kernel, CPU, I/O dan daemon Docker dengan produksi. |
| Risiko hos dikongsi | **Tinggi.** Hos hanya 2 vCPU; container ujian yang menjalankan `npm install` atau kompilasi boleh menjejaskan kependaman produksi. Daemon Docker dikongsi: satu kesilapan `docker` boleh menyentuh container produksi. |
| Kesetiaan versi | **Tinggi.** Imej dan digest yang sama sudah ada di hos. |
| Ketersediaan modul | Sama seperti produksi: Ajv di `.pnpm`, tiada symlink. |
| Sumber diperlukan | ~1.8 GB imej (sudah ada), ~400–800 MB RAM semasa ujian. |
| Pembersihan | Mudah: `docker rm -f`. |
| Kebolehulangan | Tinggi, kerana imej setempat. |

### 4.2 Pilihan B — instans n8n sekali guna pada hos bukan produksi

| Kriteria | Penilaian |
| --- | --- |
| Kekuatan isolasi | **Kuat.** Hos berasingan, daemon Docker berasingan, tiada laluan ke produksi. |
| Risiko hos dikongsi | **Rendah.** Hos Coolify mempunyai 4 vCPU dan 6.2 GB tersedia. Tetapi ia menjalankan 10 container, termasuk staging dan Coolify sendiri. |
| Kesetiaan versi | **Sederhana.** Imej n8n **tiada** di hos itu. Ia perlu ditarik, dan `latest` boleh memberi digest berbeza daripada produksi. |
| Ketersediaan modul | Sama jika digest yang sama digunakan. |
| Sumber diperlukan | ~1.8 GB muat turun + ~400–800 MB RAM. |
| Pembersihan | Mudah, tetapi imej 1.8 GB kekal dalam cache melainkan dipadam. |
| Kebolehulangan | Sederhana, bergantung pada penarikan imej. |

### 4.3 Pilihan C — persekitaran sekali guna dengan digest dan konfigurasi yang dipin

| Kriteria | Penilaian |
| --- | --- |
| Kekuatan isolasi | Bergantung pada hos. Jika di hos Coolify: **kuat**. |
| Risiko hos dikongsi | Rendah jika di Coolify; tinggi jika di hos produksi. |
| Kesetiaan versi | **Tertinggi.** Digest `sha256:a18edcf0…` dipin secara eksplisit, jadi tiada risiko `latest` bergerak. |
| Ketersediaan modul | Sama seperti produksi. |
| Sumber diperlukan | Sama seperti B, ditambah keperluan memindahkan imej 1.8 GB jika tiada rangkaian. |
| Pembersihan | Sama seperti B. |
| Kebolehulangan | **Tertinggi**, kerana digest dipin. |

### 4.4 Perbandingan ringkas

| Kriteria | A | B | C |
| --- | --- | --- | --- |
| Isolasi | Lemah | Kuat | Kuat |
| Risiko kepada produksi | **Tinggi** | Rendah | Rendah |
| Kesetiaan versi | Tinggi | Sederhana | **Tertinggi** |
| Sumber hos mencukupi | **Tidak** (2 vCPU, 2.4 GB) | Ya | Ya |
| Perlu muat turun 1.8 GB | Tidak | Ya | Ya |
| Kebolehulangan | Tinggi | Sederhana | **Tertinggi** |

## 5. Persekitaran terasing yang disyorkan

**Cadangan: Pilihan C pada hos Coolify `51.79.159.241`.** Ini **PROPOSED**, bukan dilaksanakan.

Sebab:

1. **Hos produksi tidak sesuai.** 2 vCPU dan 2.4 GB RAM tersedia tidak mencukupi untuk ujian yang melibatkan `npm` dan container tambahan tanpa risiko kepada produksi.
2. **Digest dipin menghapuskan risiko `latest`.** Imej n8n di hos Coolify mesti ditarik dengan digest `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c`, bukan `latest`. Ini menjamin versi yang diuji sama dengan produksi.
3. **Tiada laluan rangkaian ke produksi.** Hos Coolify dan hos n8n berasingan.
4. **Sumber mencukupi.** 4 vCPU, 6.2 GB tersedia.

### 5.1 Had yang mesti diakui

Persekitaran ini **bukan** produksi, dan ia tidak boleh menjadi bukti bahawa produksi berkelakuan sama. Ia membuktikan bahawa **imej yang sama**, dengan **versi n8n, Node dan Ajv yang sama**, boleh mengimport Ajv dari Code node apabila allowlist dikonfigurasikan.

Perbezaan yang tinggal antara ujian dan produksi:

| Perkara | Ujian | Produksi |
| --- | --- | --- |
| Hos | Coolify, 4 vCPU | n8n, 2 vCPU |
| Persekitaran | Baharu, konfigurasi minimum | Berjalan 3 minggu, konfigurasi semasa |
| `NODE_FUNCTION_ALLOW_EXTERNAL` | Akan ditetapkan | Tidak ditetapkan |
| Volume n8n | Baharu, kosong | Data produksi |

Kesan perbezaan `NODE_FUNCTION_ALLOW_EXTERNAL`: ujian membuktikan bahawa **apabila allowlist ditetapkan**, Ajv boleh diimport. Ia tidak membuktikan bahawa produksi boleh, kerana produksi masih tidak menetapkannya. Itu keputusan pemilik yang berasingan.

### 5.2 Alternatif jika hos Coolify tidak dibenarkan

Jika pemilik tidak membenarkan penggunaan hos Coolify, pilihan seterusnya ialah **Pilihan A dengan sekatan ketat**: container ujian pada hos produksi dengan had CPU dan memori eksplisit (`--cpus=0.5 --memory=512m`), dijalankan di luar waktu puncak, dan dipadam sejurus selepas. Ini **lebih berisiko** dan memerlukan kelulusan pemilik yang eksplisit kerana ia menyentuh hos produksi.

## 5.3 Isolasi rangkaian dan operasi workflow

v2 mencadangkan `--network none` **dan** pada masa yang sama mencadangkan mencipta serta menjalankan workflow melalui UI/API. Kedua-dua ini bercanggah. Bahagian ini menyelesaikannya tanpa melemahkan isolasi.

### 5.3.1 Percanggahan dalam v2

| Keperluan v2 | Kesan `--network none` |
| --- | --- |
| Cipta workflow melalui UI | **Mustahil** — UI memerlukan HTTP ke port 5678 dari luar container |
| Jalankan workflow secara manual dari UI | **Mustahil** |
| Ambil output eksekusi dari UI | **Mustahil** |
| Padam workflow dari UI | **Mustahil** |

v2 tidak menyelesaikannya. Ia menyatakan kedua-duanya tanpa menyedari percanggahan.

### 5.3.2 Penyelesaian: operasi workflow melalui CLI dalam container

n8n membekalkan arahan CLI yang berfungsi **tanpa rangkaian**. Ini **VERIFIED** melalui `n8n --help` dan `n8n execute --help` dalam container sedia ada.

| Keperluan | Arahan CLI | Rangkaian diperlukan? |
| --- | --- | --- |
| Cipta workflow | `n8n import:workflow --input=file.json` | **Tidak** |
| Jalankan workflow | `n8n execute --id=<id> --rawOutput` | **Tidak** |
| Ambil output | stdout proses `execute` | **Tidak** |
| Sahkan workflow wujud | `n8n export:workflow --id=<id> --output=-` | **Tidak** |
| Padam workflow | Tiada arahan CLI | Tidak berkenaan — lihat 5.3.4 |

Output `n8n execute --help` yang disahkan:

```text
FLAGS
  --id <value>    id of the workflow to execute
  --rawOutput     Outputs only JSON data, with no other text
  --file <value>  DEPRECATED: Please use --id instead
```

Kesan: **isolasi rangkaian tidak perlu dilemahkan langsung.** Ujian berjalan melalui `docker exec`, bukan melalui HTTP.

### 5.3.3 Bagaimana data masuk dan keluar tanpa rangkaian

| Arah | Kaedah | Rangkaian? |
| --- | --- | --- |
| Workflow JSON masuk | `docker cp` fail ke container, kemudian `n8n import:workflow` | Tidak |
| Schema dan fixtur masuk | Dibenamkan dalam workflow JSON itu sendiri | Tidak |
| Output keluar | stdout `docker exec`, ditangkap pada hos | Tidak |
| Bukti keluar | Output disimpan pada hos ujian, kemudian pada pod | Tidak |

Tiada satu langkah memerlukan container mempunyai antara muka rangkaian.

### 5.3.4 Pemadaman workflow

CLI n8n **tiada** arahan padam workflow. Disahkan daripada `n8n --help`: arahan yang berkaitan hanya `export:workflow`, `import:workflow`, dan `db:revert`.

Ini **tidak** menjadi masalah kerana container ujian adalah sekali guna:

1. Container ujian mempunyai **volume SQLite sendiri**, bukan `n8n_n8n_data` produksi. Disahkan bahawa n8n menggunakan SQLite di `/home/node/.n8n/database.sqlite`.
2. Memadam container **dan** volumenya menghapuskan workflow ujian sepenuhnya.
3. Ini lebih bersih daripada memadam workflow secara individu, kerana tiada sisa dalam pangkalan data.

### 5.3.5 Konfigurasi isolasi yang disemak

| Kawalan | Nilai | Apa yang dicapai |
| --- | --- | --- |
| Rangkaian container | `--network none` | Tiada antara muka selain loopback |
| Port diterbitkan | Tiada | Tiada permukaan luaran |
| Persekitaran | Tiada `--env-file` produksi | Tiada kredensial diwarisi |
| Mount hos | Tiada daripada laluan produksi | Tiada akses fail produksi |
| Socket Docker | **Tiada** | Ujian tidak boleh mengawal container lain |
| Volume | Volume baharu, nama unik | SQLite ujian terasing daripada produksi |
| `docker cp` | Digunakan untuk masuk dan keluar | Satu-satunya saluran data, melalui daemon hos |

Nota: `docker cp` menggunakan daemon Docker hos, bukan rangkaian container. Ia tidak melemahkan `--network none`.

### 5.3.6 Apa yang **tidak** dibuktikan

Saya tidak mendakwa isolasi mutlak. Yang berikut kekal benar:

1. **Kernel dikongsi** dengan hos Coolify.
2. **Hos dikongsi** dengan staging dan Coolify jika ujian dijalankan di hos Coolify.
3. **Daemon Docker dikongsi** — `docker cp` dan `docker exec` melalui daemon hos.
4. **Akses shell hos** membolehkan sentuhan kedua-dua container ujian dan staging.

`--network none` mengasingkan **rangkaian container**. Ia tidak mengasingkan **hos**. Ini mesti ditulis dalam laporan keputusan A7.

### 5.3.7 Jika isolasi hos sebenar diperlukan

Hanya VM atau hos fizikal berasingan memberi isolasi kernel dan daemon. Itu tidak tersedia dalam inventori semasa. Ia akan menjadi **Pilihan D** yang memerlukan sumber baharu dan kelulusan berasingan. Di luar skop A7.

## 6. Reka bentuk workflow ujian

### 6.1 Bentuk minimum

Satu workflow, satu nod Code, tiada trigger produksi.

| Elemen | Nilai |
| --- | --- |
| Nama workflow | `A7-ajv-import-probe` (bukan `fg1kGKB6ciYADxu9`) |
| Trigger | Manual sahaja. Tiada webhook, tiada jadual. |
| Nod | Satu nod Code, mod "Run Once for All Items" |
| Nod lain | Tiada |

### 6.2 Kandungan nod Code

```javascript
// A7 probe. Semua data dibenamkan; tiada fail dibaca, tiada modul builtin diperlukan.
const attempts = [];

function probe(label, loader) {
  try {
    const mod = loader();
    attempts.push({ label, ok: true, keys: Object.keys(mod).slice(0, 6) });
  } catch (e) {
    attempts.push({ label, ok: false, code: e.code || e.name, message: String(e.message).split('\n')[0] });
  }
}

probe('require_ajv', () => require('ajv'));
probe('require_ajv_2019', () => require('ajv/dist/2019'));
probe('require_ajv_2020', () => require('ajv/dist/2020'));
probe('require_ajv_absolute', () =>
  require('/usr/local/lib/node_modules/n8n/node_modules/.pnpm/ajv@8.20.0/node_modules/ajv'));

return [{ json: { attempts } }];
```

Tiada `require('fs')`. Schema dan fixtur dibenamkan sebagai literal dalam nod, jadi nod ini tidak memerlukan akses sistem fail mahupun mana-mana modul builtin. Ini bermakna `NODE_FUNCTION_ALLOW_BUILTIN` **tidak diperlukan** untuk ujian ini.

### 6.3 Mengapa empat percubaan, bukan satu

| Percubaan | Menjawab |
| --- | --- |
| `require('ajv')` | Sama ada resolusi nama pendek berfungsi dalam konteks Code node |
| `require('ajv/dist/2019')` | Sama ada subpath 2019 berfungsi jika pakej utama berjaya |
| `require('ajv/dist/2020')` | Sama ada subpath 2020 berfungsi |
| Laluan mutlak | Sama ada laluan mutlak melepasi kedua-dua sekatan |

Jika kesemua gagal dengan `MODULE_NOT_FOUND`, itu bermakna allowlist atau resolusi menghalang. Jika kesemua gagal dengan ralat kebenaran berbeza, itu bermakna mekanisme Code node menyekat `require` sepenuhnya.

**Nota penting:** empat percubaan import **bukan** bermakna tiga konstruktor diuji. Import hanya membuktikan modul boleh dimuatkan. Instansiasi dan validasi ialah peringkat berasingan dalam 6.4.

### 6.4 Penjana nod dan pengekstrak — satu proses koheren

v5 mempunyai penjana yang tidak mengeluarkan penanda, dan prosedur yang bergantung pada penanda itu. v6 membetulkannya: penjana mengeluarkan penanda sebenar, dan pengekstrak memerlukannya.

#### 6.4.1 Hubungan penanda dengan pengekstrakan

| Peringkat | Komponen | Apa yang ia hasilkan atau perlukan |
| --- | --- | --- |
| Jana | `build-a7-node.py` | Node JS dengan 5 blok `A7:LITERAL:*` |
| Sahkan | `check_markers()` dalam penjana | Exit 5 jika penanda hilang, berganda, atau salah susunan |
| Eksport | `n8n export:workflow --output=-` | JSON workflow mengandungi `jsCode` dengan penanda |
| Ekstrak | `extract-and-verify.py` | 5 blok, satu per penanda, tiada pertindihan |
| Hash semula | `canon_sha()` dalam pengekstrak | Lapisan 2 untuk setiap objek |
| Banding | Pengekstrak | Hash dikira semula lawan pemalar yang diisytiharkan nod |

Setiap peringkat bergantung pada peringkat sebelumnya. Jika penjana tidak mengeluarkan penanda, pengekstrak gagal pada langkah pertama. Itu kecacatan v5 yang kini ditutup.

#### 6.4.2 Skrip penjana penuh — Stage A + penjanaan

Penjana v11 melakukan **dua** perkara: mengesahkan bait mentah kelima-lima fail sumber (Stage A), dan menghasilkan nod.

```python
#!/usr/bin/env python3
"""
build-a7-node.py (v11)

STAGE A — build-time source-file verification + node generation.

Stage A verifies the RAW BYTES of all five original source files against the
independent expected manifest. This is the only stage that can establish a
source-file provenance claim, because it reads the files from disk.

It then emits a complete n8n Code node containing five A7:LITERAL markers.

Raw-file vs canonical distinction (never mixed):
  Stage A  reads file bytes      -> sha256(bytes)          -> compare to layer1
  Stage B  re-parses the literal -> sha256(json.dumps(...)) -> compare to layer2

Result labels (printed on stderr):
  STAGE_A VERIFIED       all five source-file raw hashes matched, node written
  STAGE_A FAILED         raw mismatch, invalid manifest, invalid source JSON,
                         Layer 2 disagreement, marker or placeholder contract violated
  STAGE_A NOT VERIFIED   one or more original source files unavailable

Exit codes:
  0 STAGE_A VERIFIED
  1 STAGE_A FAILED
  2 argparse usage error (no stage result)
  3 STAGE_A NOT VERIFIED (manifest or source file unavailable)

FULL_CHAIN is never claimed by this script: it requires Stage B as well.
"""

import argparse
import hashlib
import json
import pathlib
import re
import sys

REQUIRED_OBJECTS = ("schema", "baseline", "t1", "t2", "t3")
REQUIRED_MARKERS = {
    "schema": "SCHEMA",
    "baseline": "FIXTURE_baseline",
    "t1": "FIXTURE_t1",
    "t2": "FIXTURE_t2",
    "t3": "FIXTURE_t3",
}
EXPECT_RESULT = {"baseline": "VALID", "t1": "INVALID", "t2": "INVALID", "t3": "INVALID"}

SENTINEL_RE = re.compile(r"__A7_SLOT_[A-Z_]+__")
SLOTS = ("SCHEMA_RAW_SHA", "SCHEMA_CANON_SHA", "SCHEMA_OBJECT", "FIXTURE_BLOCK", "FIXTURE_SHAS")
SHA_RE = re.compile(r"^[0-9a-f]{64}$")


def sha(b):
    return hashlib.sha256(b).hexdigest()


def canon_sha(obj):
    return sha(json.dumps(obj, indent=2, ensure_ascii=False).encode("utf-8"))


def die(msg, code=1):
    label = "STAGE_A NOT VERIFIED" if code == 3 else "STAGE_A FAILED"
    print(f"{label}: {msg}", file=sys.stderr)
    sys.exit(code)


def load_manifest(path):
    p = pathlib.Path(path)
    if not p.exists():
        die(f"manifest missing: {path}", 3)
    try:
        m = json.loads(p.read_text())
    except json.JSONDecodeError as e:
        die(f"manifest is not valid JSON: {e}")
    if not isinstance(m, dict):
        die("manifest root must be an object")
    if set(m) != {"manifest_version", "canonicalization", "objects", "approval"}:
        die(f"manifest root fields must be exactly manifest_version, canonicalization, objects, approval; got {sorted(m)}")
    if m.get("manifest_version") != 1:
        die(f"manifest_version must be 1, got {m.get('manifest_version')!r}")

    canon = m.get("canonicalization")
    if not isinstance(canon, dict):
        die("manifest.canonicalization must be an object")
    if set(canon) != {"convention", "encoding", "trailing_newline"}:
        die(f"manifest.canonicalization fields must be exactly ['convention', 'encoding', 'trailing_newline']; got {sorted(canon)}")
    for k in ("convention", "encoding", "trailing_newline"):
        if k not in canon:
            die(f"manifest.canonicalization missing {k}")
    if canon["convention"] != "json.dumps(obj, indent=2, ensure_ascii=False)":
        die(f"unexpected canonicalization.convention: {canon['convention']!r}")
    if canon["encoding"] != "utf-8":
        die(f"unexpected canonicalization.encoding: {canon['encoding']!r}")
    if canon["trailing_newline"] is not False:
        die("canonicalization.trailing_newline must be false")

    approval = m.get("approval")
    if not isinstance(approval, dict) or set(approval) != {"status", "note"}:
        die("manifest.approval fields must be exactly status and note")
    if approval.get("status") != "PENDING_OWNER_CONFIRMATION" or not isinstance(approval.get("note"), str):
        die("manifest.approval metadata is invalid")

    objs = m.get("objects")
    if not isinstance(objs, dict):
        die("manifest.objects must be an object")
    miss = set(REQUIRED_OBJECTS) - set(objs)
    extra = set(objs) - set(REQUIRED_OBJECTS)
    if miss:
        die(f"manifest missing objects: {sorted(miss)}")
    if extra:
        die(f"manifest has unexpected objects: {sorted(extra)}")

    for name in REQUIRED_OBJECTS:
        e = objs[name]
        if not isinstance(e, dict):
            die(f"manifest.objects.{name} must be an object")
        if set(e) != {"layer1_raw_sha256", "layer2_canon_sha256", "marker", "source"}:
            die(f"manifest.objects.{name} fields must be exactly layer1_raw_sha256, layer2_canon_sha256, marker, source")
        for k in ("layer1_raw_sha256", "layer2_canon_sha256", "marker", "source"):
            if k not in e:
                die(f"manifest.objects.{name} missing {k}")
        for k in ("layer1_raw_sha256", "layer2_canon_sha256"):
            if not isinstance(e[k], str) or not SHA_RE.match(e[k]):
                die(f"manifest.objects.{name}.{k} is not 64 lowercase hex")
        if not isinstance(e["source"], str) or not e["source"]:
            die(f"manifest.objects.{name}.source must be a non-empty string")
        if e["marker"] != REQUIRED_MARKERS[name]:
            die(f"manifest.objects.{name}.marker must be {REQUIRED_MARKERS[name]!r}, got {e['marker']!r}")

    for layer in ("layer1_raw_sha256", "layer2_canon_sha256"):
        seen = {}
        for name in REQUIRED_OBJECTS:
            h = objs[name][layer]
            if h in seen:
                die(f"manifest duplicate {layer} for {name} and {seen[h]}")
            seen[h] = name

    return objs


def stage_a_verify(paths, manifest):
    """Read raw bytes of each source file and compare to manifest layer 1."""
    results = {}
    payloads = {}

    for name in REQUIRED_OBJECTS:
        path = paths[name]
        p = pathlib.Path(path)
        if not p.exists():
            print(f"SOURCE_RAW_NOT_VERIFIED {name} path_missing={path}", file=sys.stderr)
            results[name] = "NOT_VERIFIED"
            continue

        raw = p.read_bytes()
        actual = sha(raw)
        expected = manifest[name]["layer1_raw_sha256"]

        if actual == expected:
            print(f"SOURCE_RAW_MATCH {name} {actual}", file=sys.stderr)
            results[name] = "MATCH"
            try:
                payloads[name] = json.loads(raw.decode("utf-8"))
            except (UnicodeDecodeError, json.JSONDecodeError) as e:
                die(f"{name}: file matched hash but is not valid JSON: {e}")
        else:
            print(f"SOURCE_RAW_MISMATCH {name} expected={expected} actual={actual}", file=sys.stderr)
            results[name] = "MISMATCH"

    if any(v == "MISMATCH" for v in results.values()):
        die("source file mismatch — refusing to generate")

    if any(v == "NOT_VERIFIED" for v in results.values()):
        missing = [k for k, v in results.items() if v == "NOT_VERIFIED"]
        die(f"source files unavailable: {missing}; generation refused", 3)

    return payloads


def jslit(obj):
    return json.dumps(obj, indent=2, ensure_ascii=False)


def check_markers(node):
    positions = {}
    for name in REQUIRED_OBJECTS:
        marker = REQUIRED_MARKERS[name]
        begin = f"/* A7:LITERAL:{marker}:BEGIN */"
        end = f"/* A7:LITERAL:{marker}:END */"
        cb, ce = node.count(begin), node.count(end)
        if cb != 1:
            die(f"marker {marker} BEGIN appears {cb} times, expected 1")
        if ce != 1:
            die(f"marker {marker} END appears {ce} times, expected 1")
        ib, ie = node.index(begin), node.index(end)
        if ie <= ib:
            die(f"marker {marker} END precedes BEGIN")
        positions[name] = (ib, ie)

    order = [positions[n][0] for n in REQUIRED_OBJECTS]
    if order != sorted(order):
        die("markers out of declared order")

    names = list(REQUIRED_OBJECTS)
    for i, a in enumerate(names):
        sa, ea = positions[a]
        for b in names[i + 1:]:
            sb, _ = positions[b]
            if sa < sb < ea:
                die(f"overlapping markers: {a} and {b}")

    return positions


def render(template, slots):
    out = template
    for name in SLOTS:
        token = f"__A7_SLOT_{name}__"
        if token not in out:
            die(f"template missing slot {token}")
        out = out.replace(token, slots[name])
    leftover = SENTINEL_RE.findall(out)
    if leftover:
        die(f"unresolved placeholders: {sorted(set(leftover))}")
    if "__A7_SLOT" in out:
        die("partial sentinel fragment remains")
    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--expected-manifest", required=True)
    for k in REQUIRED_OBJECTS:
        ap.add_argument(f"--{k}", required=True)
    ap.add_argument("--out", required=True)
    a = ap.parse_args()

    manifest = load_manifest(a.expected_manifest)
    paths = {k: getattr(a, k) for k in REQUIRED_OBJECTS}

    print("STAGE build-time", file=sys.stderr)
    payloads = stage_a_verify(paths, manifest)

    schema = payloads["schema"]
    fixtures = {k: payloads[k] for k in ("baseline", "t1", "t2", "t3")}

    fixture_block = ",\n".join(
        "    /* A7:LITERAL:%s:BEGIN */\n"
        "    { name: %s, expect: %s, data: %s }\n"
        "    /* A7:LITERAL:%s:END */"
        % (REQUIRED_MARKERS[k], json.dumps(k), json.dumps(EXPECT_RESULT[k]),
           jslit(fixtures[k]), REQUIRED_MARKERS[k])
        for k in ("baseline", "t1", "t2", "t3")
    )

    fixture_shas = ",\n".join(
        "    { name: %s, expect: %s, canon_sha: %s }"
        % (json.dumps(k), json.dumps(EXPECT_RESULT[k]), json.dumps(manifest[k]["layer2_canon_sha256"]))
        for k in ("baseline", "t1", "t2", "t3")
    )

    # Layer 2 must agree between manifest and recomputation from source objects.
    for name in REQUIRED_OBJECTS:
        expected_l2 = manifest[name]["layer2_canon_sha256"]
        actual_l2 = canon_sha(schema if name == "schema" else fixtures[name])
        if expected_l2 != actual_l2:
            die(f"{name}: layer2 mismatch manifest={expected_l2} recomputed={actual_l2}")
        print(f"LAYER2_MANIFEST_CONSISTENT {name} {actual_l2}", file=sys.stderr)

    slots = {
        "SCHEMA_RAW_SHA": json.dumps(manifest["schema"]["layer1_raw_sha256"]),
        "SCHEMA_CANON_SHA": json.dumps(manifest["schema"]["layer2_canon_sha256"]),
        "SCHEMA_OBJECT": jslit(schema),
        "FIXTURE_BLOCK": fixture_block,
        "FIXTURE_SHAS": fixture_shas,
    }

    node = render(TEMPLATE, slots)
    check_markers(node)

    pathlib.Path(a.out).write_text(node, encoding="utf-8")
    data = node.encode("utf-8")
    print(f"WROTE {a.out}", file=sys.stderr)
    print(f"OUTPUT_BYTES {len(data)}", file=sys.stderr)
    print(f"OUTPUT_SHA {sha(data)}", file=sys.stderr)
    print(f"SENTINELS_REMAINING 0", file=sys.stderr)
    print(f"MARKERS_OK {len(REQUIRED_OBJECTS)}", file=sys.stderr)
    print("STAGE_A VERIFIED", file=sys.stderr)
    print("FULL_CHAIN NOT VERIFIED: Stage B has not been evaluated by build-a7-node.py", file=sys.stderr)


TEMPLATE = r"""'use strict';
// A7 probe — generated by build-a7-node.py (v11). Do not edit by hand.
// No filesystem access. No builtin modules. All data inlined.

const AJV_ROOT =
  '/usr/local/lib/node_modules/n8n/node_modules/.pnpm/ajv@8.20.0/node_modules/ajv';

const MODULE_SPECS = [
  { key: 'draft07', path: AJV_ROOT,                exportName: 'Ajv'     },
  { key: 'y2019',   path: AJV_ROOT + '/dist/2019', exportName: 'Ajv2019' },
  { key: 'y2020',   path: AJV_ROOT + '/dist/2020', exportName: 'Ajv2020' },
];

// LAYER 1 (schema): SHA-256 of the real schema file bytes.
// Fixture Layer 1 is NOT a file hash (canonical JSON + one trailing newline).
// The four fixture Layer 1 values are NOT embedded; Stage B cannot verify them.
const SCHEMA_RAW_SHA = __A7_SLOT_SCHEMA_RAW_SHA__;
// LAYER 2 marker: canonical JSON serialisation.
const SCHEMA_CANON_SHA = __A7_SLOT_SCHEMA_CANON_SHA__;

/* A7:LITERAL:SCHEMA:BEGIN */
const SCHEMA_OBJECT = __A7_SLOT_SCHEMA_OBJECT__;
/* A7:LITERAL:SCHEMA:END */

const FIXTURES = [
__A7_SLOT_FIXTURE_BLOCK__
];

// LAYER 2 hashes, copied from the verified manifest at build time.
const FIXTURE_SHAS = [
__A7_SLOT_FIXTURE_SHAS__
];

// ---- stage 1: module load -------------------------------------------------
const loadLog = [];
const loaded = {};
for (const spec of MODULE_SPECS) {
  try {
    loaded[spec.key] = require(spec.path);
    loadLog.push({ stage: 'load', key: spec.key, ok: true });
  } catch (e) {
    loadLog.push({ stage: 'load', key: spec.key, ok: false,
                   code: e.code || e.name,
                   message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 2: constructor ------------------------------------------------
const ctorLog = [];
const instances = {};
for (const spec of MODULE_SPECS) {
  if (!loaded[spec.key]) {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false, reason: 'module not loaded' });
    continue;
  }
  const mod = loaded[spec.key];
  const Ctor = mod[spec.exportName] || mod.default || mod;
  if (typeof Ctor !== 'function') {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false,
                   reason: 'export not a constructor', exportName: spec.exportName });
    continue;
  }
  try {
    const inst = new Ctor({ strict: false });
    instances[spec.key] = inst;
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: true,
                   meta: typeof inst.defaultMeta === 'function' ? inst.defaultMeta() : null });
  } catch (e) {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false,
                   code: e.code || e.name,
                   message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 3: compile ----------------------------------------------------
const compileLog = [];
const validators = {};
for (const spec of MODULE_SPECS) {
  if (!instances[spec.key]) {
    compileLog.push({ stage: 'compile', key: spec.key, ok: false, reason: 'no constructor' });
    continue;
  }
  try {
    validators[spec.key] = instances[spec.key].compile(SCHEMA_OBJECT);
    compileLog.push({ stage: 'compile', key: spec.key, ok: true });
  } catch (e) {
    compileLog.push({ stage: 'compile', key: spec.key, ok: false,
                      code: e.code || e.name,
                      message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 4: validate ---------------------------------------------------
const validationLog = [];
for (const spec of MODULE_SPECS) {
  if (!validators[spec.key]) {
    validationLog.push({ stage: 'validate', key: spec.key, ok: false, reason: 'no validator' });
    continue;
  }
  const validate = validators[spec.key];
  for (const fx of FIXTURES) {
    const copy = JSON.parse(JSON.stringify(fx.data));
    let ok;
    let errors = [];
    try {
      ok = validate(copy);
      if (!ok) {
        errors = (validate.errors || []).map(function (e) {
          return { keyword: e.keyword, path: e.instancePath || '/' };
        });
      }
    } catch (e) {
      validationLog.push({ stage: 'validate', key: spec.key, fixture: fx.name,
                           thrown: e.name,
                           message: String(e.message).split('\n')[0] });
      continue;
    }
    validationLog.push({
      stage: 'validate', key: spec.key, fixture: fx.name,
      result: ok ? 'VALID' : 'INVALID',
      expected: fx.expect,
      match: (ok && fx.expect === 'VALID') || (!ok && fx.expect === 'INVALID'),
      errorCount: errors.length,
      errors: errors,
    });
  }
}

return [{ json: {
  ajv_root: AJV_ROOT,
  schema_raw_sha: SCHEMA_RAW_SHA,
  schema_canon_sha: SCHEMA_CANON_SHA,
  fixture_shas: FIXTURE_SHAS,
  loadLog: loadLog,
  ctorLog: ctorLog,
  compileLog: compileLog,
  validationLog: validationLog,
} }];
"""

if __name__ == "__main__":
    main()

```

#### 6.4.3 Manifest hash jangkaan

**Nota v11.** Nilai `layer1_raw_sha256` bagi empat fixtur **bukan** hash bait fail. Ia ialah hash JSON kanonik + `\n` (lihat 7.2b). Nama medan dikekalkan supaya sepuluh nilai yang diluluskan kekal bait demi bait dan supaya manifest sedia ada kekal sah. Tafsiran medan itu diterangkan dalam 7.2, bukan oleh namanya.

##### 6.4.3a Kandungan penuh

```json
{
  "manifest_version": 1,
  "canonicalization": {
    "convention": "json.dumps(obj, indent=2, ensure_ascii=False)",
    "encoding": "utf-8",
    "trailing_newline": false
  },
  "objects": {
    "schema": {
      "layer1_raw_sha256": "0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21",
      "layer2_canon_sha256": "ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51",
      "marker": "SCHEMA",
      "source": "canonical-sitespec-schema-v1.1.json"
    },
    "baseline": {
      "layer1_raw_sha256": "68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a",
      "layer2_canon_sha256": "84a1f232d513e41eb59c1c34ed73743bae26f4be2822b059ff8a08e40cce029d",
      "marker": "FIXTURE_baseline",
      "source": "baseline fixture (execution 80 payload, approved A3)"
    },
    "t1": {
      "layer1_raw_sha256": "52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3",
      "layer2_canon_sha256": "9fc9ba234d3123a5ae3ba8c2fae87677ded42d9ff84c310258d7359762b1afb3",
      "marker": "FIXTURE_t1",
      "source": "A3-T1 additionalProperties fixture"
    },
    "t2": {
      "layer1_raw_sha256": "45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011",
      "layer2_canon_sha256": "6f6a7cbd4afa004a8bb946bf01e91fa3c637b55606ffb85fe347d7b2b6ab91c9",
      "marker": "FIXTURE_t2",
      "source": "A3-T2 enum fixture"
    },
    "t3": {
      "layer1_raw_sha256": "0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051",
      "layer2_canon_sha256": "e0fbc1bcc5779cd01da17b2f9fae57e714e521db7a7a17fbe4e96573a471a54c",
      "marker": "FIXTURE_t3",
      "source": "A3-T3 type fixture"
    }
  },
  "approval": {
    "status": "PENDING_OWNER_CONFIRMATION",
    "note": "Values transcribed from v5/v6 evidence reports. Independent re-derivation from original source files is required before this manifest is authoritative. See plan section 7.5."
  }
}
```

##### 6.4.3b Struktur dan pengesahan

| Medan | Peraturan | Jika gagal (v10) |
| --- | --- | --- |
| `manifest_version` | Mesti `1` | STAGE_A/STAGE_B FAILED, exit 1 |
| `canonicalization.convention` | Mesti `json.dumps(obj, indent=2, ensure_ascii=False)` | STAGE_A/STAGE_B FAILED, exit 1 |
| `canonicalization.encoding` | Mesti `utf-8` | STAGE_A/STAGE_B FAILED, exit 1 |
| `canonicalization.trailing_newline` | Mesti `false` | STAGE_A/STAGE_B FAILED, exit 1 |
| `objects` | Tepat lima kunci | STAGE_A/STAGE_B FAILED, exit 1 |
| `objects.<n>.layer1_raw_sha256` | 64 hex huruf kecil | STAGE_A/STAGE_B FAILED, exit 1 |
| `objects.<n>.layer2_canon_sha256` | 64 hex huruf kecil | STAGE_A/STAGE_B FAILED, exit 1 |
| `objects.<n>.marker` | Mesti sepadan pemetaan tetap | STAGE_A/STAGE_B FAILED, exit 1 |
| Duplikasi hash | Tiada dua objek berkongsi hash dalam lapisan yang sama | STAGE_A/STAGE_B FAILED, exit 1 |

##### 6.4.3c Hasil ujian pengesahan manifest

Dua belas laluan diuji dalam sesi ini. Semuanya menolak dengan betul:

```text
FATAL manifest missing: /tmp/nope.json
FATAL manifest is not valid JSON          (diuji secara tidak langsung)
FATAL manifest_version must be 1, got 2
FATAL manifest.canonicalization missing encoding
FATAL unexpected canonicalization.convention: 'json.dumps(obj, indent=2, ensure_ascii=False)'
FATAL unexpected canonicalization.encoding: 'UTF-8'
FATAL canonicalization.trailing_newline must be false
FATAL manifest missing objects: ['t2']
FATAL manifest has unexpected objects: ['extra']
FATAL manifest.objects.t1.layer2_canon_sha256 is not 64 lowercase hex
FATAL manifest.objects.t1.marker must be 'FIXTURE_t1', got 'FIXTURE_t2'
FATAL manifest.objects.schema.marker must be 'SCHEMA', got 'FIXTURE_schema'
FATAL manifest duplicate layer1_raw_sha256 for t2 and t1
```

##### 6.4.3d Mengapa manifest mesti berasingan

Pengekstrak tidak mempunyai laluan kod yang membaca nilai jangkaan daripada `--export`. Independence diwujudkan melalui **kustodi dan kelulusan**, bukan melalui kod.

#### 6.4.4 Skrip pengekstrak penuh — Stage B sahaja

Skrip ini **tidak** membaca fail sumber. Ia hanya membandingkan workflow yang dieksport terhadap manifest.

```python
#!/usr/bin/env python3
"""
extract-and-verify.py (v11) — POST-EXPORT verification only.

Scope of this script:
  It verifies an EXPORTED WORKFLOW against an EXTERNAL expected-hash manifest.
  It does NOT verify the original source files on disk. That is Stage A,
  performed by the generator at build time (build-a7-node.py).

Post-export comparisons performed here:
  5 x Layer 2 canonical  : recomputed from extracted literal  vs  manifest
  1 x Layer 1 schema     : workflow-declared SCHEMA_RAW_SHA   vs  manifest
  5 x declared-vs-recomputed : workflow constant              vs  recomputed
  ------------------------------------------------------------------------
  11 post-export comparisons total.

Layer 1 raw hashes for the four fixtures are NOT verifiable here: the workflow
does not embed them, and this script is not given the original files. They are
reported NOT APPLICABLE / NOT VERIFIED explicitly, never as MATCH.

Result vocabulary:
  LAYER2_CANONICAL_MATCH / LAYER2_CANONICAL_MISMATCH
  LAYER1_SCHEMA_MATCH / LAYER1_SCHEMA_MISMATCH / LAYER1_SCHEMA_NOT_VERIFIED
  WORKFLOW_DECLARED_HASH_MATCH / WORKFLOW_DECLARED_HASH_MISMATCH
  STAGE_B VERIFIED / STAGE_B FAILED / STAGE_B NOT VERIFIED
  FULL_CHAIN NOT VERIFIED (printed on success: Stage A not evaluated here)
  FULL_CHAIN FAILED (printed when Stage B fails)

Exit codes: 0 STAGE_B VERIFIED (never a full-chain claim), 1 STAGE_B FAILED,
2 argparse usage error (no stage result), 3 STAGE_B NOT VERIFIED.
"""

import argparse
import hashlib
import json
import pathlib
import re
import sys

SHA_RE = re.compile(r"^[0-9a-f]{64}$")

REQUIRED_OBJECTS = ("schema", "baseline", "t1", "t2", "t3")
FIXTURE_NAMES = ("baseline", "t1", "t2", "t3")

REQUIRED_MARKERS = {
    "schema": "SCHEMA",
    "baseline": "FIXTURE_baseline",
    "t1": "FIXTURE_t1",
    "t2": "FIXTURE_t2",
    "t3": "FIXTURE_t3",
}

CANONICALIZATION_KEYS = ("convention", "encoding", "trailing_newline")
ROOT_KEYS = {"manifest_version", "canonicalization", "objects", "approval"}
ENTRY_KEYS = {"layer1_raw_sha256", "layer2_canon_sha256", "marker", "source"}
APPROVAL_KEYS = {"status", "note"}
EXPECT_RESULT = {"baseline": "VALID", "t1": "INVALID", "t2": "INVALID", "t3": "INVALID"}


def sha(b):
    return hashlib.sha256(b).hexdigest()


def canon_sha(obj):
    """Layer 2: json.dumps(obj, indent=2, ensure_ascii=False), UTF-8 bytes, no trailing newline."""
    return sha(json.dumps(obj, indent=2, ensure_ascii=False).encode("utf-8"))


def die(msg, code=1):
    label = "STAGE_B NOT VERIFIED" if code == 3 else "STAGE_B FAILED"
    print(f"{label}: {msg}", file=sys.stderr)
    sys.exit(code)


# ------------------------------------------------------------ manifest -----
def load_manifest(path):
    p = pathlib.Path(path)
    if not p.exists():
        die(f"manifest missing: {path}", 3)
    try:
        m = json.loads(p.read_text())
    except json.JSONDecodeError as e:
        die(f"manifest is not valid JSON: {e}")
    if not isinstance(m, dict):
        die("manifest root must be an object")

    # 1. exact root contract and version
    if set(m) != ROOT_KEYS:
        die(f"manifest root fields must be exactly {sorted(ROOT_KEYS)}, got {sorted(m)}")
    if m.get("manifest_version") != 1:
        die(f"manifest_version must be 1, got {m.get('manifest_version')!r}")

    # 2. canonicalization metadata
    canon = m.get("canonicalization")
    if not isinstance(canon, dict):
        die("manifest.canonicalization must be an object")
    if set(canon) != set(CANONICALIZATION_KEYS):
        die(f"manifest.canonicalization fields must be exactly {sorted(CANONICALIZATION_KEYS)}; got {sorted(canon)}")
    for k in CANONICALIZATION_KEYS:
        if k not in canon:
            die(f"manifest.canonicalization missing {k}")
    if canon["convention"] != "json.dumps(obj, indent=2, ensure_ascii=False)":
        die(f"unexpected canonicalization.convention: {canon['convention']!r}")
    if canon["encoding"] != "utf-8":
        die(f"unexpected canonicalization.encoding: {canon['encoding']!r}")
    if canon["trailing_newline"] is not False:
        die("canonicalization.trailing_newline must be false")

    approval = m.get("approval")
    if not isinstance(approval, dict) or set(approval) != APPROVAL_KEYS:
        die("manifest.approval fields must be exactly status and note")
    if approval.get("status") != "PENDING_OWNER_CONFIRMATION" or not isinstance(approval.get("note"), str):
        die("manifest.approval metadata is invalid")

    # 3. objects: exact set
    objs = m.get("objects")
    if not isinstance(objs, dict):
        die("manifest.objects must be an object")
    missing = set(REQUIRED_OBJECTS) - set(objs)
    extra = set(objs) - set(REQUIRED_OBJECTS)
    if missing:
        die(f"manifest missing objects: {sorted(missing)}")
    if extra:
        die(f"manifest has unexpected objects: {sorted(extra)}")

    # 4. per-entry fields, hash format, marker mapping
    for name in REQUIRED_OBJECTS:
        e = objs[name]
        if not isinstance(e, dict):
            die(f"manifest.objects.{name} must be an object")
        if set(e) != ENTRY_KEYS:
            die(f"manifest.objects.{name} fields must be exactly {sorted(ENTRY_KEYS)}; got {sorted(e)}")
        for k in ("layer1_raw_sha256", "layer2_canon_sha256"):
            v = e[k]
            if not isinstance(v, str) or not SHA_RE.match(v):
                die(f"manifest.objects.{name}.{k} is not 64 lowercase hex")
        if not isinstance(e["source"], str) or not e["source"]:
            die(f"manifest.objects.{name}.source must be a non-empty string")

        # 5. strict marker mapping
        expected_marker = REQUIRED_MARKERS[name]
        if e["marker"] != expected_marker:
            die(f"manifest.objects.{name}.marker must be {expected_marker!r}, got {e['marker']!r}")

    # 6. duplicate detection within each layer
    for layer in ("layer1_raw_sha256", "layer2_canon_sha256"):
        seen = {}
        for name in REQUIRED_OBJECTS:
            h = objs[name][layer]
            if h in seen:
                die(f"manifest duplicate {layer} for {name} and {seen[h]}")
            seen[h] = name

    return objs


# ------------------------------------------------------------ workflow -----
def load_workflow(path):
    p = pathlib.Path(path)
    if not p.exists():
        die(f"export missing: {path}", 3)
    try:
        wf = json.loads(p.read_text())
    except json.JSONDecodeError as e:
        die(f"export is not valid JSON: {e}")

    if isinstance(wf, dict):
        wf = [wf]
    if not isinstance(wf, list) or not wf:
        die("export structure unexpected")
    nodes = wf[0].get("nodes")
    if not isinstance(nodes, list):
        die("export has no nodes array")

    code_nodes = [n for n in nodes if n.get("type") == "n8n-nodes-base.code"]
    if len(code_nodes) != 1:
        die(f"expected exactly 1 Code node, found {len(code_nodes)}")

    code = code_nodes[0].get("parameters", {}).get("jsCode")
    if not isinstance(code, str) or not code:
        die("jsCode missing or empty")
    return code_nodes[0].get("name"), code


# ------------------------------------------------------------- markers -----
def block_bounds(code, marker):
    begin = f"/* A7:LITERAL:{marker}:BEGIN */"
    end = f"/* A7:LITERAL:{marker}:END */"
    cb, ce = code.count(begin), code.count(end)
    if cb != 1:
        raise ValueError(f"marker {marker}: BEGIN count {cb}, expected 1")
    if ce != 1:
        raise ValueError(f"marker {marker}: END count {ce}, expected 1")
    ib, ie = code.index(begin), code.index(end)
    if ie <= ib:
        raise ValueError(f"marker {marker}: END precedes BEGIN")
    return ib + len(begin), ie


def check_no_overlap(code, bounds):
    names = list(bounds)
    for i, a in enumerate(names):
        sa, ea = bounds[a]
        for b in names[i + 1:]:
            sb, eb = bounds[b]
            if sa < sb < ea or sb < sa < eb:
                raise ValueError(f"overlapping markers: {a} and {b}")


def brace_slice(text, name):
    if not text.startswith("{"):
        raise ValueError(f"{name}: literal does not start with '{{'")
    depth, in_str, esc = 0, False, False
    for i, ch in enumerate(text):
        if in_str:
            if esc:
                esc = False
            elif ch == "\\":
                esc = True
            elif ch == '"':
                in_str = False
            continue
        if ch == '"':
            in_str = True
        elif ch == "{":
            depth += 1
        elif ch == "}":
            depth -= 1
            if depth == 0:
                return text[:i + 1]
    raise ValueError(f"{name}: unbalanced braces")


def parse_schema(block):
    """Consume the entire schema marker block; no trailing JS is permitted."""
    m = re.match(r"^\s*const\s+SCHEMA_OBJECT\s*=\s*", block)
    if not m:
        raise ValueError("SCHEMA: expected exactly const SCHEMA_OBJECT =")
    rest = block[m.end():]
    text = brace_slice(rest.lstrip(), "SCHEMA")
    suffix = rest[len(rest) - len(rest.lstrip()) + len(text):]
    if not re.fullmatch(r"\s*;?\s*", suffix):
        raise ValueError("SCHEMA: trailing content inside marker block")
    return text


def parse_fixture(block, name):
    """Consume exactly {name, expect, data} and reject all trailing content."""
    m = re.match(r'^\s*\{\s*name\s*:\s*"([^"\\]+)"\s*,\s*expect\s*:\s*"([A-Z]+)"\s*,\s*data\s*:\s*', block)
    if not m:
        raise ValueError(f"{name}: expected exact fixture wrapper")
    if m.group(1) != name:
        raise ValueError(f"{name}: fixture name is {m.group(1)!r}")
    if m.group(2) != EXPECT_RESULT[name]:
        raise ValueError(f"{name}: expected result is {m.group(2)!r}")
    rest = block[m.end():]
    data = brace_slice(rest.lstrip(), name)
    suffix = rest[len(rest) - len(rest.lstrip()) + len(data):]
    if not re.fullmatch(r"\s*\}\s*;?\s*", suffix):
        raise ValueError(f"{name}: trailing or additional content inside marker block")
    return data

def as_json(text, name):
    try:
        return json.loads(text)
    except json.JSONDecodeError as e:
        raise ValueError(f"{name}: literal is not valid JSON: {e}")


# ------------------------------------------------- declared (internal only) -
def declared_schema_raw(code):
    m = re.search(r'const\s+SCHEMA_RAW_SHA\s*=\s*"([0-9a-f]{64})"', code)
    return m.group(1) if m else None


def declared_schema_canon(code):
    m = re.search(r'const\s+SCHEMA_CANON_SHA\s*=\s*"([0-9a-f]{64})"', code)
    return m.group(1) if m else None


def declared_fixture_canon(code, short):
    m = re.search(
        r'name:\s*"%s"\s*,\s*expect:\s*"[A-Z]+"\s*,\s*canon_sha:\s*"([0-9a-f]{64})"'
        % re.escape(short),
        code,
    )
    return m.group(1) if m else None


# ------------------------------------------------------------------ main --
def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--export", required=True)
    ap.add_argument("--expected-manifest", required=True,
                    help="INDEPENDENT manifest; never sourced from --export")
    a = ap.parse_args()

    manifest = load_manifest(a.expected_manifest)
    node_name, code = load_workflow(a.export)

    print(f"CODE_NODE {node_name}")
    print(f"JSCODE_LEN {len(code)}")
    print(f"MANIFEST {a.expected_manifest}")
    print(f"STAGE post-export")

    failures = []
    not_verified = []

    # ---- marker integrity (all five) ----
    bounds = {}
    try:
        for name in REQUIRED_OBJECTS:
            bounds[name] = block_bounds(code, REQUIRED_MARKERS[name])
        check_no_overlap(code, bounds)
        print(f"MARKERS_OK {len(bounds)}")
    except ValueError as e:
        print(f"MARKER_FAILED {e}")
        print("STAGE_B FAILED reasons=['markers']")
        print("FULL_CHAIN FAILED: stage B failed")
        sys.exit(1)

    # ---- Layer 2 independent comparison (5) ----
    recomputed = {}
    for name in REQUIRED_OBJECTS:
        s, e = bounds[name]
        block = code[s:e].strip()
        try:
            text = parse_schema(block) if name == "schema" else parse_fixture(block, name)
            obj = as_json(text, name)
        except ValueError as ex:
            print(f"LAYER2_CANONICAL_MISMATCH {name} parse_error={ex}")
            failures.append(f"layer2:{name}:parse")
            continue

        recomputed[name] = canon_sha(obj)
        expected = manifest[name]["layer2_canon_sha256"]
        if recomputed[name] == expected:
            print(f"LAYER2_CANONICAL_MATCH {name} {recomputed[name]}")
        else:
            print(f"LAYER2_CANONICAL_MISMATCH {name} expected={expected} actual={recomputed[name]}")
            failures.append(f"layer2:{name}")

    # ---- Layer 1: schema declaration vs manifest (1) ----
    manifest_l1_schema = manifest["schema"]["layer1_raw_sha256"]
    wf_l1_schema = declared_schema_raw(code)
    if wf_l1_schema is None:
        print("LAYER1_SCHEMA_NOT_VERIFIED declared SCHEMA_RAW_SHA absent")
        not_verified.append("layer1:schema:absent")
    elif wf_l1_schema == manifest_l1_schema:
        print(f"LAYER1_SCHEMA_MATCH {wf_l1_schema}")
    else:
        print(f"LAYER1_SCHEMA_MISMATCH expected={manifest_l1_schema} actual={wf_l1_schema}")
        failures.append("layer1:schema")

    # ---- declared-vs-recomputed (5) — internal consistency only ----
    for name in REQUIRED_OBJECTS:
        if name not in recomputed:
            print(f"WORKFLOW_DECLARED_HASH_MISMATCH {name} no_recomputed_value")
            failures.append(f"declared:{name}:unavailable")
            continue
        wf_declared = (declared_schema_canon(code) if name == "schema"
                       else declared_fixture_canon(code, name))
        if wf_declared is None:
            print(f"WORKFLOW_DECLARED_HASH_MISMATCH {name} constant absent")
            failures.append(f"declared:{name}:absent")
        elif wf_declared == recomputed[name]:
            print(f"WORKFLOW_DECLARED_HASH_MATCH {name}")
        else:
            print(f"WORKFLOW_DECLARED_HASH_MISMATCH {name} "
                  f"declared={wf_declared} recomputed={recomputed[name]}")
            failures.append(f"declared:{name}")

    # ---- fixture Layer 1: explicitly NOT VERIFIED here ----
    for name in FIXTURE_NAMES:
        print(f"LAYER1_RAW_NOT_VERIFIED {name} workflow_does_not_embed_raw_hash")

    print()
    if failures:
        print(f"STAGE_B FAILED reasons={failures}")
        print("FULL_CHAIN FAILED: stage B failed")
        sys.exit(1)
    if not_verified:
        print(f"STAGE_B NOT VERIFIED reasons={not_verified}")
        print("FULL_CHAIN NOT VERIFIED: stage B evidence incomplete")
        sys.exit(3)
    print("STAGE_B VERIFIED")
    print("FULL_CHAIN NOT VERIFIED: Stage A source-file verification is not performed by extract-and-verify.py")
    sys.exit(0)


if __name__ == "__main__":
    main()

```

#### 6.4.5 Templat nod

```javascript
'use strict';
// A7 probe — generated by build-a7-node.py (v11). Do not edit by hand.
// No filesystem access. No builtin modules. All data inlined.

const AJV_ROOT =
  '/usr/local/lib/node_modules/n8n/node_modules/.pnpm/ajv@8.20.0/node_modules/ajv';

const MODULE_SPECS = [
  { key: 'draft07', path: AJV_ROOT,                exportName: 'Ajv'     },
  { key: 'y2019',   path: AJV_ROOT + '/dist/2019', exportName: 'Ajv2019' },
  { key: 'y2020',   path: AJV_ROOT + '/dist/2020', exportName: 'Ajv2020' },
];

// LAYER 1 (schema): SHA-256 of the real schema file bytes.
// Fixture Layer 1 is NOT a file hash (canonical JSON + one trailing newline).
// The four fixture Layer 1 values are NOT embedded; Stage B cannot verify them.
const SCHEMA_RAW_SHA = __A7_SLOT_SCHEMA_RAW_SHA__;
// LAYER 2 marker: canonical JSON serialisation.
const SCHEMA_CANON_SHA = __A7_SLOT_SCHEMA_CANON_SHA__;

/* A7:LITERAL:SCHEMA:BEGIN */
const SCHEMA_OBJECT = __A7_SLOT_SCHEMA_OBJECT__;
/* A7:LITERAL:SCHEMA:END */

const FIXTURES = [
__A7_SLOT_FIXTURE_BLOCK__
];

// LAYER 2 hashes, copied from the verified manifest at build time.
const FIXTURE_SHAS = [
__A7_SLOT_FIXTURE_SHAS__
];

// ---- stage 1: module load -------------------------------------------------
const loadLog = [];
const loaded = {};
for (const spec of MODULE_SPECS) {
  try {
    loaded[spec.key] = require(spec.path);
    loadLog.push({ stage: 'load', key: spec.key, ok: true });
  } catch (e) {
    loadLog.push({ stage: 'load', key: spec.key, ok: false,
                   code: e.code || e.name,
                   message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 2: constructor ------------------------------------------------
const ctorLog = [];
const instances = {};
for (const spec of MODULE_SPECS) {
  if (!loaded[spec.key]) {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false, reason: 'module not loaded' });
    continue;
  }
  const mod = loaded[spec.key];
  const Ctor = mod[spec.exportName] || mod.default || mod;
  if (typeof Ctor !== 'function') {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false,
                   reason: 'export not a constructor', exportName: spec.exportName });
    continue;
  }
  try {
    const inst = new Ctor({ strict: false });
    instances[spec.key] = inst;
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: true,
                   meta: typeof inst.defaultMeta === 'function' ? inst.defaultMeta() : null });
  } catch (e) {
    ctorLog.push({ stage: 'constructor', key: spec.key, ok: false,
                   code: e.code || e.name,
                   message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 3: compile ----------------------------------------------------
const compileLog = [];
const validators = {};
for (const spec of MODULE_SPECS) {
  if (!instances[spec.key]) {
    compileLog.push({ stage: 'compile', key: spec.key, ok: false, reason: 'no constructor' });
    continue;
  }
  try {
    validators[spec.key] = instances[spec.key].compile(SCHEMA_OBJECT);
    compileLog.push({ stage: 'compile', key: spec.key, ok: true });
  } catch (e) {
    compileLog.push({ stage: 'compile', key: spec.key, ok: false,
                      code: e.code || e.name,
                      message: String(e.message).split('\n')[0] });
  }
}

// ---- stage 4: validate ---------------------------------------------------
const validationLog = [];
for (const spec of MODULE_SPECS) {
  if (!validators[spec.key]) {
    validationLog.push({ stage: 'validate', key: spec.key, ok: false, reason: 'no validator' });
    continue;
  }
  const validate = validators[spec.key];
  for (const fx of FIXTURES) {
    const copy = JSON.parse(JSON.stringify(fx.data));
    let ok;
    let errors = [];
    try {
      ok = validate(copy);
      if (!ok) {
        errors = (validate.errors || []).map(function (e) {
          return { keyword: e.keyword, path: e.instancePath || '/' };
        });
      }
    } catch (e) {
      validationLog.push({ stage: 'validate', key: spec.key, fixture: fx.name,
                           thrown: e.name,
                           message: String(e.message).split('\n')[0] });
      continue;
    }
    validationLog.push({
      stage: 'validate', key: spec.key, fixture: fx.name,
      result: ok ? 'VALID' : 'INVALID',
      expected: fx.expect,
      match: (ok && fx.expect === 'VALID') || (!ok && fx.expect === 'INVALID'),
      errorCount: errors.length,
      errors: errors,
    });
  }
}

return [{ json: {
  ajv_root: AJV_ROOT,
  schema_raw_sha: SCHEMA_RAW_SHA,
  schema_canon_sha: SCHEMA_CANON_SHA,
  fixture_shas: FIXTURE_SHAS,
  loadLog: loadLog,
  ctorLog: ctorLog,
  compileLog: compileLog,
  validationLog: validationLog,
} }];
```

#### 6.4.6 Matriks ujian — apa yang dijalankan dan apa yang tidak

Setiap baris menyatakan konteks pelaksanaan. Tiada ujian runtime A7 dijalankan.

| # | Ujian | Konteks | Input | Mutasi / keadaan | Dijangka | Sebenar | Exit | Dijalankan? |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | Stage A semua fail sah | Generator unit test | 5 fail sumber | tiada | 5× `SOURCE_RAW_MATCH` | 5× MATCH | 0 | **YA** |
| 2 | Stage A fail tiada | Generator unit test | baseline hilang | laluan salah | `NOT_VERIFIED` + exit 3 | seperti dijangka | 3 | **YA** |
| 3 | Stage A fail diubah | Generator unit test | t2 + 1 bait | tambah ruang | `SOURCE_RAW_MISMATCH` | seperti dijangka | 2 | **YA** |
| 4 | Manifest L2 salah, L1 betul | Generator unit test | manifest | t1 L2 = `0`×64 | `FATAL layer2 mismatch` | seperti dijangka | 2 | **YA** |
| 5 | Penjanaan berjaya | Generator unit test | manifest sah | tiada | `MARKERS_OK 5`, `SENTINELS_REMAINING 0` | 23,393 bait | 0 | **YA** |
| 6 | Syntax output | `node --check` | `/tmp/a7-v8.js` | tiada | lulus | lulus | 0 | **YA** |
| 7 | Extraction positif | Mock workflow JSON | output penjana | tiada | `RESULT VERIFIED` | VERIFIED | 0 | **YA** |
| 8 | Pemalsuan fixtur bersepadu | Mock workflow JSON | baseline | data + `canon_sha` diubah | `RESULT FAILED` | FAILED `layer2:baseline` | 1 | **YA** |
| 9 | Pemalsuan schema bersepadu | Mock workflow JSON | schema | data + `canon_sha` diubah | `RESULT FAILED` | FAILED `layer2:schema` | 1 | **YA** |
| 10 | Penanda salah objek | Mock workflow JSON | t1 | penanda ditukar | `MARKER_FAILED` | BEGIN count 0 | 1 | **YA** |
| 11 | Penanda duplikat | Mock workflow JSON | t2 | BEGIN ditambah | `MARKER_FAILED` | BEGIN count 2 | 1 | **YA** |
| 12 | Penanda hilang | Mock workflow JSON | t2 | END dibuang | `MARKER_FAILED` | END count 0 | 1 | **YA** |
| 13 | Data diubah, pemalar tidak | Mock workflow JSON | schema | nilai diubah | `RESULT FAILED` | FAILED `layer2:schema` + `declared:schema` | 1 | **YA** |
| 14 | JSON rosak dalam blok | Mock workflow JSON | baseline | `:` dibuang | `LAYER2_CANONICAL_MISMATCH parse_error` | seperti dijangka | 1 | **YA** |
| 15 | `SCHEMA_RAW_SHA` tiada | Mock workflow JSON | schema | pemalar dibuang | `LAYER1_SCHEMA_NOT_VERIFIED` → `RESULT NOT VERIFIED` | seperti dijangka | 3 | **YA** |
| 16 | Manifest tiada | Extractor unit test | — | fail tiada | `FATAL manifest missing` | seperti dijangka | 2 | **YA** |
| 17 | Manifest objek hilang | Extractor unit test | manifest | buang `t2` | `FATAL missing objects` | seperti dijangka | 2 | **YA** |
| 18 | Manifest objek tambahan | Extractor unit test | manifest | tambah `extra` | `FATAL unexpected objects` | seperti dijangka | 2 | **YA** |
| 19 | Manifest hash cacat | Extractor unit test | manifest | `ZZZ` | `FATAL not 64 lowercase hex` | seperti dijangka | 2 | **YA** |
| 20 | Manifest versi salah | Extractor unit test | manifest | `2` | `FATAL manifest_version` | seperti dijangka | 2 | **YA** |
| 21 | Manifest penanda salah | Extractor unit test | manifest | t1→`FIXTURE_t2` | `FATAL marker must be` | seperti dijangka | 2 | **YA** |
| 22 | Manifest kanonikalisasi salah | Extractor unit test | manifest | 3 varian | `FATAL canonicalization` | seperti dijangka | 2 | **YA** |
| 23 | Manifest hash duplikat | Extractor unit test | manifest | t2←t1 | `FATAL duplicate` | seperti dijangka | 2 | **YA** |
| 24 | Penanda bertindih | Mock workflow JSON | schema+t1 | blok t1 dipindah ke dalam blok schema | `MARKER_FAILED overlapping` | `overlapping markers: schema and t1` | 1 | **YA** |
| 25 | Eksport n8n sebenar | Runtime A7 | — | — | — | **TIDAK dijalankan** | — | **TIDAK** |
| 26 | Code node Ajv | Runtime A7 | — | — | — | **TIDAK dijalankan** | — | **TIDAK** |

Ringkasan: **24 ujian dijalankan**, 2 tidak (ujian 25 dan 26, kedua-duanya memerlukan runtime n8n sebenar). Semua yang dijalankan menggunakan fail tempatan dan mock workflow JSON — **bukan** eksport n8n sebenar.

Satu nota kaedah: ujian 11 (penanda duplikat) dan ujian 24 (penanda bertindih) menangkap keadaan berbeza. Ujian 11 mengesan kiraan BEGIN=2. Ujian 24 mengesan blok bersarang tulen dengan kiraan tetap 1/1, yang hanya dapat dikesan oleh semakan pertindihan.

#### 6.4.7 Pengenal dalam output

| Pengenal | Sumber |
| --- | --- |
| `AJV_ROOT` | Pemalar literal, disahkan A4/A6 |
| `MODULE_SPECS` | Tiga entri dengan laluan dan nama eksport |
| `SCHEMA_RAW_SHA` | Lapisan 1, disuntik sebagai rentetan berkuot |
| `SCHEMA_CANON_SHA` | Lapisan 2, dikira oleh penjana |
| `SCHEMA_OBJECT` | Literal, dalam blok penanda `SCHEMA` |
| `FIXTURES` | Empat entri, setiap satu dalam blok penanda sendiri |
| `FIXTURE_SHAS` | Lapisan 2 untuk setiap fixtur |
| `loadLog`, `ctorLog`, `compileLog`, `validationLog` | Tatasusunan hasil |

Tiada pengenal tidak ditentukan.

## 7. Matriks fixtur dan keputusan dijangka

| Fixtur | SHA-256 | Dijangka | Keyword dijangka | Laluan dijangka |
| --- | --- | --- | --- | --- |
| Baseline | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` | VALID | — | — |
| A3-T1 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` | INVALID | `additionalProperties` | `/design/theme` |
| A3-T2 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` | INVALID | `enum` | `/design/theme/font_token` |
| A3-T3 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` | INVALID | `type` | `/site/name` |

Schema: `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21`.

Hash fixtur diambil daripada laporan A3, bukan dijana semula. Fixtur tidak boleh diubah.

### 7.2 Lapisan 1 dan Lapisan 2 — takrifan yang betul

Ini pemisahan yang v3 tidak buat, dan yang v10 salah label untuk fixtur. v11 membetulkan takrifan tanpa mengubah satu pun nilai.

#### 7.2a Schema — dua ukuran yang benar-benar berbeza

| Lapisan | Takrif | Nilai |
| --- | --- | --- |
| **Lapisan 1 — hash bait fail sebenar** | SHA-256 bagi bait tepat fail `canonical-sitespec-schema-v1.1.json` di cakera (3,566 bait, berakhir dengan `\n`, format manual) | `0c184dce…aff21` |
| **Lapisan 2 — hash JSON kanonik** | SHA-256 bagi `json.dumps(obj, indent=2, ensure_ascii=False).encode("utf-8")` **tanpa** baris baru | `ce403cb9…1ce51` |

Bagi schema, Lapisan 1 dan Lapisan 2 mengukur perkara berbeza: bait fail lawan serialisasi kanonik. Fail asal menggunakan format padat (`"required": [...]` pada satu baris), jadi saiznya 3,566 bait manakala serialisasi kanonik 4,713 bait. Membandingkan kedua-duanya sentiasa gagal.

#### 7.2b Empat fixtur — dua bentuk serialisasi objek yang sama

Empat fixtur **tiada fail sumber berdiri sendiri** dalam bukti projek. Nilai "Lapisan 1" mereka bukan hash bait fail. Ia ialah hash serialisasi JSON kanonik **dengan satu baris baru akhir**:

| Lapisan | Takrif | Nilai |
| --- | --- | --- |
| **Lapisan 1 — hash serialisasi fixtur yang diluluskan (JSON kanonik + baris baru akhir)** | SHA-256 bagi `json.dumps(obj, indent=2, ensure_ascii=False) + "\n"` | lihat jadual 7.2c |
| **Lapisan 2 — hash JSON kanonik (tanpa baris baru akhir)** | SHA-256 bagi `json.dumps(obj, indent=2, ensure_ascii=False)` | lihat jadual 7.2c |

Oleh itu, bagi empat fixtur, Lapisan 1 dan Lapisan 2 ialah **dua bentuk serialisasi objek JSON yang sama**, berbeza hanya pada satu bait baris baru di hujung. Ia bukan dua jenis ukuran yang berlainan. Perbezaan panjangnya tepat satu bait:

| Objek | Panjang L1 (kanonik + `\n`) | Panjang L2 (kanonik) | Beza |
| --- | --- | --- | --- |
| baseline | 3,257 | 3,256 | +1 |
| t1 | 3,293 | 3,292 | +1 |
| t2 | 3,256 | 3,255 | +1 |
| t3 | 3,243 | 3,242 | +1 |

**Jangan** menerangkan Lapisan 1 fixtur sebagai "hash bait fail asal". Tiada fail sedemikian. Jika fixtur kemudian disimpan sebagai fail sebenar, hash bait fail itu akan menjadi ukuran ketiga yang berasingan dan mesti tidak dikelirukan dengan nilai yang diluluskan sekarang.

#### 7.2c Jadual nilai yang diluluskan — tidak berubah

| Objek | Lapisan 1 (mengikut takrif 7.2a/7.2b) | Lapisan 2 (JSON kanonik, tanpa `\n`) |
| --- | --- | --- |
| schema | `0c184dce…aff21` (bait fail) | `ce403cb9…1ce51` |
| baseline | `68a816c8…ca4a` (kanonik + `\n`) | `84a1f232…029d` |
| t1 | `52b80727…43f3` (kanonik + `\n`) | `9fc9ba23…afb3` |
| t2 | `45379ba2…8011` (kanonik + `\n`) | `6f6a7cbd…91c9` |
| t3 | `09488320…3051` (kanonik + `\n`) | `e0fbc1bc…a54c` |

Kesepuluh-sepuluh nilai ini kekal sama seperti v10. v11 hanya membetulkan cara ia diterangkan.

**Kesan kepada perbandingan.** Stage A masih mengesahkan lima nilai Lapisan 1 terhadap manifest, dan Stage B masih mengesahkan lima nilai Lapisan 2. Apa yang berubah ialah tafsiran: bagi fixtur, kedua-dua perbandingan itu mengesahkan dua bentuk serialisasi objek yang sama, bukan dua sumber bukti bebas. Bilangan perbandingan tidak berubah: 5 build-time + 11 post-export = 16.

### 7.3 Kaedah serialisasi — dibuktikan untuk kelima-lima objek

v4 mendakwa "kelima-lima objek" tetapi hanya memaparkan empat fixtur. Objek schema tidak ditunjukkan. v5 membetulkannya: kelima-lima diuji dan dipaparkan.

Diuji dalam sesi ini: `JSON.stringify(obj, null, 2)` dalam Node.js berbanding Python `json.dumps(obj, indent=2, ensure_ascii=False)`.

| Objek | Panjang JSON inden-2 | Python SHA-256 | Node reserialize identik |
| --- | --- | --- | --- |
| **schema** | 4,713 | `ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51` | **ya** |
| baseline | 3,257 | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` | **ya** |
| t1 | 3,293 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` | **ya** |
| t2 | 3,256 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` | **ya** |
| t3 | 3,243 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` | **ya** |

Output ujian untuk fixtur:

```text
baseline reserialize_identik: true
t1       reserialize_identik: true
t2       reserialize_identik: true
t3       reserialize_identik: true
```

Output ujian untuk schema:

```text
python sha: ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51
panjang: 4713
node reserialize identik: true
node sha: ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51
```

Ujian yang dijalankan:

1. Python menulis setiap objek dengan `json.dumps(obj, indent=2, ensure_ascii=False)`.
2. Node membaca fail itu, `JSON.parse`, kemudian `JSON.stringify(obj, null, 2)`.
3. Bandingkan rentetan hasil dengan kandungan fail.

Keputusan: **identik** untuk kelima-lima objek. Kaedah serialisasi boleh dipercayai untuk objek-objek ini.

**Nota penting:** hash schema `ce403cb9…` di atas ialah hash **serialisasi JSON**, bukan hash **bait fail**. Hash bait fail kanonik ialah `0c184dce…` (3,566 bait). Kedua-duanya sah untuk tujuan berbeza — lihat 7.2.

**Pemisahan bukti yang mesti dikekalkan:**

| Jenis bukti | Apa yang dibuktikan | Apa yang **tidak** dibuktikan |
| --- | --- | --- |
| Ujian serialisasi (seksyen ini) | Python dan JS menghasilkan JSON yang sama | Bahawa Ajv menerima atau menolak fixtur |
| Validasi Ajv sebenar | Tingkah laku Ajv terhadap fixtur | **TIDAK diuji dalam A7.** Tiada Ajv pada mesin ini. |

Bukti serialisasi **bukan** bukti validasi. Ia hanya membuktikan bahawa dua bahasa menghasilkan rentetan yang sama, yang diperlukan untuk perbandingan hash yang bermakna.

#### 7.3.1 Batas yang diakui

1. Kesetaraan ini diuji untuk **objek SiteSpec** dengan ciri khusus: rentetan ASCII, integer, boolean, tiada nombor titik terapung, tiada aksara kawalan, tiada unikod di luar ASCII.
2. Ia **tidak** dijamin untuk semua objek JSON. Nombor titik terapung dan unikod boleh diserialisasi berbeza antara Python dan JavaScript.
3. Jika fixtur kemudian mengandungi nilai sedemikian, kesetaraan mesti diuji semula.

Status: **VERIFIED** untuk lima objek A7. **UNKNOWN** untuk objek lain.

#### 7.3.2 Mengapa ini menyelesaikan Isu 5

v3 mencetak `fx.sha` dalam output nod. Itu hanya menunjukkan "saya menulis pemalar ini". Ia tidak membuktikan bahawa `fx.data` menghasilkan hash itu.

v4 memisahkan dua soalan:

| Soalan | Dijawab oleh | Boleh dijawab dalam nod? |
| --- | --- | --- |
| Adakah fail sumber sepadan dengan hash yang diluluskan? | `sha256sum` pada hos (7.4.1) | Tidak — nod tiada akses fail |
| Adakah workflow yang dijalankan mengandungi data yang sama? | Eksport dan hash semula (7.4.4) | Tidak — perlu di luar nod |

Nod itu sendiri **tidak boleh** menjawab sama ada. Ia hanya boleh melaporkan bahawa pemalar dalam kod sepadan dengan jangkaan. Itu satu-satunya dakwaan yang boleh dibuat.

### 7.4 Rantaian bukti lengkap

Empat peringkat, setiap satu dengan bukti berasingan.

#### 7.4.1 Peringkat persediaan — pada hos, sebelum workflow dibina

```bash
# Guna fail sumber yang disahkan, bukan salinan tempatan.
sha256sum canonical-sitespec-schema-v1.1.json
# dijangka: 0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21

sha256sum baseline.json t1.json t2.json t3.json
# dijangka: 68a816c8... , 52b80727... , 45379ba2... , 09488320...

# Jika mana-mana TIDAK sepadan, BERHENTI. Jangan bina workflow.
```

Bukti: output `sha256sum` dengan laluan fail dan cap masa.

#### 7.4.2 Peringkat pembinaan — penjana mengesahkan semula

Penjana (6.4.2) mengira semula hash setiap fail input dan **keluar dengan kod 2** jika tidak sepadan. Ia mencetak:

```text
VERIFY schema    <hash> MATCH
VERIFY baseline  <hash> MATCH
VERIFY t1        <hash> MATCH
VERIFY t2        <hash> MATCH
VERIFY t3        <hash> MATCH
WROTE <output>
OUTPUT_SHA <hash nod yang dijana>
OUTPUT_BYTES <n>
PLACEHOLDERS_LEFT 0
```

Bukti: output penjana. `PLACEHOLDERS_LEFT` mesti `0`.

#### 7.4.3 Peringkat dalam nod — hanya kesinambungan

Nod melaporkan `schema_sha_expected` dan `fixture_shas_expected`. Ini **hanya** menyatakan pemalar yang digunakan. Ia **tidak** membuktikan data sepadan. Laporan keputusan mesti melabelnya sebagai "kesinambungan", bukan "pengesahan hash".

#### 7.4.4 Peringkat pasca-larian — prosedur kanonik

v5 mempunyai dua kaedah eksport yang bercanggah. v6 menetapkan **satu** kaedah kanonik dan melabel yang lain sebagai tidak dibenarkan.

##### 7.4.4a Kaedah eksport kanonik

**`--output=-` ke stdout, ditangkap oleh hos.**

```bash
# Langkah 9 (kanonik). Pada hos ujian.
docker exec a7-probe \
  n8n export:workflow --id=<WORKFLOW_ID> --output=- \
  > /tmp/a7-export.json
```

Sebab kaedah ini dipilih: tiada fail dicipta di dalam container, jadi tiada langkah pemindahan berasingan, dan tiada risiko meninggalkan fail dalam container selepas ujian.

**Sahkan eksport sebelum meneruskan:**

```bash
test -s /tmp/a7-export.json \
  || { echo "ABORT: export kosong atau tiada"; exit 1; }

python3 -c 'import json;json.load(open("/tmp/a7-export.json"))' \
  || { echo "ABORT: export bukan JSON sah"; exit 1; }
```

Jika sama ada gagal, **BERHENTI**. Status: **BLOCKED**. Jangan cuba kaedah lain tanpa kelulusan.

##### 7.4.4b Langkah verifikasi — 11 perbandingan post-export

```bash
# Stage B. Manifest mesti datang dari luar, bukan daripada export.
python3 extract-and-verify.py \
  --export /tmp/a7-export.json \
  --expected-manifest expected-hashes.json
```

`--expected-manifest` **wajib**. Skrip ini **tidak** membaca fail sumber; ia hanya membandingkan workflow yang dieksport terhadap manifest.

**Sebelas perbandingan, tiga kategori:**

| Kategori | Bilangan | Sumber jangkaan | Sumber sebenar | Jenis bukti |
| --- | --- | --- | --- | --- |
| Lapisan 2 kanonik | **5** | Manifest (bebas) | Hash dikira semula daripada literal | **Bebas** |
| Lapisan 1 schema | **1** | Manifest (bebas) | `SCHEMA_RAW_SHA` dalam workflow | **Bebas** (deklarasi lawan manifest) |
| Pemalar workflow | **5** | Pemalar dalam workflow | Hash dikira semula | Konsistensi dalaman sahaja |
| | **11** | | | |

**Yang tidak dapat dilakukan oleh Stage B:**

| Dakwaan | Status di Stage B | Sebab |
| --- | --- | --- |
| Bait mentah schema sepadan hash fail | **NOT APPLICABLE** | Skrip tidak membaca fail |
| Bait mentah baseline, t1, t2, t3 sepadan hash | **NOT VERIFIED** | Workflow tidak mengandungi hash mentah fixtur |
| Manifest itu sendiri autoritatif | **NOT VERIFIED** | Stage B hanya membandingkan terhadapnya |

Skrip mencetak `LAYER1_RAW_NOT_VERIFIED` untuk keempat-empat fixtur secara eksplisit, supaya tiada pembaca menyangka ia diperiksa.

**Output yang dijangka apabila semua lulus:**

```text
CODE_NODE A7 Probe
JSCODE_LEN 23391
MANIFEST expected-hashes.json
STAGE post-export
MARKERS_OK 5
LAYER2_CANONICAL_MATCH schema ce403cb9b50fdd2a02bc7482bddf1501294ee59e6b9d47b1eca7eee3ecc1ce51
LAYER2_CANONICAL_MATCH baseline 84a1f232d513e41eb59c1c34ed73743bae26f4be2822b059ff8a08e40cce029d
LAYER2_CANONICAL_MATCH t1 9fc9ba234d3123a5ae3ba8c2fae87677ded42d9ff84c310258d7359762b1afb3
LAYER2_CANONICAL_MATCH t2 6f6a7cbd4afa004a8bb946bf01e91fa3c637b55606ffb85fe347d7b2b6ab91c9
LAYER2_CANONICAL_MATCH t3 e0fbc1bcc5779cd01da17b2f9fae57e714e521db7a7a17fbe4e96573a471a54c
LAYER1_SCHEMA_MATCH 0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21
WORKFLOW_DECLARED_HASH_MATCH schema
WORKFLOW_DECLARED_HASH_MATCH baseline
WORKFLOW_DECLARED_HASH_MATCH t1
WORKFLOW_DECLARED_HASH_MATCH t2
WORKFLOW_DECLARED_HASH_MATCH t3
LAYER1_RAW_NOT_VERIFIED baseline workflow_does_not_embed_raw_hash
LAYER1_RAW_NOT_VERIFIED t1 workflow_does_not_embed_raw_hash
LAYER1_RAW_NOT_VERIFIED t2 workflow_does_not_embed_raw_hash
LAYER1_RAW_NOT_VERIFIED t3 workflow_does_not_embed_raw_hash

RESULT VERIFIED
NOTE build_time_stage_a_required_for_full_chain
```

**Ini output daripada workflow JSON tiruan yang membalut output penjana sebenar**, bukan eksport n8n. Ia membuktikan pengekstrak berfungsi terhadap bentuk yang dijangka.

**Nota tentang `RESULT VERIFIED` di Stage B:** ia bermakna workflow sepadan dengan manifest yang dibekalkan. Ia **tidak** bermakna rantaian penuh lengkap, kerana Stage A tidak dijalankan dalam invokasi yang sama. Baris `NOTE build_time_stage_a_required_for_full_chain` mengingatkan perkara itu.

##### 7.4.4c Keputusan yang dibezakan

| Label | Maksud | Boleh gagal sendiri? |
| --- | --- | --- |
| `LAYER2_CANONICAL_MATCH <name>` | Hash dikira semula sepadan **manifest bebas** | Ya |
| `LAYER2_CANONICAL_MISMATCH <name>` | Tidak sepadan manifest | Ya → `RESULT FAILED` |
| `LAYER1_RAW_MATCH schema` | `SCHEMA_RAW_SHA` dalam workflow sepadan manifest | Ya |
| `WORKFLOW_DECLARED_HASH_MATCH <name>` | Pemalar dalam workflow sepadan hash dikira semula | Ya |
| `RESULT VERIFIED` | **Semua** perbandingan lulus | — |
| `RESULT FAILED reasons=[...]` | Sekurang-kurangnya satu gagal | — |

`RESULT VERIFIED` hanya dikeluarkan apabila:

1. Kelima-lima hash kanonik sepadan manifest bebas.
2. `SCHEMA_RAW_SHA` sepadan manifest.
3. Kelima-lima pemalar workflow sepadan nilai dikira semula.
4. Kelima-lima blok penanda hadir tepat sekali dan dihurai dengan jayanya.

`SELF_CONSISTENT` **tidak** digunakan sebagai hasil keseluruhan. Ia istilah v6 yang tidak membezakan bukti bebas daripada konsistensi dalaman.

##### 7.4.4d Apa yang dibuktikan oleh `RESULT VERIFIED`

| Dakwaan | Betul? | Sebab |
| --- | --- | --- |
| "Workflow mengandungi data yang sepadan dengan manifest bebas yang dibekalkan" | **Betul** | Itu perbandingan 1–6 |
| "Ajv menerima schema dan fixtur" | **Tidak** | Tiada Ajv dalam pengekstrakan |
| "Fail asal tidak berubah sejak manifest dicipta" | **Hanya jika** manifest sendiri disahkan sumbernya | Lihat 7.5 |
| "Manifest itu sendiri betul" | **Tidak** | Pengekstrak hanya membandingkan terhadapnya |

Ini pengesahan **korespondensi kepada manifest**, bukan pengesahan bahawa manifest itu autoritatif. Provenance manifest dibincangkan dalam 7.5.

##### 7.4.4e Ujian negatif wajib — pemalsuan bersepadu

Ini ujian yang v6 **tidak** lakukan, dan yang membuktikan jurang itu ditutup.

**Prosedur:**

1. Mula dengan workflow JSON tiruan yang sah (membalut output penjana).
2. Ubah data literal satu fixtur.
3. Ubah juga pemalar `canon_sha` fixtur itu supaya sepadan dengan data baharu.
4. Jangan ubah manifest.
5. Jalankan pengekstrak.

**Keputusan sebenar yang diperoleh dalam sesi ini:**

```text
LAYER2_CANONICAL_MISMATCH baseline expected=84a1f232d513e41eb59c1c34ed73743bae26f4be2822b059ff8a08e40cce029d actual=91955e0cf1c454fadbcc5bbffc478bb9badc54c3c599e147a9d57c52bc32a4bb
WORKFLOW_DECLARED_HASH_MATCH baseline
RESULT FAILED reasons=['layer2:baseline']
```

Exit code: **1**.

Dua baris pertama mesti dibaca bersama. `WORKFLOW_DECLARED_HASH_MATCH` menunjukkan konsistensi dalaman **lulus** — inilah yang v6 laporkan sebagai kejayaan. `LAYER2_CANONICAL_MISMATCH` menunjukkan perbandingan bebas **menangkap** pemalsuan. Itu perbezaan antara v6 dan v7.

**Ujian yang sama pada schema:**

```text
LAYER2_CANONICAL_MISMATCH schema expected=ce403cb9... actual=90b905b7...
LAYER1_RAW_MATCH schema 0c184dce...
WORKFLOW_DECLARED_HASH_MATCH schema
RESULT FAILED reasons=['layer2:schema']
```

Perhatikan `LAYER1_RAW_MATCH` lulus di sini, kerana pemalar `SCHEMA_RAW_SHA` tidak diubah. Perbandingan lapisan-2 bebas yang menangkapnya.

##### 7.4.4f Ujian negatif lain — semuanya dijalankan

| Ujian | Keputusan | Exit |
| --- | --- | --- |
| Penanda hilang | `FAILED marker FIXTURE_t2: BEGIN=1 END=0` | 1 |
| Penanda berganda | `FAILED marker FIXTURE_t3: BEGIN=2 END=1` | 1 |
| Data diubah, pemalar tidak | `RESULT FAILED reasons=['layer2:schema','declared:schema']` | 1 |
| JSON rosak dalam blok | `FAILED baseline: literal is not valid JSON` | 1 |
| Manifest tiada | `FATAL manifest missing` | 2 |
| Manifest hilang objek | `FATAL manifest missing objects: ['t2']` | 2 |
| Hash format salah | `FATAL ... is not 64 lowercase hex` | 2 |
| Objek tambahan | `FATAL manifest has unexpected objects: ['extra']` | 2 |

Semua ujian ini menggunakan **JSON tiruan**, bukan eksport n8n sebenar. Ia dijalankan dalam sesi ini sebagai semakan peringkat dokumentasi dan kod, bukan sebagai ujian runtime A7.

##### 7.4.4g Kriteria berhenti

| Keadaan | Tindakan |
| --- | --- |
| `export` kosong atau bukan JSON | BERHENTI, BLOCKED |
| Bukan tepat satu nod Code | BERHENTI, lapor kiraan |
| Mana-mana penanda bukan 1/1 | BERHENTI, INCONCLUSIVE |
| Blok bukan JSON sah | BERHENTI, INCONCLUSIVE |
| Mana-mana `LAYER2_CANONICAL_MISMATCH` | BERHENTI, `RESULT FAILED` |
| Mana-mana `LAYER1_RAW_MISMATCH` | BERHENTI, `RESULT FAILED` |
| Mana-mana `WORKFLOW_DECLARED_HASH_MISMATCH` | BERHENTI, `RESULT FAILED` |
| Semua lulus | `RESULT VERIFIED` untuk manifest yang dibekalkan |

##### 7.4.4h Kaedah tidak kanonik — TIDAK DIBENARKAN

Eksport ke fail dalam container kemudian `docker cp`:

```bash
# NON-CANONICAL / NOT AUTHORIZED tanpa kelulusan pemilik berasingan
docker exec a7-probe n8n export:workflow --id=<ID> --output=/tmp/wf.json
docker cp a7-probe:/tmp/wf.json /tmp/a7-export.json
```

**Status: NOT AUTHORIZED** untuk larian A7 standard. Sebab:

1. Ia mencipta fail di dalam container, yang mesti dipadam kemudian — satu lagi langkah pembersihan yang boleh terlepas.
2. Ia memperkenalkan dua kaedah, yang menyebabkan percanggahan v5.
3. Ia tidak menambah keupayaan berbanding kaedah kanonik.

Hanya digunakan jika kaedah kanonik gagal **dan** pemilik meluluskan secara eksplisit. Jika digunakan, ia mesti direkod sebagai penyimpangan dalam laporan keputusan.

##### 7.4.4i Asumsi yang mesti disahkan sebelum larian

| # | Asumsi | Status | Kesan jika salah |
| --- | --- | --- | --- |
| 1 | `--output=-` menulis JSON ke stdout dalam 2.43.8 | **UNVERIFIED** | ABORT pada langkah 9; status BLOCKED |
| 2 | Struktur eksport mengandungi `nodes[].parameters.jsCode` | **UNVERIFIED** | Pengekstrak gagal: `no nodes array` atau `jsCode kosong` |
| 3 | Penanda `/* A7:LITERAL:... */` kekal melalui import dan eksport | **UNVERIFIED** | Pengekstrak gagal: `count BEGIN=0` |
| 4 | `json.dumps(obj, indent=2, ensure_ascii=False)` menghasilkan semula literal | **VERIFIED** untuk objek ini (7.3) | — |

Asumsi 1–3 hanya boleh disahkan oleh larian yang dibenarkan. Jika mana-mana gagal, rantaian bukti **tidak lengkap** dan status **INCONCLUSIVE**.

#### 7.4.5 Apa yang dibuktikan setiap peringkat

| Peringkat | Membuktikan | Tidak membuktikan |
| --- | --- | --- |
| 7.4.1 | Fail pada hos sepadan hash | Nod menggunakan fail itu |
| 7.4.2 | Nod dijana daripada fail yang disahkan | Tiada ralat semasa pembinaan |
| 7.4.3 | Pemalar dalam kod sepadan jangkaan | Data menghasilkan hash itu |
| 7.4.4 | Data yang **dijalankan** sepadan hash | — |

Hanya **7.4.1 dan 7.4.4** bersama membuktikan rantaian penuh.

#### 7.4.6 Jika 7.4.4 tidak dapat dijalankan

Jika `export:workflow` gagal atau pengekstrakan tidak dapat dilakukan:

1. Rantaian bukti **tidak lengkap**.
2. Keputusan boleh dilaporkan sebagai **tingkah laku sahaja**, bukan bukti fixtur yang diluluskan telah diuji.
3. Status mesti **INCONCLUSIVE** untuk soalan pematuhan fixtur.
4. Ini mesti ditulis dalam laporan keputusan, bukan disembunyikan.

### 7.4a Provenance dan batasan manifest

Manifest bebas hanya berguna jika manifest itu sendiri boleh dipercayai. Bahagian ini menjelaskan asalnya, dan apa yang **belum** dibuktikan.

#### 7.4a.1 Asal setiap nilai

| Objek | Nilai | Asal yang didakwa | Status provenance |
| --- | --- | --- | --- |
| schema L1 | `0c184dce…` | **Bait fail sebenar** `canonical-sitespec-schema-v1.1.json`, 3,566 bait | **Disahkan** melalui `sha256sum`; fail wujud dalam simpanan projek |
| baseline L1 | `68a816c8…` | **JSON kanonik + `\n`** bagi payload execution 80 (tiada fail fixtur) | Diterbitkan semula; versi workflow `c413686f…` **VERIFIED** melalui `$.workflowVersionId` (sumber: rekod API dibekalkan pemilik); **fixtur rasmi W1 kekal NOT VERIFIED** |
| t1 L1 | `52b80727…` | **JSON kanonik + `\n`** bagi mutasi A3-T1 (tiada fail fixtur) | Diterbitkan semula daripada mutasi yang didokumenkan dalam pelan A3 |
| t2 L1 | `45379ba2…` | **JSON kanonik + `\n`** bagi mutasi A3-T2 (tiada fail fixtur) | Diterbitkan semula daripada mutasi yang didokumenkan dalam pelan A3 |
| t3 L1 | `09488320…` | **JSON kanonik + `\n`** bagi mutasi A3-T3 (tiada fail fixtur) | Diterbitkan semula daripada mutasi yang didokumenkan dalam pelan A3 |
| schema L2 | `ce403cb9…` | Serialisasi kanonik schema | Dikira dalam sesi v5 dan v6, disahkan semula dalam v7 |
| baseline L2 | `84a1f232…` | Serialisasi kanonik baseline | Dikira dalam sesi v6 |
| t1 L2 | `9fc9ba23…` | Serialisasi kanonik t1 | Dikira dalam sesi v6 |
| t2 L2 | `6f6a7cbd…` | Serialisasi kanonik t2 | Dikira dalam sesi v6 |
| t3 L2 | `e0fbc1bc…` | Serialisasi kanonik t3 | Dikira dalam sesi v6 |

#### 7.4a.2 Dua lapisan mengukur perkara berbeza — tetapi tidak sama bagi semua objek

**Schema:** Lapisan 1 mengukur **bait fail sebenar**. Lapisan 2 mengukur **serialisasi JSON kanonik**. Ia berbeza walaupun objeknya sama:

| Objek | L1 bait | L2 bait | Beza |
| --- | --- | --- | --- |
| schema | 3,566 | 4,713 | 1,147 |
| baseline | 3,257 | 3,256 | −1 |

Schema L2 lebih besar kerana fail asal menggunakan pemformatan padat, manakala serialisasi kanonik menggunakan inden 2.

**Fixtur:** tiada fail. Lapisan 1 dan Lapisan 2 ialah dua serialisasi objek yang sama, berbeza satu bait baris baru. Baseline L2 lebih kecil daripada baseline L1 tepat satu bait kerana sebab itu (3,256 lawan 3,257).

**Kesimpulan yang betul:** bagi schema, membandingkan L1 dengan L2 sentiasa gagal walaupun data tidak berubah. Bagi fixtur, L1 dan L2 **tidak boleh** dibandingkan sebagai dua sumber bukti bebas, kerana kedua-duanya terbit daripada objek yang sama; hanya bentuk serialisasi berbeza. v10 menyatakan peraturan ini terlalu umum; v11 membetulkannya.

#### 7.4a.3 Bagaimana manifest dipelihara secara berasingan

| Keperluan | Cara |
| --- | --- |
| Tidak dibaca daripada workflow | Pengekstrak tiada laluan kod yang membaca nilai jangkaan daripada `--export` |
| Disimpan di luar container | Fail pada hos, bukan dalam volume n8n |
| Dihantar secara eksplisit | Argumen `--expected-manifest` wajib |
| Tidak dijana semasa verifikasi | Dicipta sebelum larian, disemak dengan mata manusia |
| Boleh diaudit | Format JSON mudah, setiap entri ada medan `source` |

#### 7.4a.4 Apa yang **belum** dibuktikan tentang provenance

| Perkara | Status |
| --- | --- |
| Nilai L1 schema diterbitkan semula daripada fail sebenar | **YA** — `sha256sum` pada `docs/canonical-sitespec-schema-v1.1.json` dalam semakan v10 |
| Nilai L1 fixtur diterbitkan semula daripada fail asal | **TIDAK BERKENAAN** — tiada fail fixtur wujud. Nilai ialah hash serialisasi kanonik + `\n`, dan telah diterbitkan semula daripada objek yang dibina semula |
| Payload baseline ialah fixtur W1 rasmi | **TIDAK** — ia payload execution 80; identiti W1 kekal NOT VERIFIED |
| Manifest telah diluluskan pemilik | **TIDAK** — status `PENDING_OWNER_CONFIRMATION` dalam manifest |
| Nilai L2 boleh diterbitkan semula oleh pihak ketiga | **YA**, jika mereka mempunyai objek JSON dan menggunakan konvensyen 7.2/7.3 |
| Pilihan `ensure_ascii` boleh dibuktikan daripada hash | **TIDAK** — kelima-lima objek tiada aksara bukan ASCII, jadi `True` dan `False` menghasilkan hash yang sama |
| Proses penjanaan asal (skrip) disimpan | **TIDAK** — kaedah penjanaan awal ialah inferens daripada hash, bukan artifak skrip |

**Penting:** menulis nilai ke dalam manifest **tidak** mewujudkan provenance bebas. Manifest hanya memindahkan kebergantungan: jika nilai dalam manifest salah, semua perbandingan akan "lulus" terhadap nilai yang salah itu.

#### 7.4a.6 Status kustodi manifest selepas semakan v10

| Perkara | Dapatan |
| --- | --- |
| Fail manifest berdiri sendiri dalam projek | **Tiada.** Kandungan manifest hanya terbenam dalam dokumen v10 (dan kini v11) |
| Kelulusan pemilik | **PENDING_OWNER_CONFIRMATION** — tiada rekod kelulusan bertulis |
| Salinan lapuk | `/tmp/expected-hashes.json` menggunakan rentetan konvensyen v8 yang **ditolak** oleh pengekstrak v11. Ia bukan artifak projek dan mesti tidak digunakan |
| Checksum manifest | Tiada. Perubahan senyap tidak dapat dikesan |
| Hash fixtur sebagai fail | Tiada. Fixtur tidak wujud sebagai fail boleh-di-hash |

Mencipta v11 **tidak** mewujudkan kelulusan pemilik dan **tidak** mencipta manifest berdiri sendiri. Untuk menutup jurang ini, pemilik perlu menyediakan fail manifest terkawal dengan checksum, dan mengesahkan nilai secara bertulis.

### 7.4a.7 Rantaian provenance execution #80 — penunjuk versi langsung

Rantaian penuh, dengan atribusi sumber bagi setiap pautan:

```text
W1 Workflow            id fg1kGKB6ciYADxu9  ("VividAI - Design Agent")
      |
      |  workflowVersionId = c413686f-5ef0-452f-9b55-f1d9075455de
      |  (medan peringkat akar, $.workflowVersionId; sumber: rekod API dibekalkan pemilik)
      v
Saved Workflow Version  c413686f-5ef0-452f-9b55-f1d9075455de
      |                 created 2026-11-13 11:25:31 MYT, 52 min before the run
      |                 only saved version (count: 1); current versionId = activeVersionId
      v
Execution #80           status success · mode webhook
      |                 started 2026-11-13T04:17:54.989Z
      |                 finished 2026-11-13T04:18:11.423Z
      |                 lastNodeExecuted "Respond Success"
      v
Returned SiteSpec       post-validator finalSpec inside response.sitespec
      |                 candidate #8B4513 -> returned #8b4513 (validator lowercases)
      v
Reconstructed A7 Baseline
                        L2 = sha256(canon, no newline)     = 84a1f232…  (approved)
                        L1 = sha256(canon + "\n")        = 68a816c8…  (approved)
```

| Pautan | Klasifikasi | Asas |
| --- | --- | --- |
| W1 workflow wujud dan aktif | **VERIFIED** | `internal/sitespec-historical-version.md`: workflow id, `count: 1`, `activeVersionId` = snapshot |
| Penunjuk versi langsung pada execution #80 | **VERIFIED** (rekod pemilik) | `$.workflowVersionId` = `c413686f…`; **tidak** disahkan bebas oleh Cursor (lihat 0x.1) |
| Execution #80 berjaya, mod webhook | **VERIFIED** | Rekod pemilik **dan** bacaan Cursor bebas (id, workflowId, mode, status, cap masa sepadan) |
| Baseline dibina semula daripada keluaran #80 | **VERIFIED** | `internal/sitespec-schema-discovery.md` §4; hash diterbitkan semula |
| Execution #80 ialah fixtur rasmi W1 | **NOT VERIFIED** | Tiada definisi fixtur, tiada kriteria penerimaan, tiada kelulusan pemilik |

**Had yang dikekalkan.** Bukti langsung ini mengukuhkan atribusi **versi workflow**. Ia tidak mengubah apa-apa tentang status fixtur rasmi. W1 sebagai *label workflow* dan W1 sebagai *fixtur yang diluluskan* ialah dua perkara berbeza; v12 hanya membetulkan yang pertama.

#### 7.4a.5 Siapa yang boleh mengubah nilai jangkaan

| Perubahan | Siapa |
| --- | --- |
| Nilai hash untuk fixtur sedia ada | **Pemilik projek sahaja**, dengan justifikasi bertulis |
| Fixtur baharu | **Pemilik projek**, melalui keputusan pemilik baharu |
| Konvensyen kanonikalisasi | **Pemilik projek**; membatalkan semua nilai L2 sedia ada |
| Tarikh luput manifest | Tiada; manifest kekal sehingga diubah secara eksplisit |

Sesiapa yang boleh mengubah manifest boleh membuat pemalsuan lulus. Oleh itu manifest mesti dianggap sebagai **artefak terkawal**, disimpan bersama dokumen kelulusan, bukan sebagai fail kerja.

### 7.5 Konstruktor yang akan diuji

**Kekal terbuka.** Ini dependency yang mesti diselesaikan sebelum pelaksanaan.

A6 membuktikan setiap konstruktor menerima satu URI dialek sahaja. Cadangan untuk ujian:

| Konstruktor | Bila digunakan |
| --- | --- |
| `Ajv2020` | Jika 2020-12 dipilih, atau sebagai konstruktor lalai untuk ujian kerana Opis juga lalai 2020-12 |
| `Ajv` (draft-07) | Jika Draft-07 dipilih |
| Ketiga-tiga | Jika pemilik mahu bukti bahawa ketiga-tiganya berfungsi dari Code node sebelum memilih |

**Cadangan neutral:** uji ketiga-tiga dalam satu larian. Ia tidak memilih dialek; ia mengumpul bukti bahawa mekanisme import berfungsi tanpa mengira konstruktor. Kos tambahan hampir sifar kerana ketiga-tiga berada dalam pakej yang sama.

**Dependency yang tidak boleh direka:** jika pemilik memilih dialek sebelum A7, ujian boleh dihadkan kepada satu konstruktor. Jika tidak, uji ketiga-tiga dan biarkan dialek terbuka. A7 **tidak** memilih.

### 7.6 Jadual keputusan mengikut peringkat

Jadual ini menggantikan sebarang ringkasan satu baris. Setiap sel mesti diisi secara berasingan dalam laporan keputusan nanti.

| Konstruktor | Peringkat 1: import modul | Peringkat 2: instansiasi | Peringkat 3: kompil | Peringkat 4: 4 fixtur |
| --- | --- | --- | --- | --- |
| `Ajv` (draft-07) | ? | ? | ? | ? |
| `Ajv2019` | ? | ? | ? | ? |
| `Ajv2020` | ? | ? | ? | ? |

`?` bermaksud **belum diukur dalam konteks Code node**. Nilai ini sudah diukur dalam konteks proses Node container (A6), tetapi A6 **tidak** membuktikan Code node berkelakuan sama.

## 8. Keperluan konfigurasi dan pakej

### 8.0 Arkitektur task runner dan pembekalan allowlist

v2 menanda mekanisme allowlist sebagai **UNKNOWN**. v3 **menyelesaikannya** melalui bacaan kod n8n dalam container.

**Proses yang menjalankan Code node:**

| PID | Induk | Arahan | Peranan |
| --- | --- | --- | --- |
| 1 | — | `tini -- /docker-entrypoint.sh` | init |
| 7 | 1 | `n8n` | proses utama n8n |
| 19 | **7** | `n8n task-runner` | **proses yang menjalankan Code node** |

**Persekitaran proses runner (PID 19), nama sahaja:** `GENERIC_TIMEZONE`, `HOME`, `NODE_PATH`, `PATH`.

**Bukti yang menyelesaikan mekanisme:**

Fail `/usr/local/lib/node_modules/n8n/dist/task-runners/task-runner-process-js.js` membina environment proses anak secara eksplisit. Dua fakta daripada kod itu:

1. Konstruktor kelas ini mengandungi `(0, strict_1.default)(this.isInternal, ...)` — kelas ini **hanya** digunakan dalam mod `internal`.
2. `getProcessEnvVars()` menyalin pemboleh ubah ibu bapa secara eksplisit:

```javascript
getProcessEnvVars(grantToken, taskBrokerUri, runnerId) {
    const envVars = Object.assign(Object.create(null), {
        PATH: process.env.PATH,
        HOME: process.env.HOME ?? process.env.USERPROFILE,
        NODE_PATH: process.env.NODE_PATH,
        GENERIC_TIMEZONE: process.env.GENERIC_TIMEZONE,
        NODE_FUNCTION_ALLOW_BUILTIN: process.env.NODE_FUNCTION_ALLOW_BUILTIN,
        NODE_FUNCTION_ALLOW_EXTERNAL: process.env.NODE_FUNCTION_ALLOW_EXTERNAL,
        // ...
```

**Kesimpulan yang kini dapat disahkan:**

Dalam mod `internal`, `NODE_FUNCTION_ALLOW_EXTERNAL` **diwarisi terus** daripada environment proses induk n8n. Ia dibaca daripada `process.env` n8n, bukan daripada fail konfigurasi launcher.

Ini **membetulkan** andaian v2. v2 menyebut dokumentasi yang berkata pemboleh ubah itu mesti dalam `/etc/n8n-task-runners.json`. Dokumen itu betul untuk mod `external`, tetapi **tidak terpakai** di sini kerana:

1. `/etc/n8n-task-runners.json` **tidak wujud** — disahkan.
2. `/etc/task-runners.json` (fail launcher) **tidak wujud** — disahkan.
3. Tiada fail `*task-runner*` dalam `/etc` atau `/usr/local` — disahkan.
4. Kod menunjukkan mod `internal` menggunakan pewarisan environment, bukan launcher.

**Kesan praktikal:** menetapkan `NODE_FUNCTION_ALLOW_EXTERNAL` sebagai pemboleh ubah persekitaran container **akan** sampai kepada proses runner dalam mod `internal`. Ini bertentangan dengan apa yang v2 andaikan.

**Tetapi** ini tidak menyelesaikan keseluruhan masalah. Dua perkara kekal:

1. Dokumentasi n8n menyatakan allowlist membenarkan modul **"from `n8n/node_modules`"**. Ajv berada di `.pnpm`, bukan di `n8n/node_modules`. Sama ada allowlist menerima laluan `.pnpm` **tidak disahkan**.
2. `require('ajv')` gagal walaupun dengan `NODE_PATH` runner, dan `NODE_PATH` pun tidak membantu. Jadi allowlist sahaja mungkin tidak mencukupi; laluan mutlak atau symlink mungkin diperlukan.

**Status komponen:**

| Perkara | Status |
| --- | --- |
| Mekanisme pewarisan allowlist dalam mod `internal` | **VERIFIED** daripada kod |
| Sama ada allowlist membenarkan laluan `.pnpm` | **UNKNOWN / NEEDS VERIFICATION** |
| Sama ada laluan mutlak melepasi allowlist | **UNKNOWN / NEEDS VERIFICATION** |
| Sama ada symlink diperlukan | **UNKNOWN / NEEDS VERIFICATION** |

**Bukti yang masih diperlukan:** satu larian ujian import dalam container terasing dengan `NODE_FUNCTION_ALLOW_EXTERNAL` ditetapkan, merekod `e.code` bagi empat percubaan import. Itu tepat perkara yang A7 cadangkan. Tanpa larian itu, tiga perkara **UNKNOWN** di atas kekal.

### 8.1 Senarai perubahan yang mungkin diperlukan

| # | Perkara | Klasifikasi | Nota |
| --- | --- | --- | --- |
| 1 | Imej n8n dengan digest dipin | **REQUIRED** | `sha256:a18edcf0…`, bukan `latest` |
| 2 | Node.js `v26.7.0` | **NOT REQUIRED** sebagai tindakan | Datang bersama imej |
| 3 | Ajv `8.20.0` tersedia | **NOT REQUIRED** sebagai tindakan | Sudah dalam imej |
| 4 | `NODE_FUNCTION_ALLOW_EXTERNAL=ajv` | **REQUIRED** untuk menguji kebenaran allowlist | Tanpa ini, ujian akan gagal atas sebab kebenaran |
| 5 | `NODE_FUNCTION_ALLOW_BUILTIN` | **NOT REQUIRED** | Nod ujian tidak mengimport modul builtin. Schema dan fixtur dibenamkan sebagai literal. |
| 6 | Resolusi pnpm / symlink | **UNKNOWN / NEEDS VERIFICATION** | Tergantung keputusan percubaan `require('ajv')`. Jika allowlist menerima nama pakej tetapi resolusi gagal, laluan mutlak atau symlink diperlukan. **Symlink ialah perubahan `node_modules`.** |
| 7 | Pemboleh ubah persekitaran lain | **NOT REQUIRED** | Konfigurasi minimum cukup |
| 8 | Restart container | **NOT REQUIRED** dalam urutan yang didokumenkan | Lihat 8.1.1. Container dicipta **dengan** pemboleh ubah sudah ditetapkan. |
| 9 | Penciptaan workflow | **REQUIRED** | Satu workflow ujian, bukan produksi |
| 10 | Pembersihan workflow | **REQUIRED** | Melalui padam container + volume (11.1) |
| 11 | Volume atau fail | **OPTIONAL** | Boleh dielakkan sepenuhnya dengan data dibenamkan |

#### 8.1.1 Arahan restart — penjelasan

v4 mengandungi percanggahan: item 8 berkata restart **REQUIRED** selepas menetapkan pemboleh ubah, tetapi senarai semak mencipta container **dengan** pemboleh ubah sudah ditetapkan. Kedua-duanya tidak konsisten.

Kebenaran untuk urutan yang didokumenkan:

| Urutan | Pemboleh ubah ditetapkan bila | Restart diperlukan? |
| --- | --- | --- |
| **A. Seperti dalam senarai semak v5** — `docker run -e NODE_FUNCTION_ALLOW_EXTERNAL=ajv …` | **Sebelum** container dicipta | **TIDAK** — tiada apa-apa untuk dimulakan semula |
| B. Container sudah berjalan, kemudian `docker exec` untuk tetapkan | Tidak boleh — `docker exec` tidak mengubah environment proses yang berjalan | **TIDAK BERKESAN** — mesti recreate |
| C. Container berjalan, kemudian ubah konfigurasi compose atau `docker run` semula | Selepas | **YA** — recreate container, bukan restart |

**Untuk A7, urutan A digunakan.** Container dicipta sekali dengan pemboleh ubah sudah ada. Tiada restart, tiada recreate dalam fasa persediaan.

**Satu pengecualian yang perlu diperhatikan:** jika semasa larian ternyata allowlist perlu diubah (contohnya daripada `ajv` kepada laluan lain), itu **bukan** restart — ia **recreate** container dengan pemboleh ubah berbeza. Itu tindakan berasingan yang memerlukan kelulusan pemilik, dan ia membatalkan kesinambungan larian sebelumnya kecuali payload dan skrip dibina semula.

Nota teknikal: `docker exec` tidak boleh mengubah environment proses yang sudah berjalan. Menetapkan pemboleh ubah dengan `docker exec -e` hanya memberi kesan kepada proses `exec` itu sendiri, bukan kepada proses n8n. Ini sebabnya urutan B tidak berfungsi.

### 8.2 Satu perkara yang perlu dijelaskan

`NODE_FUNCTION_ALLOW_EXTERNAL` membenarkan import modul **dari `n8n/node_modules`**. Ajv tidak berada di situ; ia berada di `.pnpm` bersarang. Jadi menetapkan allowlist sahaja **mungkin tidak mencukupi**.

Ini **INFERRED** daripada gabungan A4 (`require('ajv')` gagal, tiada symlink) dan dokumentasi n8n ("external modules (from `n8n/node_modules`)"). Ia belum diuji.

Kesan praktikal: A7 mungkin menemui bahawa allowlist mesti ditetapkan **dan** laluan modul mesti boleh diselesaikan. Jika demikian, itu perubahan `node_modules` yang lebih invasif daripada satu pemboleh ubah, dan ia memerlukan kelulusan pemilik yang berasingan.

### 8.3 Apa yang TIDAK dibenarkan dalam A7

Pemasangan pakej pada produksi. Perubahan `node_modules` produksi. Perubahan konfigurasi produksi. Lihat seksyen 6 tugasan dan seksyen 12 laporan ini.

## 9. Risiko dan batasan

| Risiko | Kesan | Mitigasi |
| --- | --- | --- |
| Imej `latest` bergerak | Ujian mengukur versi berbeza daripada produksi | Pin digest `sha256:a18edcf0…` |
| Allowlist tidak mencukupi kerana pnpm | Ujian gagal walaupun konfigurasi betul | Uji tiga laluan import; rekod `e.code` |
| Keperluan symlink | Perubahan `node_modules` yang lebih invasif | Kenal pasti sebelum pelaksanaan; minta kelulusan berasingan |
| Ujian pada hos produksi | Kesan kependaman | Gunakan hos Coolify, bukan hos produksi |
| Ujian membuktikan bukan-produksi sahaja | Tidak boleh mendakwa produksi berfungsi | Nyatakan batas ini dalam laporan keputusan |
| Data fixtur diubah | Keputusan tidak bermakna | Semak hash sebelum dan selepas |

### 9.1 Batasan yang mesti dinyatakan dalam laporan keputusan nanti

1. Ujian pada hos bukan produksi membuktikan imej itu berkelakuan begitu, bukan produksi.
2. Jika allowlist ditetapkan dalam ujian tetapi tidak dalam produksi, hasil ujian **tidak** boleh dipindahkan kepada produksi tanpa perubahan yang setara.
3. Ujian ini mengesahkan empat peraturan struktur sahaja. OD-1 hingga OD-12 kekal tidak diuji.
4. Dialek kekal tidak dipilih oleh A7.

## 10. Senarai semak pelaksanaan untuk larian yang dibenarkan kemudian

Setiap langkah memerlukan kebenaran pemilik yang berasingan. Tiada satu pun dijalankan.

| # | Langkah | Kaedah | Perlu kelulusan |
| --- | --- | --- | --- |
| 0 | Sahkan hash bait schema dan empat fixtur pada hos | `sha256sum` (**7.4.1**, lapisan 1) | Tidak |
| 0a | Sahkan manifest bebas sepadan nilai yang diluluskan | Banding dengan manifest (**6.4.3**) | Tidak |
| 1 | Pilih hos untuk ujian (cadangan: Coolify) | — | **YA** |
| 2 | Tarik imej dengan digest `sha256:a18edcf0…` | `docker pull` (perlu rangkaian) | **YA** |
| 3 | Cipta container ujian: `--network none`, volume baharu, `NODE_FUNCTION_ALLOW_EXTERNAL=ajv` | `docker run` | **YA** |
| 4 | Tunggu container sihat; sahkan versi n8n, Node, Ajv | `docker exec` baca sahaja | Tidak |
| 5 | Bina workflow JSON daripada fail yang disahkan | Penjana (**6.4.2**) | Tidak |
| 6 | Salin masuk dan import: `docker cp` + `n8n import:workflow --input=…` | CLI, **tiada rangkaian** | **YA** |
| 7 | Jalankan: `docker exec … n8n execute --id=<id> --rawOutput` | CLI, **tiada rangkaian** | **YA** |
| 8 | Baca output empat log: load, ctor, compile, validate | stdout | Tidak |
| 9 | Eksport workflow — **kaedah kanonik sahaja** | `--output=-` ke stdout (**7.4.4a**) | Tidak |
| 10 | Sahkan hash pasca-larian terhadap **manifest bebas** | Pengekstrak (**7.4.4b**) | Tidak |
| 11 | Rekod output penuh sebagai bukti, termasuk `RESULT VERIFIED`/`FAILED` | — | Tidak |
| 12 | Padam container **dan volumenya** (ini memadam workflow) | `docker rm -f` + `docker volume rm` | **YA** |
| 13 | Pilih: kekalkan atau padam imej 1.8 GB | `docker rmi` | **YA** |

**Nota konsistensi:**

1. Langkah 0 menggunakan **lapisan 1** (bait fail). Langkah 10 menggunakan **lapisan 2** (JSON kanonik) terhadap manifest. Ia tidak dicampur.
2. Langkah 0a memastikan manifest sendiri sepadan dengan nilai yang diluluskan **sebelum** ia digunakan. Tanpanya, perbandingan langkah 10 hanya mengesahkan konsistensi terhadap nilai yang mungkin salah.
3. Langkah 9 menggunakan kaedah kanonik `--output=-`. Kaedah `docker cp` dalam 7.4.4h ialah **NOT AUTHORIZED**.
4. Langkah 6 dan 7 berbeza: 6 mengimport, 7 menjalankan. Probe `require()` hanya pada langkah 7.
5. Langkah 12 memadam workflow melalui padam volume; tiada arahan CLI padam workflow.

Nota: tiada langkah menggunakan UI n8n, tiada port diterbitkan, dan tiada langkah memerlukan container mempunyai rangkaian. Ini konsisten dengan `--network none` dalam langkah 3.

Langkah 12 memadam workflow secara berkesan kerana container ujian mempunyai volume SQLite sendiri (5.3.4). Tiada arahan CLI padam workflow diperlukan.

### 10.1 Kriteria lulus dan gagal

| Keadaan | Tafsiran |
| --- | --- |
| Sekurang-kurangnya satu import berjaya, instansiasi berjaya, kompil berjaya, dan empat fixtur memberi keputusan dijangka | **PASS** — Ajv boleh digunakan dari Code node apabila dikonfigurasikan |
| Import berjaya tetapi instansiasi gagal | **PARTIAL** — modul dimuatkan, konstruktor tidak boleh digunakan. Rekod `e.code`. |
| Import dan instansiasi berjaya tetapi kompil gagal | **PARTIAL** — rekod mesej ralat kompil penuh |
| Kompil berjaya tetapi keputusan fixtur salah | **FAIL** — masalah konfigurasi konstruktor atau schema |
| Semua import gagal `MODULE_NOT_FOUND` | **FAIL** — allowlist atau resolusi menghalang; rekod `e.code` |
| Semua import gagal dengan ralat kebenaran berbeza | **INCONCLUSIVE** — mekanisme Code node menyekat `require`; perlu siasatan berasingan |
| Hash fixtur tidak sepadan | **ABORT** — ujian tidak sah |

### 10.2 Perkara yang mesti diselesaikan sebelum langkah 1

| # | Perkara | Status | Diselesaikan oleh |
| --- | --- | --- | --- |
| 1 | Mekanisme allowlist dalam mod `internal` | **VERIFIED** — diwarisi daripada `process.env` n8n (8.0) | Selesai |
| 2 | Sama ada allowlist membenarkan laluan `.pnpm` | **UNKNOWN / NEEDS VERIFICATION** | **Langkah 7** (pelaksanaan Code node) |
| 3 | Sama ada laluan mutlak melepasi allowlist | **UNKNOWN / NEEDS VERIFICATION** | **Langkah 7** |
| 4 | Sama ada symlink `node_modules` diperlukan | **UNKNOWN** — bergantung #3 | Keputusan pemilik selepas langkah 7 |
| 5 | Dialek yang diuji | **TERBUKA** | Keputusan pemilik, atau uji ketiga-tiga |
| 6 | Kaedah pengesahan fixtur pasca-larian | **PROPOSED** — 7.4 | Langkah 9 dan 10 |

### 10.3 Kaedah pengesahan fixtur pasca-larian

| Titik berhenti | Syarat untuk meneruskan | Langkah |
| --- | --- | --- |
| Selepas imej ditarik | Digest sepadan `sha256:a18edcf0…` | 2 |
| Selepas container dicipta | Container melaporkan n8n 2.43.8 dan Node v26.7.0 | 4 |
| Selepas **import** workflow | Workflow id diperoleh; tiada ralat import | 6 |
| **Selepas pelaksanaan Code node** — ini titik probe `require()` | Sekurang-kurangnya satu import berjaya. Jika kesemuanya `MODULE_NOT_FOUND`, **BERHENTI** dan laporkan BLOCKED dengan `e.code` setiap percubaan. Jangan cuba symlink tanpa kelulusan. | **7** |
| Selepas hash pasca-larian | Hash sepadan kaedah 7.4. Jika tidak, **BATAL** keputusan. | 10 |

**Penting:** probe `require()` hanya berlaku pada **langkah 7**, apabila `n8n execute` menjalankan Code node. Langkah 6 hanya mengimport workflow ke pangkalan data; tiada kod dijalankan pada langkah itu. Jika larian berhenti pada langkah 7, langkah 9 dan 10 tidak dijalankan, dan rantaian bukti hash tidak lengkap — itu mesti dilaporkan, bukan disembunyikan.

## 11. Pembersihan dan pemeliharaan bukti

### 11.1 Pembersihan — satu kaedah sahaja

Workflow ujian **tidak** dipadam melalui UI atau API. Tiada UI dan tiada API digunakan dalam A7. Pembersihan berlaku dengan memadam container ujian dan volume SQLite khususnya.

#### 11.1.1 Nama sumber yang digunakan

| Sumber | Nama | Nota |
| --- | --- | --- |
| Container ujian | `a7-probe` | Nama tetap, unik |
| Volume ujian | `a7-probe-data` | Nama tetap, unik |
| Imej | `docker.n8n.io/n8nio/n8n@sha256:a18edcf0…` | Dipin mengikut digest |

Nama ini dipilih supaya tiada peluang pertembungan dengan `n8n-n8n-1`, `n8n_n8n_data`, atau mana-mana container Coolify.

#### 11.1.2 Urutan pembersihan yang tepat

```bash
# 1. Sahkan container ujian wujud dan ialah container yang betul.
docker ps -a --filter name=^a7-probe$ --format '{{.Names}} {{.ID}} {{.Image}}'

# 2. Sahkan volume ujian wujud dan bukan volume produksi.
docker volume ls --filter name=^a7-probe-data$ --format '{{.Name}}'

# 3. BERHENTI jika mana-mana nama tidak sepadan tepat.
#    Jangan teruskan dengan padam jika output kosong atau berbeza.

# 4. Padam container.
docker rm -f a7-probe

# 5. Padam volume SQLite ujian. Ini yang menghapuskan workflow.
docker volume rm a7-probe-data

# 6. Sahkan kedua-duanya tiada.
docker ps -a --filter name=^a7-probe$ --format '{{.Names}}'    # mesti kosong
docker volume ls --filter name=^a7-probe-data$ --format '{{.Name}}'  # mesti kosong

# 7. Pilihan: padam imej jika tidak digunakan semula.
docker rmi docker.n8n.io/n8nio/n8n@sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c
```

#### 11.1.3 Perlindungan sumber produksi

Peraturan berikut mesti dipatuhi tanpa pengecualian:

| Larangan | Sebab |
| --- | --- |
| Jangan jalankan `docker rm` tanpa `--filter name=^a7-probe$` yang disahkan | Elak padam container lain secara tidak sengaja |
| Jangan jalankan `docker volume rm` pada `n8n_n8n_data` | Itu volume n8n produksi |
| Jangan jalankan `docker system prune` | Ia memadam sumber yang tidak berkaitan |
| Jangan gunakan `-a` atau wildcard pada `docker rm`/`docker volume rm` | Tiada padam pukal |
| Jangan sentuh `n8n-n8n-1` atau container Coolify | Produksi dan staging |

Jika langkah 1 atau 2 menghasilkan output kosong atau nama berbeza, **berhenti** dan laporkan. Jangan cuba "membetulkan" dengan padam secara pukal.

#### 11.1.4 Mengapa ini memadai

Container ujian mempunyai volume SQLite sendiri. Apabila container dan volumenya dipadam, workflow ujian hilang sepenuhnya kerana ia hanya wujud dalam pangkalan data itu. Tiada sisa dalam pangkalan data produksi, kerana ujian tidak pernah menyentuhnya.

### 11.2 Bukti yang mesti dikekalkan

1. Output penuh empat percubaan import, termasuk `e.code` dan mesej.
2. Jadual log instansiasi bagi setiap konstruktor yang berjaya dimuatkan.
3. Jadual kompil bagi setiap instans yang berjaya dicipta.
4. Jadual validasi empat fixtur bagi setiap konstruktor, termasuk keyword dan laluan.
5. Hash schema dan keempat-empat fixtur, sebelum dan selepas.
6. Versi n8n, Node dan Ajv yang diperiksa dari dalam container ujian.
7. Digest imej yang digunakan.
8. Konfigurasi persekitaran ujian, dengan nama pemboleh ubah sahaja dan tanpa nilai sensitif.
9. Rekod sama ada `/etc/n8n-task-runners.json` wujud dalam container ujian, dan kandungannya jika ada.

Bukti disimpan dalam laporan keputusan A7 yang berasingan, bukan dalam dokumen ini.

## 11v. Log pembetulan v8

| # | Kecacatan v7 | Punca | Pembetulan v8 |
| --- | --- | --- | --- |
| A | Dakwaan 5 perbandingan Lapisan 1 bebas di post-export | Workflow hanya mengandungi `SCHEMA_RAW_SHA`, bukan hash mentah fixtur | Dua peringkat dipisahkan. Stage A (build-time) mengesahkan 5 fail. Stage B melaporkan `LAYER1_RAW_NOT_VERIFIED` untuk 4 fixtur. 0.A |
| B | "Sepuluh perbandingan" tetapi pengekstrak buat 11 | Kiraan tidak disemak terhadap kod | 5 build-time + 11 post-export = 16. Setiap kategori dikira berasingan. 0.B, 7.4.4b |
| C | Set `declared` tidak digunakan; komen mengelirukan | Kod dibiarkan selepas refactor | Dibuang. Independence dijelaskan melalui kustodi, bukan kod. 0.C |
| D | Pengesahan manifest tidak menyemak versi/kanonikalisasi/penanda | v7 hanya semak objek dan format hash | 13 peraturan, 12 diuji. 0.D, 6.4.3 |
| E | Nilai hash | — | Tidak berubah. Disahkan 10/10. |
| F | Pemetaan penanda tidak dikuatkuasakan dalam manifest | v7 hanya semak kewujudan penanda | Pemetaan tetap dikuatkuasakan. Ujian penanda salah, hilang, berganda, bertindih. 0.F |
| G | Ujian pemalsuan bersepadu | — | Dikekalkan, dijalankan semula pada v8. 4/4 gagal dengan betul. |
| H | Provenance | — | Kekal `PENDING_OWNER_CONFIRMATION`. Tidak dinaik taraf. |
| I | Model hasil | v7 guna `RESULT VERIFIED` untuk Stage B sahaja tanpa nota | Label dipisahkan mengikut peringkat; `RESULT NOT VERIFIED` ditambah untuk bukti tidak lengkap. 0.I |

### 11v.1 Pemeriksaan dokumentasi yang dijalankan

| # | Semakan | Keputusan |
| --- | --- | --- |
| 1 | 5 nilai L1 hadir dan betul | 5/5 |
| 2 | 5 nilai L2 hadir dan betul | 5/5 |
| 3 | Build-time dan post-export dipisahkan | Ya, dalam 0.A dan 7.4.4b |
| 4 | Kiraan perbandingan betul | 5 + 11 = 16 |
| 5 | Tiada logik `declared` mati | Dibuang |
| 6 | Pengesahan manifest ketat | 12 laluan diuji |
| 7 | Ujian pemalsuan hadir dan dijalankan | 2/2 |
| 8 | Provenance kekal pending | Ya |
| 9 | Tiada `RESULT VERIFIED` yang tidak disokong | Nota Stage B ditambah |
| 10 | Kod lengkap dan konsisten | Ya |
| 11 | Rujukan seksyen sah | Disemak |
| 12 | Tiada placeholder | Disemak |
| 13 | v1–v7 tidak berubah | 7/7 |
| 14 | Status governance dikekalkan | Ya |

### 11v.2 UNVERIFIED yang kekal

| # | Perkara | Status |
| --- | --- | --- |
| 1 | `--output=-` menulis stdout dalam 2.43.8 | **UNVERIFIED** |
| 2 | Struktur eksport mengandungi `parameters.jsCode` | **UNVERIFIED** |
| 3 | Penanda kekal melalui import/eksport n8n | **UNVERIFIED** |
| 4 | Pengekstrak terhadap eksport n8n sebenar | **NOT TESTED** |
| 5 | Manifest diluluskan pemilik | **PENDING_OWNER_CONFIRMATION** |
| 6 | Nilai L1 diterbitkan semula daripada fail asal dalam sesi ini | **TIDAK** — daripada laporan A2–A6 |
| 7 | Baseline ialah fixtur W1 rasmi | **NOT VERIFIED** — payload execution 80 |
| 8 | Allowlist `.pnpm`, laluan mutlak, symlink | **UNKNOWN** |
| 9 | Import Ajv sebenar, keputusan fixtur sebenar | **NOT TESTED** |
| 10 | Dialek | **TERBUKA** |

### 11v.3 Status yang tidak berubah

SD-01 **ACCEPTED** · SD-02 **DEFERRED** · Blocker 3 **OPEN / NOT VERIFIED** · M0–M5 **NOT AUTHORIZED** · A7 **PROPOSED — NOT AUTHORIZED FOR EXECUTION** · Dialek **OPEN** · Manifest **PENDING_OWNER_CONFIRMATION**

Lima syarat penutupan Blocker 3 kekal berasingan. v8 **tidak** menutup Blocker 3.

## 11w. Log pembetulan v7 (dikekalkan)

| # | Kecacatan v6 | Punca | Pembetulan v7 |
| --- | --- | --- | --- |
| A | Tiada sumber jangkaan bebas | Pengekstrak membandingkan terhadap pemalar dalam workflow yang sama | Manifest `expected-hashes.json` luaran dengan 5 nilai L1 + 5 nilai L2. Argumen `--expected-manifest` wajib. 6.4.3 |
| B | Pengekstrak tidak membandingkan secara bebas | Perbandingan hanya dalaman | 10 perbandingan berasingan: 5 L2 bebas, 1 L1 bebas, 5 deklarasi workflow. 7.4.4b |
| C | Hasil `SELF_CONSISTENT` tidak membezakan | Istilah tunggal untuk dua jenis bukti | `LAYER1_RAW_MATCH`, `LAYER2_CANONICAL_MATCH`, `WORKFLOW_DECLARED_HASH_MATCH`, `RESULT VERIFIED`/`FAILED`. 7.4.4c |
| D | Tiada ujian pemalsuan bersepadu | v6 tidak menguji serangan ini | Ujian wajib dalam 7.4.4e, dijalankan dalam sesi ini. v6 lulus serangan; v7 gagal. |
| E | Provenance tidak didokumenkan | Manifest tidak wujud dalam v6 | 7.4a: asal setiap nilai, dua lapisan, pemeliharaan, siapa boleh ubah |

### 11w.1 Bukti bahawa v6 terdedah dan v7 tidak

**Ujian yang sama, dua pengekstrak.** Data fixtur baseline diubah **dan** pemalar `canon_sha` ditukar supaya sepadan.

| Pengekstrak | Output | Keputusan |
| --- | --- | --- |
| v6 | `recomputed=91955e0c… declared=91955e0c… self_consistent=True` | **LULUS** (salah) |
| v7 | `LAYER2_CANONICAL_MISMATCH baseline expected=84a1f232… actual=91955e0c…` | **GAGAL** (betul) |

v6 melaporkan `RESULT SELF_CONSISTENT`. v7 melaporkan `RESULT FAILED reasons=['layer2:baseline']` dengan exit 1.

### 11w.2 Semakan dokumentasi yang dijalankan

| # | Semakan | Kaedah | Keputusan |
| --- | --- | --- | --- |
| 1 | 5 hash L1 hadir dan betul | Kira semula, banding | 5/5 |
| 2 | 5 hash L2 hadir dan betul | Kira semula, banding | 5/5 |
| 3 | Struktur manifest sepadan pengekstrak | Uji dengan manifest cacat | 4/4 ditolak |
| 4 | Perbandingan bebas berfungsi | Pengekstrak pada output penjana | `RESULT VERIFIED` |
| 5 | Pemalsuan bersepadu dikesan | Ubah data + pemalar | `RESULT FAILED`, exit 1 |
| 6 | Pemalsuan schema dikesan | Sama pada schema | `RESULT FAILED`, exit 1 |
| 7 | Penanda hilang | Buang `END` | Exit 1 |
| 8 | Penanda berganda | Tambah `BEGIN` | Exit 1 |
| 9 | Data diubah tanpa tukar pemalar | Ubah nilai | `RESULT FAILED` |
| 10 | JSON rosak | Rosakkan blok | Exit 1 |
| 11 | Manifest tiada | Laluan salah | Exit 2 |
| 12 | Manifest hilang objek | Buang `t2` | Exit 2 |
| 13 | Hash format salah | `ZZZ` | Exit 2 |
| 14 | Objek tambahan | Tambah `extra` | Exit 2 |
| 15 | `--expect-file` dibuang | `rg` | Tiada |
| 16 | `--expect-schema-raw` dibuang | `rg` | Tiada |
| 17 | Tiada nilai jangkaan dari workflow | Baca kod | Tiada laluan |
| 18 | Rujukan seksyen sah | `rg` lawan heading | Semua sah |
| 19 | Tiada placeholder kod | Semak blok | 0 |
| 20 | v1–v6 tidak berubah | `sha256sum` | 6/6 sama |

**Semua semakan ini menggunakan JSON tiruan dan fail tempatan.** Tiada satu pun merupakan ujian runtime A7, tiada eksport n8n sebenar, dan tiada Ajv terlibat.

### 11w.3 UNVERIFIED yang kekal

| # | Perkara | Status |
| --- | --- | --- |
| 1 | `--output=-` menulis stdout dalam 2.43.8 | **UNVERIFIED** |
| 2 | Struktur eksport mengandungi `parameters.jsCode` | **UNVERIFIED** |
| 3 | Penanda kekal melalui import/eksport n8n | **UNVERIFIED** |
| 4 | Pengekstrak terhadap eksport n8n sebenar | **NOT TESTED** — hanya JSON tiruan |
| 5 | Manifest diluluskan pemilik | **PENDING_OWNER_CONFIRMATION** |
| 6 | Nilai L1 diterbitkan semula daripada fail asal dalam sesi v7 | **TIDAK** — diambil daripada laporan A2–A6 |
| 7 | Baseline ialah fixtur W1 rasmi | **NOT VERIFIED** — ia payload execution 80 |
| 8 | Allowlist `.pnpm`, laluan mutlak, symlink | **UNKNOWN** |
| 9 | Import Ajv sebenar, keputusan fixtur sebenar | **NOT TESTED** |
| 10 | Dialek | **TERBUKA** |

### 11w.4 Status yang tidak berubah

SD-01 **ACCEPTED** · SD-02 **DEFERRED** · Blocker 3 **OPEN / NOT VERIFIED** · M0–M5 **NOT AUTHORIZED** · A7 **PROPOSED — NOT AUTHORIZED FOR EXECUTION** · Dialek **OPEN**

Lima syarat penutupan Blocker 3 kekal berasingan. v7 **tidak** menutup Blocker 3. Dokumen pelan yang lebih baik bukan bukti pematuhan.

## 11x. Log pembetulan v6 (dikekalkan)

| # | Kecacatan | Lokasi v5 | Punca | Pembetulan v6 |
| --- | --- | --- | --- | --- |
| 1 | Penanda tidak pernah dijana | 7.4.4 memerlukan `A7:LITERAL:*`, templat 6.4 tidak mengeluarkannya | Prosedur dan penjana dibangunkan berasingan; tiada ujian hujung-ke-hujung | Templat v6 mengeluarkan 5 penanda. `check_markers()` mengesahkan kiraan 1/1, susunan, tiada pertindihan (exit 5). Diuji: penanda hilang dan berganda dikesan. 0.1, 6.4.1, 6.4.4 |
| 2 | Dua lapisan hash dicampur | 7.3 hanya ada hash bait untuk fixtur, tetapi 7.4.4 membandingkan hash kanonik | v5 mengira lapisan 2 untuk schema sahaja | Kedua-dua lapisan dikira dan direkod untuk kelima-lima objek. Konvensyen tanpa baris baru ditetapkan dan dibuktikan. 0.2, 7.2 |
| 3 | Kaedah eksport bercanggah | 7.4.4 utama lawan alternatif | Dua kaedah dalam satu seksyen tanpa penetapan | Satu kaedah kanonik (`--output=-`). Kaedah `docker cp` dilabel NOT AUTHORIZED (7.4.4d). Tiga asumsi ditanda UNVERIFIED (7.4.4e) |

### 11x.1 Pemeriksaan konsistensi yang dijalankan

| # | Pemeriksaan | Kaedah | Keputusan |
| --- | --- | --- | --- |
| 1 | Penjana menghasilkan penanda | Jalankan penjana | `MARKERS_OK 5` |
| 2 | Tiada sentinel tinggal | Penjana mengira sendiri | `SENTINELS_REMAINING 0` |
| 3 | Syntax output | `node --check` | Lulus |
| 4 | Pengekstrak berfungsi hujung-ke-hujung | Pengekstrak pada output penjana | 5/5 `self_consistent=True` |
| 5 | Penanda hilang dikesan | Buang satu `END` | Exit 1 |
| 6 | Penanda berganda dikesan | Tambah satu `BEGIN` | Exit 1 |
| 7 | Data diubah dikesan | Ubah satu nilai enum | `RESULT SELF_INCONSISTENT` |
| 8 | Laluan keluar penjana 2/3/4 | Ubah hash, laluan, templat | Exit 2/3/4, tiada fail ditulis |
| 9 | Hash lapisan 2 semua objek | Kira dengan Python | 5/5 disahkan |
| 10 | Konvensyen baris baru | Kira dengan dan tanpa | Berbeza, didokumenkan |
| 11 | Rujukan seksyen | `rg` lawan heading | Semua sah |
| 12 | Tiada placeholder kod | Semak blok kod | 0 |

### 11x.2 Perkara yang masih UNVERIFIED

| # | Perkara | Status | Kesan |
| --- | --- | --- | --- |
| 1 | `--output=-` menulis stdout dalam 2.43.8 | **UNVERIFIED** | ABORT langkah 9 |
| 2 | Struktur eksport mengandungi `parameters.jsCode` | **UNVERIFIED** | Pengekstrak gagal |
| 3 | Penanda kekal melalui import/eksport n8n | **UNVERIFIED** | Pengekstrak gagal |
| 4 | Pengekstrak terhadap eksport n8n sebenar | **NOT TESTED** — hanya JSON tiruan | — |
| 5 | Allowlist menerima laluan `.pnpm` | **UNKNOWN** | Langkah 7 |
| 6 | Laluan mutlak diterima | **UNKNOWN** | Langkah 7 |
| 7 | Symlink diperlukan | **UNKNOWN** | Keputusan pemilik |
| 8 | Import Ajv sebenar dalam Code node | **NOT TESTED** | Langkah 7 |
| 9 | Fixtur lulus dengan Ajv sebenar | **NOT VERIFIED** | Langkah 7 |
| 10 | Dialek | **TERBUKA** | Keputusan pemilik |

### 11x.3 Status yang tidak berubah

| Perkara | Status |
| --- | --- |
| SD-01 | **ACCEPTED** |
| SD-02 | **DEFERRED** |
| Blocker 3 | **OPEN / NOT VERIFIED** |
| M0–M5 | **NOT AUTHORIZED** |
| A7 | **PROPOSED — NOT AUTHORIZED FOR EXECUTION** |
| Dialek | **OPEN** |
| Digest imej | `sha256:a18edcf0…` |
| Hos ujian | Coolify |
| Rangkaian | `--network none` |

Lima syarat penutupan Blocker 3 kekal berasingan dan tidak ditanda selesai.

## 11y. Log pembetulan v5 (dikekalkan)

| # | Isu | Lokasi v4 | Kecacatan | Pembetulan v5 |
| --- | --- | --- | --- | --- |
| A | Semakan placeholder tidak berkesan | 6.4 (penjana), `node.count('{{')` | `str.format()` menukar `{{`→`{` sebelum semakan, jadi `count('{{')` sentiasa 0 dan tidak pernah gagal | Sentinel `__A7_SLOT_<NAME>__` + penggantian literal + regex `__A7_SLOT_[A-Z_]+__`. Exit 4 jika tinggal. Diuji 5 laluan (0/2/3/4). Lihat 0.A dan 6.4.1 |
| B | Dakwaan bukti serialisasi berlebihan | 7.3 | Mendakwa "kelima-lima objek" tetapi hanya 4 fixtur dipaparkan; schema tiada | Jadual kelima-lima objek dengan panjang dan hash. Bukti schema: `ce403cb9…`, 4,713 bait, reserialize identik. Pemisahan bukti serialisasi lawan validasi Ajv dinyatakan. Lihat 7.3 |
| C | Prosedur eksport bercanggah | 7.4.4 lawan senarai semak | Fail ditulis dalam container dalam satu tempat, `--output=-` di tempat lain; tiada pemindahan atau pengekstrakan | Satu kaedah: `--output=-` ke stdout, ditangkap hos. 5 langkah penuh dengan kod. Asumsi CLI ditanda UNVERIFIED (7.4.4b). Penanda `A7:LITERAL:*` untuk pengekstrakan deterministik (7.4.4a) |
| D | Rujukan seksyen dan restart | 10, 8.1 | `7.2.1/7.2.2/7.2.4` lapuk; item 8 berkata restart REQUIRED walaupun container dicipta dengan pemboleh ubah | Rujukan dibetulkan kepada 7.4.1, 6.4.3, 7.4.4. Item 8 jadi NOT REQUIRED dengan penjelasan 8.1.1 (3 urutan, satu pengecualian recreate) |

### 11y.1 Perkara yang masih UNKNOWN / UNVERIFIED selepas v5

| # | Perkara | Status | Boleh diselesaikan oleh |
| --- | --- | --- | --- |
| 1 | Allowlist menerima laluan `.pnpm` | **UNKNOWN** | Larian A7, langkah 7 |
| 2 | Laluan mutlak diterima | **UNKNOWN** | Larian A7, langkah 7 |
| 3 | Symlink diperlukan | **UNKNOWN** | Bergantung #2; keputusan pemilik |
| 4 | Import Ajv sebenar dalam Code node | **NOT TESTED** | Larian A7, langkah 7 |
| 5 | Fixtur lulus dengan Ajv sebenar | **NOT VERIFIED** | Larian A7, langkah 7 |
| 6 | Dialek betul | **TERBUKA** | Keputusan pemilik |
| 7 | `export:workflow --output=-` menulis stdout | **UNVERIFIED** | Larian A7, langkah 9 |
| 8 | Struktur export mengandungi `parameters.jsCode` | **UNVERIFIED** untuk 2.43.8 | Larian A7, langkah 9 |
| 9 | Penanda komen kekal melalui import/eksport | **UNVERIFIED** | Larian A7, langkah 6+9 |
| 10 | Kesetaraan serialisasi untuk objek bukan-SiteSpec | **UNKNOWN** | Hanya jika fixtur berubah |

Tiada satu pun di atas boleh diselesaikan melalui dokumentasi. Lapan memerlukan larian yang dibenarkan; dua memerlukan keputusan pemilik.

### 11y.2 Pemeriksaan dokumentasi yang dijalankan dalam sesi ini

| Pemeriksaan | Kaedah | Keputusan |
| --- | --- | --- |
| Penjana: laluan berjaya | Jalankan `build-a7-node-v5.py` | Exit 0, 22,536 bait, `SENTINELS_REMAINING 0` |
| Penjana: syntax output | `node --check` | Lulus |
| Penjana: slot hilang | Ubah templat | Exit 4, tiada fail |
| Penjana: sentinel tinggal | Isi slot dengan sentinel | Exit 4, tiada fail |
| Penjana: hash tidak sepadan | Ubah satu bait fixtur | Exit 2, tiada fail |
| Penjana: input tiada | Laluan salah | Exit 3, tiada fail |
| Serialisasi: 4 fixtur | Python tulis, Node reserialize | 4/4 identik |
| Serialisasi: schema | Python tulis, Node reserialize | Identik, `ce403cb9…` |
| Semakan `count('{{')` tidak berkesan | Demonstrasi dengan marker | Disahkan sentiasa 0 |
| Rujukan seksyen | `rg` terhadap heading sebenar | Semua sah |

**Yang TIDAK dijalankan:** ujian A7, larian Code node, import Ajv, validasi Ajv sebenar, `n8n export:workflow`, `docker run`, `docker pull`. Tiada perubahan pada mana-mana hos.

### 11y.3 Apa yang **tidak** berubah dalam v5

| Perkara | Nilai |
| --- | --- |
| Digest imej | `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c` |
| Hash bait schema | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` |
| Hash baseline | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` |
| Hash A3-T1 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` |
| Hash A3-T2 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` |
| Hash A3-T3 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` |
| Dialek | **TERBUKA** |
| Hos ujian | **Coolify** `51.79.159.241` |
| Rangkaian | `--network none` |
| Pembersihan | Container `a7-probe` + volume `a7-probe-data` sahaja |
| Perlindungan `fg1kGKB6ciYADxu9` | Kekal |
| **Status A7** | **PROPOSED — NOT AUTHORIZED FOR EXECUTION** |
| SD-01 / SD-02 / Blocker 3 / M0–M5 | ACCEPTED / DEFERRED / OPEN / NOT AUTHORIZED |

Lima syarat penutupan Blocker 3 kekal berasingan dan **tidak** ditanda selesai kerana dokumen pelan ini bertambah baik. Dokumen pelan bukan bukti pematuhan.

## 11z. Log pembetulan v4 (dikekalkan)

| # | Isu | Lokasi v3 | Masalah | Pembetulan v4 |
| --- | --- | --- | --- | --- |
| 1 | SHA tidak sepadan | Laporan respons | Hash dilaporkan 61 aksara, bukan 64. `42` hilang pada kedudukan 23. | Dikira semula: `4017a3a4989079b30d4ac0a42aa7724ab4a5f3747b62ae2274bb87efcd03cdb5`. Punca dan perbandingan didokumenkan dalam 0.1. Semua hash v1–v3 dijangka semula. |
| 2 | Kod 6.4 tidak lengkap | 6.4 | `{ /* ... */ }` dalam `SCHEMA_OBJECT` dan `FIXTURES`; kod tidak boleh dilaksanakan | Diganti dengan penjana mekanikal penuh (6.4.2) dan templat (6.4.3). Diuji: 22,529 bait, `PLACEHOLDERS_LEFT 0`, `node --check` lulus |
| 3 | Pembersihan bercanggah | 5.3.4, 10, 11.1 | 11.1 berkata "padam melalui UI atau API" walaupun `--network none` | 11.1 ditulis semula: satu kaedah (container + volume), urutan 7 langkah, pengesahan nama sebelum padam, larangan pukal |
| 4 | Nombor langkah probe | 10.2, 10.3 | "Berhenti selepas langkah 6" — tetapi langkah 6 ialah import, bukan pelaksanaan | Semua rujukan dibetulkan kepada **langkah 7**. Langkah 6 dan 7 dibezakan secara eksplisit |
| 5 | Pengesahan hash | 7.2, 7.3, 7.4 | `fx.sha` dicetak dan didakwa bukti; kaedah serialisasi tidak dibuktikan | Dipisahkan kepada hash bait fail lawan hash serialisasi (7.2), kesetaraan Python/JS dibuktikan (7.3), empat peringkat bukti (7.4) |

### 11z.1 Perkara yang masih UNKNOWN atau INCONCLUSIVE selepas v4

| # | Perkara | Status | Boleh diselesaikan oleh |
| --- | --- | --- | --- |
| 1 | Allowlist lawan laluan `.pnpm` | **UNKNOWN / NEEDS VERIFICATION** | Larian A7, langkah 7 |
| 2 | Laluan mutlak melepasi allowlist | **UNKNOWN / NEEDS VERIFICATION** | Larian A7, langkah 7 |
| 3 | Keperluan symlink | **UNKNOWN** | Bergantung #2; keputusan pemilik |
| 4 | Dialek | **TERBUKA** | Keputusan pemilik |
| 5 | Keputusan Ajv sebenar terhadap fixtur | **NOT VERIFIED** — hanya stub diuji | Larian A7, langkah 7 |
| 6 | Kesetaraan serialisasi untuk objek bukan-SiteSpec | **UNKNOWN** | Hanya jika fixtur berubah |
| 7 | Rantaian bukti fixtur hujung-ke-hujung | **PROPOSED** | Langkah 9 + 10 mesti dijalankan |
| 8 | Import Ajv dari Code node | **NOT TESTED** | Larian A7, langkah 7 |

### 11z.2 Apa yang **tidak** berubah dalam v4

| Perkara | Nilai |
| --- | --- |
| Digest imej | `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c` |
| Hash schema | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` |
| Hash baseline | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` |
| Hash A3-T1 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` |
| Hash A3-T2 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` |
| Hash A3-T3 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` |
| Dialek | **TERBUKA** |
| Hos ujian | **Coolify** `51.79.159.241` |
| Rangkaian | `--network none` |
| Perlindungan `fg1kGKB6ciYADxu9` | Kekal |
| Status A7 | **PROPOSED** — bukan APPROVED, bukan COMPLETED |

## 11a. Log pembetulan v3 (dikekalkan)

| # | Lokasi v2 | Masalah | Pembetulan v3 |
| --- | --- | --- | --- |
| 1 | 5.3 dan 6 | v2 mencadangkan `--network none` **dan** operasi UI/API. Kedua-duanya bercanggah. | Ditambah 5.3.2: workflow diimport dan dijalankan melalui CLI (`import:workflow`, `execute`) di dalam container. Tiada rangkaian diperlukan. Isolasi **tidak** dilemahkan. |
| 2 | 8.0 | v2 menanda mekanisme allowlist sebagai UNKNOWN dan memetik dokumentasi mod `external` | Dibaca `task-runner-process-js.js` dalam container. Kelas itu `strict_1.default(this.isInternal, …)` — hanya untuk mod `internal`. `getProcessEnvVars()` menyalin `process.env.NODE_FUNCTION_ALLOW_EXTERNAL` secara eksplisit. Mekanisme kini **VERIFIED**. |
| 3 | 6.4 | `BASE`, `SCHEMA_OBJECT`, `FIXTURES` tidak ditentukan; kod tidak boleh dilaksanakan | Diganti sepenuhnya: `AJV_ROOT`, `MODULE_SPECS`, `SCHEMA_OBJECT`, `FIXTURES`, `SCHEMA_SHA_EXPECTED` semuanya ditakrifkan. Empat peringkat dengan log berasingan. Jadual 6.4.7 menyenaraikan setiap pengenal. |
| 4 | 7 | `fx.sha` hanya dicetak; ia tidak membuktikan data sepadan | Ditambah 7.2: empat peringkat bukti (persediaan, pembinaan, dalam nod, pasca-larian). Perbezaan antara "hash yang dicetak" dan "hash yang dibuktikan" dinyatakan. |
| 5 | 10 | Senarai semak menganggap UI/API | Ditulis semula dengan kaedah CLI. Ditambah langkah 0 (sahkan hash) dan langkah 10 (sahkan hash pasca-larian). Padam workflow melalui padam container+volume. |
| 6 | 10.2 dan 10.3 | Tiada peraturan berhenti | Ditambah 10.3: empat titik berhenti eksplisit, termasuk BLOCKED selepas probe import gagal. |

### 11a.1 Perkara UNKNOWN dan BLOCKED yang tinggal selepas v3

| # | Perkara | Status | Boleh diselesaikan oleh |
| --- | --- | --- | --- |
| 1 | Sama ada allowlist membenarkan laluan `.pnpm` | **UNKNOWN / NEEDS VERIFICATION** | Larian ujian, langkah 7 |
| 2 | Sama ada laluan mutlak melepasi allowlist | **UNKNOWN / NEEDS VERIFICATION** | Larian ujian, langkah 7 |
| 3 | Sama ada symlink `node_modules` diperlukan | **UNKNOWN** | Bergantung #2; keputusan pemilik |
| 4 | Dialek yang diuji | **TERBUKA** | Keputusan pemilik |
| 5 | Sama ada fixtur boleh disahkan penghujung-ke-hujung | **PROPOSED** | 7.4.1 + 7.4.4 mesti dijalankan |

Tiada satu pun daripada lima ini boleh diselesaikan melalui dokumentasi sahaja. Empat memerlukan larian ujian; satu memerlukan keputusan pemilik.

### 11a.2 Apa yang **tidak** berubah

| Perkara | Nilai |
| --- | --- |
| Digest imej | `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c` |
| Hash schema | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` |
| Hash baseline | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` |
| Hash A3-T1 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` |
| Hash A3-T2 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` |
| Hash A3-T3 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` |
| Dialek | **TERBUKA** |
| Perlindungan `fg1kGKB6ciYADxu9` | Kekal |
| Semua larangan perubahan produksi | Kekal |

## 11b. Log pembetulan v2 (dikekalkan)

| # | Lokasi v1 | Masalah | Pembetulan v2 |
| --- | --- | --- | --- |
| 1 | 6.2, nod probe | `const FS = require('fs')` tanpa digunakan; ia akan gagal jika builtin tidak dibenarkan, dan itu mengaburkan ujian | Baris itu dibuang. Nod tidak mengimport sebarang modul builtin. |
| 2 | 8.1, item 5 | `NODE_FUNCTION_ALLOW_BUILTIN=fs` diklasifikasikan **OPTIONAL** | Diklasifikasikan **NOT REQUIRED**, dengan sebab |
| 3 | 6.2 dan 6.3 | Hanya `ajv` dan `ajv/dist/2020` diuji; `ajv/dist/2019` tiada | Empat percubaan: `ajv`, `dist/2019`, `dist/2020`, laluan mutlak |
| 4 | 6.4 | Satu blok kod menganggap satu import berjaya bermakna validasi boleh dijalankan | Dipecahkan kepada tiga peringkat berasingan: muat, instansiasi, kompil+validasi. Setiap satu dilaporkan berasingan. |
| 5 | 7.1 | Jadual konstruktor tidak membezakan peringkat | Ditambah 7.2: matriks 3×4 dengan sel `?` bagi setiap peringkat |
| 6 | 8 (baharu) | Tiada bahagian tentang arkitektur task runner | Ditambah 8.0: proses PID 1/7/19, persekitaran runner, petikan dokumentasi rasmi, dan penanda **UNKNOWN** untuk mod `internal` |
| 7 | 8.0 (baharu) | Andaian `NODE_PATH` tidak pernah diuji | Diuji: `NODE_PATH` runner **tidak** menyelesaikan `require('ajv')`. Andaian ditolak dan direkod. |
| 8 | 5.3 (baharu) | Tiada keperluan isolasi rangkaian | Ditambah 5.3: `--network none`, tiada port, tiada mount hos, tiada socket Docker, had sumber, urutan penarikan imej, dan batas isolasi |
| 9 | 10.1 | Kriteria lulus hanya PASS/FAIL/INCONCLUSIVE | Ditambah **PARTIAL** untuk kegagalan instansiasi atau kompil, supaya kegagalan separa tidak dilaporkan sebagai kejayaan penuh |
| 10 | 10.2 (baharu) | Tiada senarai prasyarat yang mesti diselesaikan sebelum larian | Ditambah lima item, termasuk **UNKNOWN** untuk mekanisme allowlist mod `internal` |

### 11a.1 Apa yang **tidak** berubah

| Perkara | Nilai |
| --- | --- |
| Digest imej | `sha256:a18edcf018198b33b5488fb47ededafe11e0b10d28f65b9535b7c447c9ce3b2c` |
| Hash schema | `0c184dce282b1caf47fe31ef717b141596a5edeb26c49c9834c0c352bb6aff21` |
| Hash baseline | `68a816c8ab7223835737098127918545a0cb1a6c36ea3f6c4a01e09d03cfca4a` |
| Hash A3-T1 | `52b807271d5ea312a0540f46b587ff78395230286fec6ef70752d9df817f43f3` |
| Hash A3-T2 | `45379ba288057e06f24f12caf9ecdefbef2ca57959a5fb6bbfbedc837e268011` |
| Hash A3-T3 | `0948832003b1ddc7e2a7e9208c6d2f9d2bd8aa8c6dfae4c100aa740268ad3051` |
| Dialek | **TERBUKA** — tiada pilihan dibuat |
| Perlindungan `fg1kGKB6ciYADxu9` | Kekal |
| Semua larangan perubahan produksi | Kekal |

## 12. Pernyataan eksplisit — apa yang TIDAK dijalankan atau diubah

**Tiada satu pun daripada yang berikut dilakukan dalam tugasan ini:**

1. Tiada ujian A7 dijalankan.
2. Tiada workflow dicipta, diubah atau dipadam, pada produksi atau mana-mana instans.
3. Workflow produksi `fg1kGKB6ciYADxu9` **tidak** diakses, tidak dibuka, tidak dijalankan, tidak diubah.
4. Tiada pemboleh ubah persekitaran diubah pada mana-mana hos.
5. Tiada allowlist diubah.
6. Tiada container dicipta, dimulakan, direstart atau dipadam.
7. Tiada imej ditarik atau dipadam.
8. Tiada pakej dipasang atau dinaik taraf.
9. Tiada perubahan `node_modules`, symlink atau laluan modul.
10. Tiada perubahan volume atau fail pada mana-mana hos.
11. Tiada perubahan pangkalan data atau kredensial.
12. Tiada perubahan Coolify atau konfigurasi deployment.
13. Tiada commit, push atau merge.
14. Tiada dialek dipilih. Tiada konstruktor Ajv dipilih sebagai produksi.

**Tindakan baca sahaja yang dijalankan:** bacaan dokumen dalam simpanan, pengiraan hash, bacaan dokumentasi rasmi n8n melalui HTTPS awam, dan pemeriksaan hos melalui SSH baca sahaja (`nproc`, `free -m`, `df -h`, `docker ps`, `docker images`, `ps -eo pid,ppid,args`, bacaan `/proc/<pid>/environ` untuk **nama** pemboleh ubah sahaja, dan satu ujian `node -e` dengan/tanpa `NODE_PATH` untuk menguji resolusi). Ujian `node -e` itu menjalankan proses Node sementara dalam container sedia ada dan tidak menulis apa-apa; ia hanya membaca nilai versi dan mesej ralat. Tiada nilai rahsia dipaparkan.

**Perkara yang memerlukan kebenaran berasingan sebelum A7 boleh dilaksanakan:**

1. Pilihan hos untuk ujian.
2. Penarikan imej 1.8 GB.
3. Penciptaan container dengan allowlist ditetapkan.
4. Penciptaan dan pemadaman workflow ujian.
5. Sebarang perubahan `node_modules` jika symlink diperlukan.
6. Penciptaan `/etc/n8n-task-runners.json` jika mekanisme mod `internal` memerlukannya.
7. Pilihan dialek jika ujian mahu dihadkan kepada satu konstruktor.

**A7 kekal PROPOSED.** Ia tidak menjadi INSPECTION atau COMPLETED sehingga pemilik menyemak pelan ini dan membenarkan pelaksanaan secara berasingan.

## 11w. Log pembetulan v9

v9 ialah pembetulan dokumentasi dan pemeriksaan lokal sahaja. v1–v8 tidak diubah.

| # | Isu v8 | Pembetulan v9 |
| --- | --- | --- |
| A | `brace_slice()` menerima objek pertama dan mengabaikan kandungan selepasnya | `parse_schema()` dan `parse_fixture()` kini anchored dan mesti menggunakan keseluruhan blok; trailing JavaScript, deklarasi, property atau wrapper tambahan ditolak. |
| B | Manifest menyebut `json.dumps(obj, indent=2)` sedangkan kod menggunakan `ensure_ascii=False` | Kontrak tunggal kini `json.dumps(obj, indent=2, ensure_ascii=False)`, UTF-8, tanpa newline akhir. Nilai diluluskan tidak diubah. |
| C | Medan tambahan boleh terlepas pada aras tertentu | Root, canonicalization, approval dan setiap entri objek kini mempunyai set medan tepat; nama objek dan marker juga tepat. |
| D | Komen templat masih mengenal pasti generator sebagai v6 | Komen templat aktif kini `build-a7-node.py (v9)`; rujukan sejarah tidak diubah. |

### 11w.1 Sempadan grammar marker (Stage B)

Verifier mesti memakan **keseluruhan** kandungan antara marker. Grammar yang diterima ialah:

* schema: whitespace, satu `const SCHEMA_OBJECT =`, satu JSON object literal, whitespace dan terminator pilihan `;` sahaja;
* fixtur: satu wrapper tepat `{ name: "<marker identity>", expect: "<approved result>", data: <one JSON object> }`, whitespace dan terminator pilihan `;` sahaja.

`parse_schema()` dan `parse_fixture()` menolak sebarang kandungan selepas object yang telah diparse. Mereka juga menolak nama fixtur yang salah, hasil jangkaan yang salah, wrapper/property tambahan, deklarasi, expression, komen atau blok bertindih. Pemeriksaan ini membuktikan extraction yang tidak ambigu dalam marker boundary sahaja; ia tidak melindungi perubahan di luar marker.

Ujian negatif v9 berikut ditetapkan dengan hasil dan exit code yang diperlukan. Ia **NOT EXECUTED** dalam sesi dokumentasi ini; “actual result” oleh itu ialah `NOT EXECUTED`, bukan hasil rekaan:

| Ujian | Input/mutasi | Expected | Actual | Exit | Context |
| --- | --- | --- | --- | --- | --- |
| schema appended statement | schema object sama + `additionalExecutableStatement()` sebelum END | `RESULT FAILED` | NOT EXECUTED | 1 jika dijalankan | mock workflow JSON test |
| fixture appended statement | data sama + statement sebelum END | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| extra fixture property | tambah `extra: 1` pada wrapper | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| wrong fixture name | nama tidak sama marker | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| wrong expected result | `expect` bukan mapping diluluskan | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| schema declaration | tambah `const EVIL = 1` dalam block | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| fixture trailing content | kandungan selepas wrapper | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |
| valid generated literals | output generator v9 yang lengkap | `RESULT VERIFIED` (jika Stage A juga lulus) | NOT EXECUTED | 0 jika dijalankan | generator/extractor mock check |
| missing/duplicate/reversed/overlap marker | struktur marker rosak | `RESULT FAILED` | NOT EXECUTED | 1 | mock workflow JSON test |

### 11w.2 Kontrak kanonikalisasi dan ujian encoding

Kontrak Layer 2 tunggal ialah `json.dumps(obj, indent=2, ensure_ascii=False)`, encode UTF-8, tanpa trailing newline, kemudian SHA-256. `ensure_ascii=True`, newline akhir, encoding bukan UTF-8, dan convention lain adalah tidak serasi dan mesti ditolak atau menghasilkan hash yang berbeza. Layer 1 ialah hash bytes fail asal; ia tidak boleh diganti dengan Layer 2.

| Kes | Jangkaan | Actual | Context |
| --- | --- | --- | --- |
| object ASCII sahaja | output deterministic dan hash sama pada ulangan | NOT EXECUTED | extractor unit test |
| object Unicode | UTF-8 + `ensure_ascii=False` deterministic | NOT EXECUTED | extractor unit test |
| object sama `ensure_ascii=True` | hash berbeza dan bukan kontrak | NOT EXECUTED | canonicalization unit test |
| newline akhir | hash berbeza dan ditolak sebagai contract mismatch | NOT EXECUTED | canonicalization unit test |
| UTF-8 bytes | bytes yang di-hash tepat output UTF-8 tanpa newline | NOT EXECUTED | canonicalization unit test |

Manifest metadata penuh yang diperlukan ialah:

```json
"canonicalization": {
  "convention": "json.dumps(obj, indent=2, ensure_ascii=False)",
  "encoding": "utf-8",
  "trailing_newline": false
}
```

### 11w.3 Manifest contract tepat

Medan yang dibenarkan (dan wajib) adalah:

* root: `manifest_version`, `canonicalization`, `objects`, `approval`;
* `canonicalization`: `convention`, `encoding`, `trailing_newline`;
* setiap `objects.<name>`: `layer1_raw_sha256`, `layer2_canon_sha256`, `marker`, `source`;
* `approval`: `status`, `note`.

Setiap set mesti **exactly** sama dengan set di atas. Validator juga menolak objek hilang/tambahan, marker salah, hash bukan 64 lowercase hex, versi/canonicalization salah, hash duplicate, atau metadata approval tidak sah. `source` dikekalkan sebagai metadata sah; ia tidak membuktikan provenance dengan sendirinya.

Ujian unexpected-field di root, canonicalization, approval dan object-entry, serta ujian missing-field, invalid version, invalid canonicalization, duplicate hash dan wrong marker: **NOT EXECUTED** dalam sesi ini. Setiap expected ialah `FATAL ...`, exit 2. Ujian v8 yang dilaporkan sebagai telah dijalankan kekal laporan sejarah dan tidak ditulis semula.

### 11w.4 Kiraan dan status

Accounting tidak berubah: Stage A = **5** raw-source comparisons; Stage B = **11** post-export comparisons (5 Layer 2 manifest, 1 schema Layer 1 declaration, 5 workflow-declared canonical consistency); jumlah = **16** across two stages. Fixture raw-file Layer 1 di Stage B kekal `NOT VERIFIED` kerana empat bytes asal tidak dibekalkan kepada extractor.

`RESULT VERIFIED` hanya jika lima Stage A raw checks benar-benar selesai dan lulus, sebelas Stage B checks lulus, grammar marker lengkap, dan manifest strict validation lulus. Jika input asal tidak tersedia tanpa mismatch, gunakan `RESULT NOT VERIFIED`; mismatch atau struktur tidak sah ialah `RESULT FAILED`. Tiada status ini membuktikan Ajv, n8n export, task-runner allowlist atau Blocker 3.

## 12. Audit v9 dan status operasi

Pemeriksaan dokumentasi v9 yang dijalankan: carian struktur, semakan literal hash, semakan tajuk/versi, dan perbandingan hash v8 selepas salinan. Tiada code snippet, mock workflow, generator, extractor, n8n, Docker atau runtime A7 dijalankan. Ujian v9 dalam 11w ditanda `NOT EXECUTED` secara eksplisit.

Andaian export kekal **UNVERIFIED**: `--output=-` mengeluarkan stdout dalam n8n 2.43.8; struktur export mengandungi `nodes[].parameters.jsCode`; dan komen/marker literal survive import/export. Manifest kekal `PENDING_OWNER_CONFIRMATION`; Layer 1 masih disalin daripada laporan A2–A6 dan belum dijana semula daripada semua fail asal dalam sesi semasa; baseline ialah payload execution 80 dan identiti W1 masih NOT VERIFIED.

Governance kekal tepat:

* SD-01: **ACCEPTED**
* SD-02: **DEFERRED**
* Blocker 3: **OPEN / NOT VERIFIED**
* M0–M5: **NOT AUTHORIZED**
* A7: **PROPOSED — NOT AUTHORIZED FOR EXECUTION**
* Dialect: **OPEN**
* Manifest: **PENDING_OWNER_CONFIRMATION**

Lima closure criteria Blocker 3 kekal berasingan. v9 tidak menutup Blocker 3 dan tidak memberi kuasa menjalankan A7.


## 13. Log pembetulan v10

| # | Isu dalam v9 | Punca | Pembetulan v10 | Bukti |
| --- | --- | --- | --- | --- |
| A | Pengekstrak tidak menolak medan tambahan dalam entri objek | Penggantian `# 4. per-entry exact fields` gagal senyap; kod tidak berubah | Pemeriksaan `set(e) != ENTRY_KEYS` dan `ENTRY_KEYS` digunakan. Pemalar `REQUIRED_ENTRY_FIELDS` dibuang. | V4-extract: v9 ACCEPTED; v10 REJECTED |
| B | Medan `source` hilang menyebabkan `KeyError` (traceback, bukan FATAL) | `REQUIRED_ENTRY_FIELDS` tidak merangkumi `source` | Set medan tepat merangkumi `source` dan dinilai sebelum sebarang akses kunci | V8-extract: v9 EXCEPTION; v10 REJECTED |
| C | Mesej untuk medan canonicalization hilang melaporkan `unexpected: []` | Mesej membina senarai lebihan dan bukan senarai kekurangan | Mesej menyatakan set yang diperlukan dan set yang diterima | V6-build: mesej v10 menyebut `got [...]` |
| D | Label keputusan `RESULT VERIFIED` boleh dibaca sebagai pengesahan penuh | Satu label untuk dua peringkat | Label dipisahkan kepada `STAGE_A …`, `STAGE_B …`, dan `FULL_CHAIN …` (seksyen 14) | Output E0 dan A-ok |
| E | Kod keluar campur: 2 digunakan untuk manifest tidak sah dan untuk mismatch | Tiada pemetaan tetap | 1 = FAILED, 3 = NOT VERIFIED, 2 = penggunaan argparse sahaja (seksyen 14.2) | A-mismatch, A-badjson, E16, E18, V20 |
| F | Hanya kompilasi sintaks dilaporkan untuk v9 | Ujian fungsi tidak dijalankan | Harness 132 kes dijalankan terhadap kod sebenar v9 dan v10 | seksyen 15 |
| G | Dakwaan v9 seksyen 11w.3 bahawa validator menolak medan objek tambahan | Dakwaan tidak disahkan dan tidak benar untuk pengekstrak | Dibetulkan oleh A dan dibuktikan oleh V4-extract | seksyen 15 |
| H | Templat dalam seksyen 6.4.5 v9 bukan templat penjana: nama slot `SCHEMA_SHA`, komen berbeza, dan satu baris hilang | Salinan manual lama tidak disegerakkan dengan `TEMPLATE` dalam penjana | Blok 6.4.5 digantikan dengan `TEMPLATE` daripada `build_a7_node.py` v10 (sama seperti dijalankan oleh harness) | Semakan kesamaan dalam seksyen 16 |

Kesan kepada seksyen lama. Seksyen 11w (v9) dan 12 (audit v9) kekal sebagai rekod sejarah. Dakwaan yang bertentangan dengan dapatan di atas digantikan oleh seksyen ini. Baris `NOT EXECUTED` dalam seksyen 11w kini dijalankan sebagai ujian mock dalam seksyen 15, dan pelaksanaannya dicatat secara eksplisit. Output `RESULT VERIFIED`, `RESULT FAILED`, dan `RESULT NOT VERIFIED` dalam seksyen 6.4.6, 11v, 11w dan log lain ialah rekod output lama. Ia tidak ditulis semula. Dalam v10, label yang sepadan ialah seperti dalam seksyen 14.1.

## 14. Vocabulary keputusan dan kod keluar v11

### 14.1 Label keputusan

| Peringkat | Label | Maksud | Kod keluar |
| --- | --- | --- | --- |
| Stage A (build-time) | `STAGE_A VERIFIED` | Lima hash raw fail sumber sepadan dengan manifest, lima hash Lapisan 2 yang dikira daripada objek sumber sepadan dengan manifest, penanda sah, manifest sah ketat, nod ditulis | 0 |
| Stage A | `STAGE_A FAILED` | Mismatch hash raw, manifest tidak sah, fail sumber bukan JSON, Lapisan 2 tidak konsisten, penanda atau placeholder melanggar kontrak | 1 |
| Stage A | `STAGE_A NOT VERIFIED` | Satu atau lebih fail sumber atau manifest tidak tersedia. Tiada nod ditulis | 3 |
| Stage B (post-export) | `STAGE_B VERIFIED` | Sebelas perbandingan pasca-eksport lulus, penanda sah, manifest sah ketat | 0 |
| Stage B | `STAGE_B FAILED` | Satu atau lebih perbandingan gagal, penanda melanggar kontrak, atau struktur eksport/manifest tidak sah dikesan | 1 |
| Stage B | `STAGE_B NOT VERIFIED` | Bukti diperlukan tidak tersedia (manifest atau eksport hilang, atau `SCHEMA_RAW_SHA` tiada) | 3 |
| Keseluruhan | `FULL_CHAIN VERIFIED` | Hanya dibenarkan jika `STAGE_A VERIFIED` dan `STAGE_B VERIFIED` untuk input yang sama. Tiada skrip v11 menghasilkan label ini. Ia ialah keputusan semakan operator yang menggabungkan dua output | — |
| Keseluruhan | `FULL_CHAIN FAILED` | Mana-mana peringkat `FAILED` | — |
| Keseluruhan | `FULL_CHAIN NOT VERIFIED` | Tiada peringkat gagal, tetapi satu atau kedua-dua peringkat tiada bukti diperlukan. `extract-and-verify.py` sentiasa mencetak baris ini pada `STAGE_B VERIFIED` kerana ia tidak menjalankan Stage A | — |

Peraturan utama: **`STAGE_B VERIFIED` ialah pengesahan pasca-eksport sahaja. Ia bukan `FULL_CHAIN VERIFIED`.** Kod keluar 0 bagi `extract-and-verify.py` bermakna `STAGE_B VERIFIED` sahaja.

Pemetaan label lama: `RESULT VERIFIED` (output v8/v9 pengekstrak) = `STAGE_B VERIFIED`. `RESULT FAILED` = `STAGE_B FAILED` (pengekstrak) atau `STAGE_A FAILED` (penjana). `RESULT NOT VERIFIED` = `STAGE_B NOT VERIFIED`.

### 14.2 Kod keluar

| Kod | Maksud | Skrip |
| --- | --- | --- |
| 0 | `STAGE_A VERIFIED` atau `STAGE_B VERIFIED` | kedua-dua |
| 1 | `STAGE_A FAILED` atau `STAGE_B FAILED` | kedua-dua |
| 2 | Ralat penggunaan argparse sahaja, tiada keputusan peringkat | kedua-dua |
| 3 | `STAGE_A NOT VERIFIED` atau `STAGE_B NOT VERIFIED` | kedua-dua |

Tiada kod 4 atau 5 dalam v10. Kod 2 dalam v9 untuk manifest tidak sah dan mismatch tidak lagi digunakan. Pemetaan v9 → v10 ialah: manifest tidak sah dan mismatch 2 → 1; manifest hilang 2 → 3; sumber hilang kekal 3; pelanggaran penanda 5 dan placeholder 4 → 1.

### 14.3 Had tafsiran

Walaupun `STAGE_B VERIFIED` dan `FULL_CHAIN VERIFIED`, keputusan ini tidak membuktikan bahawa Ajv menerima skema atau fixtur, bahawa eksport n8n sebenar berfungsi, bahawa allowlist task-runner betul, atau bahawa Blocker 3 ditutup. Manifest yang diluluskan ialah rujukan kustodi dan kelulusan. Kesamaan hash dengan workflow tidak membuktikan provenance manifest.

### 14.4 Had sempadan penanda

Pengurai seksyen 6.4.4 menerima bentuk `SCHEMA` dan `FIXTURE_*` yang tepat dan menolak sebarang kandungan selain whitespace dan satu terminator pilihan di dalam blok penanda. Ia tidak melindungi kandungan di luar penanda. Ia juga tidak membuktikan asal-usul workflow.

## 15. Matriks ujian v10 — ringkasan

Semua ujian di bawah dijalankan pada **data mock dan kod sebenar** v9 serta v10. Ini ialah ujian harness tempatan, bukan ujian n8n. Perincian setiap kes ada dalam `internal/a7-v10-local-test-results.md`.

| Kumpulan | Dijalankan | Jumlah kes | Lulus pada v9 | Lulus pada v10 |
| --- | --- | --- | --- | --- |
| 1. Parser (parse_schema, parse_fixture, as_json, block_bounds, check_no_overlap) | 39 | 39 | 39 | 39 |
| 2. Canonicalization (canon_sha dan fail sebenar) | 16 | 16 | 16 | 16 |
| 3. Pengesah manifest (load_manifest, kedua-dua salinan) | 47 | 47 | 45 | 47 |
| 4. CLI pada data mock (subprocess ke kedua-dua skrip) | 30 | 30 | 30 | 30 |
| **Jumlah** | **132** | **132** | **130** | **132** |

Konteks setiap kumpulan: Kumpulan 1 dan 3 memanggil fungsi secara terus dalam proses. Kumpulan 2 memanggil `canon_sha` sebenar. Kumpulan 4 menjalankan `build_a7_node.py` dan `extract_and_verify.py` sebagai subprocess pada fail mock dalam direktori sementara. Kedua-dua fail sumber dan manifest ujian ialah mock. Manifest ujian dikira daripada mock dan diberi `"source": "TEST-ONLY mock source …"`. Ia **bukan** manifest yang diluluskan.

### 15.1 Ujian yang paling penting (exit code sebenar v10)

Simbol "—" bermaksud ujian dalam proses (panggilan fungsi terus); ia tiada kod keluar proses. Kes CLI mempunyai kod keluar sebenar.

| ID | Ujian | Exit | Keputusan |
| --- | --- | --- | --- |
| P1 | parse_schema: valid schema block | — | LULUS |
| N1 | parse_schema: appended executable statement | — | LULUS |
| N2 | parse_schema: additional declaration after object | — | LULUS |
| N3 | parse_schema: trailing block comment | — | LULUS |
| N4 | parse_fixture: appended executable statement after wrapper | — | LULUS |
| N5 | parse_fixture: additional wrapper property | — | LULUS |
| N6 | parse_fixture: wrong fixture name (t1 text, baseline marker) | — | LULUS |
| N7 | parse_fixture: wrong expected result (baseline marked INVALID) | — | LULUS |
| N8 | parse_fixture+as_json: malformed JSON in data (trailing comma) | — | LULUS |
| N9 | parse_fixture: unbalanced braces in data | — | LULUS |
| M1 | marker: missing FIXTURE_t3 block | — | LULUS |
| M2 | marker: duplicate SCHEMA block | — | LULUS |
| M3 | marker: END before BEGIN | — | LULUS |
| M4 | marker: nested/overlapping blocks | — | LULUS |
| C2-build | Unicode object uses ensure_ascii=False (build.canon_sha) | — | LULUS |
| C3-build | ensure_ascii=True serialization yields different hash (build) | — | LULUS |
| C4-build | UTF-8 encoding used (build) | — | LULUS |
| C5-build | no trailing newline (build) | — | LULUS |
| C6-build | adding trailing newline changes hash (build) | — | LULUS |
| C7-raw | SOURCE-FILE HASH CHECK: real schema raw bytes vs approved Layer 1 | — | LULUS |
| C7-canon-extract | real schema canonical hash vs approved Layer 2 (extract.canon_sha) | — | LULUS |
| C8 | approved schema L2 discriminates ensure_ascii convention? | — | LULUS |
| V4-extract | unexpected object-entry field (objects.t1.extra) (extract.load_manifest) | 1 | LULUS |
| V8-extract | missing object-entry field (objects.t1.source) (extract.load_manifest) | 1 | LULUS |
| V6-build | missing canonicalization field (encoding) (build.load_manifest) | 1 | LULUS |
| V19-extract | invalid approval status (APPROVED) (extract.load_manifest) | 1 | LULUS |
| A-ok | Stage A generator: all five mock sources match TEST manifest | 0 | LULUS |
| A-mismatch | Stage A: schema raw bytes changed -> raw mismatch | 1 | LULUS |
| A-missing | Stage A: t2 source file unavailable | 3 | LULUS |
| A-badjson | Stage A: source matches hash but is not JSON | 1 | LULUS |
| A-layer2 | Stage A: manifest Layer 2 disagrees with recomputed source value | 1 | LULUS |
| A-syntax | generated node file passes `node --check` (syntax only, not executed) | 0 | LULUS |
| E0 | Stage B positive: unmodified generated node vs TEST manifest | 0 | LULUS |
| E1 | schema: executable statement appended before END | 1 | LULUS |
| E2 | fixture t1: executable statement appended before END | 1 | LULUS |
| E3 | fixture t1: extra wrapper property | 1 | LULUS |
| E4 | fixture t1: wrong fixture name inside marker | 1 | LULUS |
| E5 | fixture baseline: wrong expected result | 1 | LULUS |
| E6 | fixture baseline: malformed JSON (trailing comma) | 1 | LULUS |
| E7 | fixture baseline: unbalanced data brace | 1 | LULUS |
| E8 | schema: extra declaration inside marker block | 1 | LULUS |
| E9 | fixture t2: extra content after wrapper | 1 | LULUS |
| E10 | duplicate FIXTURE_t1 BEGIN marker | 1 | LULUS |
| E11 | missing FIXTURE_t3 END marker | 1 | LULUS |
| E12 | BEGIN/END order reversed for FIXTURE_t3 | 1 | LULUS |
| E13 | overlapping blocks: FIXTURE_t1 moved inside SCHEMA block | 1 | LULUS |
| T1 | COORDINATED TAMPERING fixture: data and its canon constant changed together | 1 | LULUS |
| T2a | COORDINATED TAMPERING schema: literal and SCHEMA_CANON_SHA changed together | 1 | LULUS |
| T2b | COORDINATED TAMPERING schema: literal, SCHEMA_CANON_SHA and SCHEMA_RAW_SHA changed together | 1 | LULUS |
| E14 | fixture baseline literal changed, declared constant unchanged | 1 | LULUS |
| E15 | declared SCHEMA_CANON_SHA changed only | 1 | LULUS |
| E16 | missing expected manifest file | 3 | LULUS |
| E17 | --expected-manifest omitted (argument must be required) | 2 | LULUS |
| E18 | export file missing | 3 | LULUS |
| E19 | export with no Code node | 1 | LULUS |
| E20 | independent manifest Layer 2 for t2 differs from workflow | 1 | LULUS |

Kes T1, T2a, dan T2b ialah **penyelewengan bersepadu**. Setiap kes mengubah data dan pemalar hash dalam workflow sekali gus, dengan manifest tidak berubah. Dalam T1, pemeriksaan konsistensi dalaman lulus (`WORKFLOW_DECLARED_HASH_MATCH baseline`), tetapi perbandingan bebas Lapisan 2 gagal (`LAYER2_CANONICAL_MISMATCH baseline`). Keputusan keseluruhan ialah `STAGE_B FAILED`, exit 1. Perbandingan bebas yang menangkap penyelewengan ini mengakses manifest luaran sahaja. Ia tidak boleh menangkap penyerang yang juga boleh mengubah manifest. Itu tanggungjawab kustodi manifest.

Kes T2b juga mengubah `SCHEMA_RAW_SHA` dengan betul kepada hash bait fail yang telah diubah. Pemeriksaan Lapisan 1 bebas tetap gagal (`LAYER1_SCHEMA_MISMATCH`), kerana manifest tidak berubah.

### 15.2 Ujian yang tidak dijalankan atau tidak boleh dijalankan di sini

| Perkara | Status | Sebab |
| --- | --- | --- |
| Eksport n8n sebenar melalui `--output=-` | **NOT EXECUTED** | Di luar skop. Tiada n8n dijalankan |
| Penanda kekal selepas import/export n8n | **NOT EXECUTED** | Sama seperti di atas. Andaian kekal **UNVERIFIED** |
| Struktur eksport `nodes[].parameters.jsCode` | **UNVERIFIED** | Struktur dalam fail mock dibina oleh harness mengikut andaian. Ia bukan eksport n8n |
| Runtime Code node n8n dan Ajv | **NOT EXECUTED** | Di luar skop |
| Penjanaan positif daripada lima fail sumber sebenar | **NOT EXECUTED** | Fail `baseline`, `t1`, `t2`, `t3` tidak wujud dalam persekitaran ini. Hanya `schema` tersedia |
| Lapisan 1 raw untuk `baseline`, `t1`, `t2`, `t3` | **NOT VERIFIED** | Bait asal tidak tersedia |
| Lapisan 2 approved untuk fixtur | **NOT VERIFIED** | Fixtur sebenar tidak tersedia. Ujian mock tidak membuktikan nilai diluluskan |
| Pemilihan `ensure_ascii=False` terhadap nilai diluluskan | **NOT VERIFIED** | Skema sebenar tiada aksara bukan ASCII, jadi kedua-dua konvensyen menghasilkan hash yang sama. Fixtur tidak tersedia |
| `FULL_CHAIN VERIFIED` | **NOT PERFORMED** | Tiada set bukti penuh dalam persekitaran ini |
| Sintaks nod mock yang dijana | **Dijalankan sebagai syntax-only** | `node --check` pada nod mock. Tiada kod dijalankan |

Nota: seksyen 6.4.6 mengandungi baris sejarah (contoh: "Penjanaan berjaya", "23,393 bait") daripada sesi terdahulu. Baris itu tidak dapat dihasilkan semula dalam sesi ini kerana fail sumber fixtur tidak tersedia, dan ia tidak dikemas kini oleh v10.

### 15.3 Hasil Lapisan 1 dan 2 yang diperiksa semula

| Perkara | Hasil | Sifat |
| --- | --- | --- |
| Hash raw fail `schema` daripada salinan `docs/canonical-sitespec-schema-v1.1.json` dalam store | sepadan `0c184dce…` | Pengiraan semula pada fail sebenar hari ini. Ia tidak membuktikan asal-usul fail sebagai sumber asal, dan provenance tetap perlu disahkan |
| `canon_sha` pada objek skema sebenar | sepadan `ce403cb9…` | Pengiraan semula. Kedua-dua konvensyen menghasilkan nilai yang sama kerana tiada aksara bukan ASCII |
| Empat hash raw fixtur dan empat hash Lapisan 2 fixtur | tidak diperiksa | Fail tidak tersedia. Status kekal **NOT VERIFIED** |

Lapisan 1 untuk empat fixtur kekal **NOT VERIFIED** pada Stage B seperti dalam v9.

## 16. Audit dokumentasi v10 dan status

Pemeriksaan dokumen yang dijalankan: kod seksyen 6.4.2 dan 6.4.4 diganti dengan kod yang diuji dan ditandakan `v10`; templat 6.4.5 disamakan dengan `TEMPLATE` penjana yang diuji (sebelum itu berbeza daripada penjana); semua nilai Lapisan 1 dan Lapisan 2 dibandingkan dengan nilai yang diluluskan dalam harness (V0); semua rujukan `build-a7-node.py (v9)` dan `extract-and-verify.py (v9)` dalam kod hidup telah diganti; v1–v9 tidak diubah (hash dibandingkan).

Status governance tidak berubah:

* Manifest: **PENDING_OWNER_CONFIRMATION**. Manifest tidak diluluskan oleh v10.
* Lapisan 1: disalin daripada laporan A2–A6. Ia tidak dijana semula daripada semua fail asal dalam sesi ini. Hanya `schema` diperiksa semula terhadap salinan dalam store.
* Baseline: payload execution 80. Identiti W1 kekal **NOT VERIFIED**.
* SD-01: **ACCEPTED**.
* SD-02: **DEFERRED**.
* Blocker 3: **OPEN / NOT VERIFIED**. Lima kriteria penutupan kekal berasingan. v10 tidak menutup Blocker 3.
* M0–M5: **NOT AUTHORIZED**.
* A7: **PROPOSED — NOT AUTHORIZED FOR EXECUTION**.
* Dialect: **OPEN**.

Tiga andaian export kekal **UNVERIFIED** dan tidak ditukar menjadi fakta:
1. `--output=-` mengeluarkan stdout dalam n8n 2.43.8.
2. Struktur eksport mengandungi `nodes[].parameters.jsCode`.
3. Komen dan penanda literal kekal selepas import/export.

Ujian mock dalam seksyen 15 tidak mengesahkan mana-mana andaian ini.

Dapatan v10 tidak menaikkan status provenance. Penyimpanan nilai dalam fail manifest tidak membuktikan provenance. Lulus harness tidak membuktikan kebenaran nilai yang diluluskan.

## 17. Log pembetulan v11

| # | Isu dalam v10 | Pembetulan v11 | Nilai hash berubah? |
| --- | --- | --- | --- |
| A | Seksyen 7.2 melabel hash A3 sebagai "hash bait fail asal" | 7.2a/7.2b/7.2c memisahkan schema (hash fail sebenar) daripada fixtur (JSON kanonik + `\n`) | **Tidak** |
| B | 7.4a.1 menyenaraikan fixtur L1 sebagai mutasi A3 tanpa menyatakan bentuk serialisasi | Setiap baris fixtur L1 kini menyatakan "JSON kanonik + `\n`" dan "tiada fail fixtur" | **Tidak** |
| C | 7.4a.2 menyatakan "Lapisan 1 mengukur bait fail" untuk semua objek | Dibatasi kepada schema; fixtur diterangkan sebagai dua serialisasi objek yang sama | **Tidak** |
| D | 7.4a.4 tidak merekod batasan `ensure_ascii` | Dua baris baharu: `ensure_ascii` tidak dapat dibezakan; skrip penjanaan asal tidak disimpan | **Tidak** |
| E | Tiada bahagian kustodi manifest selepas semakan provenance | 7.4a.6 baharu: tiada fail manifest, salinan `/tmp` lapuk, tiada checksum | **Tidak** |
| F | Nama medan `layer1_raw_sha256` mengelirukan untuk fixtur | Nama dikekalkan untuk keserasian; nota tafsiran ditambah dalam 6.4.3 | **Tidak** |
| G | Komen templat 6.4.5 menyebut "raw file bytes" untuk semua Lapisan 1 | Komen dikemas kini untuk menyatakan fixtur bukan hash fail | **Tidak** |

### 17.1 Pemeriksaan dokumentasi yang dijalankan

1. Kesepuluh-sepuluh rentetan hash dibandingkan dengan v10: **sama, bait demi bait**. Tiada pengiraan semula dan tiada penggantian.
2. Schema L1 diterangkan sebagai SHA-256 bait fail sebenar; schema L2 sebagai JSON kanonik tanpa baris baru.
3. Fixtur L1 diterangkan sebagai JSON kanonik + satu baris baru; fixtur L2 sebagai JSON kanonik sahaja.
4. Batasan `ensure_ascii` dinyatakan secara eksplisit dalam 0w, 7.2, 7.4a.4 dan 17.
5. Identiti W1 kekal **NOT VERIFIED**.
6. Manifest kekal **PENDING_OWNER_CONFIRMATION**; tiada manifest berdiri sendiri dicipta.
7. A7 kekal **PROPOSED — NOT AUTHORIZED FOR EXECUTION**.
8. v1–v10 tidak diubah (hash fail dibandingkan).

### 17.2 Apa yang **tidak** dibuktikan oleh v11

v11 ialah pembetulan dokumentasi. Ia tidak menjalankan sebarang ujian, tidak mengesahkan Ajv, tidak menjalankan eksport n8n, dan tidak menutup Blocker 3. Ia tidak menaikkan taraf provenance: penerbitan semula hash membuktikan objek yang dibina semula sepadan dengan nilai yang diluluskan, bukan bahawa objek itu berasal daripada sumber yang dipercayai.

### 17.3 Status governance selepas v11

| Item | Status |
| --- | --- |
| A7 v10 | Baseline sejarah yang diterima |
| A7 v11 | Baseline cadangan yang dibetulkan dokumentasinya |
| A7 execution | **NOT AUTHORIZED** |
| Manifest approval | **PENDING_OWNER_CONFIRMATION** |
| Blocker 3 | **OPEN / NOT VERIFIED** |
| M0–M5 | **NOT AUTHORIZED** |
| Dialect | **OPEN** |

Mencipta v11 tidak memberi kuasa menjalankan A7 dan tidak menutup Blocker 3. Lima kriteria penutupan Blocker 3 kekal berasingan.

## 18. Pengekalan batasan v10

Semua batasan v10 dikekalkan tanpa dilemahkan:

| Batasan | Status dalam v11 |
| --- | --- |
| Kelulusan manifest masih pending | **PENDING_OWNER_CONFIRMATION** |
| Identiti W1 baseline | **NOT VERIFIED** — execution 80 tidak dilabel W1 |
| Empat fail fixtur sumber | **Tidak wujud** sebagai fail projek |
| Pilihan sejarah `ensure_ascii` | **Tidak dapat dibezakan** daripada hash sedia ada |
| Andaian eksport n8n 1: `--output=-` mengeluarkan stdout | **UNVERIFIED** |
| Andaian eksport n8n 2: struktur mengandungi `nodes[].parameters.jsCode` | **UNVERIFIED** |
| Andaian eksport n8n 3: komen/penanda kekal selepas import/export | **UNVERIFIED** |
| Eksport/import n8n sebenar | **Tidak diuji** |
| Runtime nod Code n8n | **Tidak diuji** |
| Validasi Ajv sebenar | **Tidak dijalankan** |
| Hasil ujian mock tempatan | **Bukan bukti runtime n8n** |
| A7 | **NOT AUTHORIZED** |

## 19. Log pembetulan v12

| # | Isu dalam v11 dan rekod terdahulu | Pembetulan v12 | Nilai hash berubah? |
| --- | --- | --- | --- |
| A | `internal/sitespec-historical-version.md` baris 36: "There is no workflow version id" | Dibatalkan oleh bukti pemilik: `$.workflowVersionId` hadir. Rekod itu ditanda SUPERSEDED, bukan ditulis semula | **Tidak** |
| B | Baris 40: atribusi hanya melalui cap masa + `activeVersionId`, penunjuk "ABSENT" | Kini **VERIFIED** melalui penunjuk langsung; cap masa kekal sebagai sokongan konsisten | **Tidak** |
| C | Dakwaan bahawa versi tepat tidak dapat dikenal pasti | Dibatalkan: versi ialah `c413686f-5ef0-452f-9b55-f1d9075455de` | **Tidak** |
| D | Rantaian provenance tidak menunjukkan pautan versi secara langsung | 7.4a.7 baharu dengan rajah dan jadual atribusi sumber | **Tidak** |

**Penting tentang atribusi bukti.** Penunjuk `workflowVersionId` **tidak** disahkan secara bebas oleh Cursor. Bacaan Cursor melalui `get_workflow_execution` dan `search_workflow_executions` memulangkan subset medan yang dinormalkan tanpa medan itu. v12 merekodkannya sebagai bukti yang dibekalkan pemilik, dan tidak mendakwa pengesahan bebas.

### 19.1 Pemeriksaan dokumentasi yang dijalankan

1. Kesepuluh-sepuluh rentetan hash dibandingkan dengan v11: **sama, bait demi bait**.
2. `c413686f-5ef0-452f-9b55-f1d9075455de` dan `$.workflowVersionId` direkod secara konsisten dalam 0x, 7.4a.7 dan 19.
3. Tiada pernyataan yang tinggal dalam v12 yang mengatakan rekod execution tiada penunjuk versi. Pernyataan itu hanya muncul dalam log pembetulan sebagai rekod sejarah yang dibatalkan.
4. `OFFICIAL_W1_FIXTURE = NOT VERIFIED` dikekalkan.
5. Manifest kekal `PENDING_OWNER_CONFIRMATION`.
6. A7 kekal `PROPOSED — NOT AUTHORIZED FOR EXECUTION`.
7. v11 tidak diubah (hash dibandingkan).

### 19.2 Had yang tidak berubah

Pembetulan ini ialah pembetulan **provenance versi**, bukan pengesahan fixtur. Yang berikut kekal seperti dalam v11:

| Perkara | Status |
| --- | --- |
| Fixtur rasmi W1 | **NOT VERIFIED** — tiada definisi, kriteria atau kelulusan |
| Manifest | **PENDING_OWNER_CONFIRMATION** |
| Fail fixtur sumber | Tidak wujud |
| Pilihan `ensure_ascii` sejarah | Tidak dapat dibezakan daripada hash |
| Tiga andaian eksport n8n | **UNVERIFIED** |
| Eksport/import n8n sebenar, runtime Code node, Ajv | **Tidak diuji** |
| A7 | **NOT AUTHORIZED FOR EXECUTION** |
| Blocker 3 | **OPEN / NOT VERIFIED** |
| M0–M5 | **NOT AUTHORIZED** |
| Dialect | **OPEN** |

## 20. Status governance selepas v12

| Item | Status |
| --- | --- |
| A7 v11 | Baseline dokumentasi terdahulu yang diterima |
| A7 v12 | Baseline cadangan dengan pembetulan provenance versi |
| A7 execution | **NOT AUTHORIZED** |
| Blocker 3 | **OPEN / NOT VERIFIED** |
| M0–M5 | **NOT AUTHORIZED** |
| Dialect | **OPEN** |
| Manifest approval | **PENDING_OWNER_CONFIRMATION** |
| Official W1 fixture | **NOT VERIFIED** |

v12 tidak memberi kuasa menjalankan A7, tidak menutup Blocker 3, dan tidak melantik execution #80 sebagai fixtur rasmi.
