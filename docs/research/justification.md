# Justifikasi Penelitian & Gap Novelty

## Latar Belakang

Pengajuan judul skripsi di perguruan tinggi Indonesia masih bergantung pada proses manual: mahasiswa mengajukan judul, kemudian validator (dosen pembimbing/kaprodi) memeriksa secara manual apakah judul sudah ada atau mirip dengan penelitian sebelumnya. Proses ini menimbulkan dua masalah utama:

1. **Risiko duplikasi** — judul yang mirip bisa lolos karena keterbatasan waktu dan arsip yang tidak terdigitalisasi dengan baik.
2. **Beban administratif** — validator harus memeriksa ratusan judul secara manual, terutama di program studi dengan angkatan besar.

Solusi yang sudah banyak dieksplorasi di Indonesia adalah sistem deteksi kemiripan berbasis **string-matching** (Smith-Waterman, Winnowing, Levenshtein, Oliver) atau **TF-IDF + Cosine Similarity**. Kedua pendekatan ini relatif cepat dan ringan, tapi lemah menangkap kemiripan **semantik** — dua judul yang berbeda kata tapi sama makna (atau sebaliknya) akan salah dievaluasi.

---

## Gap yang Ditemukan

### Gap 1: Indonesia masih dominan pendekatan klasik

Mayoritas penelitian nasional yang mengumpulkan **masih di kategori 1–2** (string-matching & TF-IDF+Cosine). Hanya 1 penelitian (Ditto Whitening, UNG) yang mencapai kategori 3 (embedding-based) secara spesifik untuk judul penelitian. Belum ada yang menggabungkan kategori 3 (embedding) + kategori 4 (LLM reranker) untuk domain ini.

### Gap 2: Embedding + LLM Reranker belum dieksplorasi untuk judul skripsi Indonesia

Berdasarkan 32 referensi yang telah dikumpulkan (termasuk paper terbaru 2025–2026):

- **Media Elektrik UNM (2025)** — pakai IndoSBERT + Cosine Similarity untuk judul skripsi, akurasi 93%. Ini baseline kompetisi langsung dari kampusmu, tapi **hanya sampai cosine similarity**.
- **JPI-Widina (2025)** — Word Embeddings + Cosine, 500 judul, akurasi 85%. Berhenti di cosine similarity.
- **Ditto Whitening (UNG)** — IndoBERT + whitening untuk isotropi embedding, tapi tidak ada reranker.
- **jina-reranker-v3.5 (2026)** — reranker multilingual terbaru, belum diuji untuk domain judul skripsi Indonesia.

**Kesimpulan:** kombinasi **sentence embedding + LLM reranker** untuk deteksi kemiripan judul skripsi di Indonesia **masih belum ada**. Ini adalah celah novelty yang valid untuk diteliti.

---

## Perbedaan Eksplisit dengan Media Elektrik UNM 2025

| Aspek                 | Media Elektrik UNM (2025)       | Rencana Riset Ini                                   |
| --------------------- | ------------------------------- | --------------------------------------------------- |
| **Metode utama**      | IndoSBERT + Cosine Similarity   | Sentence Embedding + LLM Reranker (2-stage)         |
| **Layer tambahan**    | Tidak ada                       | LLM reranker untuk reasoning kualitatif             |
| **Dataset**           | 114 judul skripsi               | [Sesuaikan dengan data kampusmu]                    |
| **Arsitektur sistem** | Aplikasi desktop/web standalone | Sistem terintegrasi berbasis Laravel                |
| **Evaluasi**          | Akurasi, F1, SUS                | [Sesuaikan — bandingkan skor embedding vs skor LLM] |

---

## Pilihan Metodologis

### Embedding Model

| Opsi                                 | Kelebihan                                       | Kekurangan                                        |
| ------------------------------------ | ----------------------------------------------- | ------------------------------------------------- |
| **IndoSBERT** (baseline UNM)         | Sudah terbukti di domain yang sama, akurasi 93% | Bukan model terbaru, performa bisa ditandingi     |
| **cassador/indobert-base-p2-nli-v2** | Fine-tuned untuk NLI, cocok untuk similarity    | Performa STS belum diukur secara ekstensif        |
| **jina-embeddings-v3**               | Multilingual, Matryoshka (dimensi fleksibel)    | Belum diuji khusus untuk Indonesian academic text |
| **mE5-large**                        | SOTA retrieval multilingual                     | Model besar, resource intensive                   |

**Rekomendasi awal:** pakai **IndoSBERT** sebagai baseline (karena langsung komparabel dengan Media Elektrik UNM), lalu bandingkan dengan **cassador/indobert-base-p2-nli-v2** untuk melihat apakah fine-tuning NLI memberikan peningkatan.

### Reranker Model

| Opsi                    | Kelebihan                                  | Kekurangan                                       |
| ----------------------- | ------------------------------------------ | ------------------------------------------------ |
| **jina-reranker-v3.5**  | 0.6B, multilingual, SOTA BEIR, open-weight | Perlu GPU untuk inference yang nyaman            |
| **Cohere Rerank v4**    | API-based, tanpa self-host                 | Bergantung pada API eksternal, biaya per-request |
| **Qwen3-Reranker-0.6B** | Open-source, Qwen family                   | Performa lebih rendah dari jina-v3.5             |

**Rekomendasi awal:** **jina-reranker-v3.5** — model terbaru, multilingual, dan performa terbaik di skala 0.6B. Jika resource terbatas, pertimbangkan API-based Cohere untuk prototyping.

---

## Hipotesis

Kombinasi sentence embedding + LLM reranker akan meningkatkan akurasi deteksi kemiripan judul skripsi dibanding embedding + cosine similarity murni, karena:

1. **Embedding menangkap makna semantik** — berbeda dari string-matching yang hanya melihat karakter.
2. **LLM reranker menambah reasoning kualitatif** — tidak hanya menghitung jarak vektor, tapi memahami konteks dan nuansa judul.
3. **2-stage architecture efisien** — embedding untuk retrieval massal, reranker hanya untuk top-K, sehingga biaya inference terkontrol.

---

## Batasan yang Diharapkan

- Evaluasi pada satu program studi (Teknik Informatika UNM) — generalisasi ke program lain perlu penelitian lanjut.
- Dataset terbatas pada judul yang tersedia di arsip kampus — bisa ditambah dengan judul dari tahun sebelumnya.
- Reranker berjalan di top-K (misal 20) — bukan seluruh database — untuk menjaga latensi yang acceptable.
