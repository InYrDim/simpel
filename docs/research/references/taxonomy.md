Berikut daftar metode yang muncul dari seluruh referensi yang sudah dikumpulkan, dikelompokkan per kategori:

## 1. String-Matching / Lexical Algorithms

Metode klasik yang membandingkan karakter atau string secara langsung, tanpa memahami makna.

- **Smith-Waterman** — pencocokan sekuens karakter berbasis matriks (dipakai FIKOM UMI)
- **Winnowing** — deteksi kemiripan berbasis k-gram dan fingerprinting dokumen (dipakai di 3 penelitian berbeda: Infotekjar, Unasman, JACIS)
- **Levenshtein Distance** — edit distance (insertion/deletion/substitution) (STMIK MIC Cikarang, hybrid dengan Cosine di UNESA)
- **Algoritma Oliver** (fungsi bawaan PHP) — menghasilkan skor kemiripan dua string (Sistem Informasi Pengajuan Skripsi, Polsri)
- **Rabin-Karp** — disebut sebagai pembanding dalam beberapa related work

**Karakteristik:** cepat, ringan, cocok untuk skala kecil (puluhan–ratusan judul), tapi lemah menangkap parafrase atau sinonim.

## 2. Statistical Weighting + Machine Learning Klasik

Representasi teks berbasis frekuensi kata, dikombinasikan dengan model ML tradisional.

- **TF-IDF** (Term Frequency-Inverse Document Frequency) — hampir selalu jadi fondasi representasi teks (muncul di 6+ penelitian: Nasrullah/UNM, UMS, Polsri, Unismuh, dll.)
- **Cosine Similarity** — metode pengukuran jarak paling umum digunakan bersama TF-IDF (dipakai di hampir semua penelitian nasional Indonesia yang dikumpulkan)
- **Support Vector Machine (SVM)** — dikombinasikan dengan TF-IDF untuk klasifikasi kemiripan (Unismuh)
- **K-Nearest Neighbor (KNN)** — klasifikasi kemiripan naskah berdasarkan jarak ke data training (Univ. Almuslim)
- **Fuzzy Matching** — dikombinasikan dengan TF-IDF untuk threshold otomatis (Polsri)

**Karakteristik:** representasi berbasis kemunculan kata (bag-of-words), belum menangkap makna kontekstual — dua judul bisa berbeda kata tapi sama makna (atau sebaliknya) dan sistem ini akan salah menilai.

## 3. Deep Learning / Embedding-Based (Semantic)

Representasi teks kontekstual menggunakan model transformer — ini kategori paling relevan dengan rencanamu.

- **BERT / IndoBERT** — model transformer terlatih korpus Indonesia, dipakai sebagai basis embedding (INTEC UNM, JEPIN-Untan, JJEEE-UNG)
- **Sentence-BERT (SBERT)** — arsitektur siamese network untuk menghasilkan embedding kalimat yang bisa dibandingkan langsung dengan cosine similarity (Reimers & Gurevych 2019 — fondasi internasional; dipakai juga di INTEC UNM)
- **Mean Pooling pada last hidden state IndoBERT** — teknik ekstraksi embedding dari token-level ke sentence-level (JJEEE-UNG)
- **Ditto Whitening** — teknik pasca-proses untuk memperbaiki anisotropi ruang embedding IndoBERT, terbukti meningkatkan akurasi deteksi kemiripan judul (JJEEE-UNG) ⭐
- **Word2Vec** — disebut sebagai metode pembanding di beberapa related work (belum kontekstual seperti BERT)
- **LaBSE** (Language-agnostic BERT Sentence Embedding) — embedding lintas 109 bahasa
- **Multilingual E5 (mE5)** — model embedding multibahasa, unggul di benchmark retrieval MIRACL
- **jina-embeddings-v3** — embedding multibahasa dengan Matryoshka Representation Learning (dimensi embedding bisa dikompres tanpa banyak kehilangan performa)

**Karakteristik:** menangkap makna semantik, bukan hanya kemunculan kata — inilah yang membedakan sistemmu dari kebanyakan penelitian nasional yang masih pakai TF-IDF.

## 4. LLM-Based (Reranker / Judge)

Kategori terbaru dan paling sedikit dieksplorasi di konteks judul skripsi Indonesia — ini titik novelty-mu.

- **LLM-as-a-Judge** — LLM memberi skor kemiripan langsung (0-5 atau persentase); terbukti rentan bias posisi dan tidak stabil antar model (Semantic Needles, Semantic-KG)
- **LLM Reranker (2-stage retrieval)** — retrieval awal pakai embedding, lalu LLM mengurutkan ulang top-K berdasarkan relevansi semantik sesungguhnya (RA-LLMs survey, structured RAG paper) ⭐
- **Retrieve-Rerank-Generate (R2G)** — kerangka kerja menggabungkan multi-retriever + rerank untuk hasil lebih robust
- **Chain-of-Thought reranking (RankCoT)** — LLM menghasilkan reasoning eksplisit sebelum memutuskan ranking, meningkatkan explainability

**Karakteristik:** biaya lebih tinggi per panggilan, tapi punya kemampuan reasoning kualitatif yang tidak dimiliki cosine similarity murni — cocok dipakai di tahap akhir (top-K), bukan seluruh database.

---

**Ringkasan posisi metodologis:** mayoritas penelitian Indonesia (khususnya tier nasional) masih di kategori 1–2 (string-matching & TF-IDF+Cosine). Baru 1 penelitian (Ditto Whitening, UNG) yang masuk kategori 3 secara spesifik untuk judul penelitian. Belum ada yang ditemukan menggabungkan kategori 3+4 sekaligus — ini konfirmasi ulang bahwa kombinasi **embedding + LLM reranker** benar-benar jadi celah novelty yang realistis untuk skripsimu.