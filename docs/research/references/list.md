## 🔹 Tier 1 — Jurnal UNM

1. **Integrasi TF-IDF dan Algoritma Cosine Similarity untuk Deteksi Tingkat Kemiripan Judul Penelitian** — Asmaul Husnah Nasrullah, _INTEC Journal_ (journal.unm.ac.id), 2024. Penelitian ini mengintegrasikan TF-IDF dan Cosine Similarity untuk mendeteksi kemiripan judul penelitian mahasiswa, dengan rata-rata akurasi hingga 89,7%. Paling relevan sebagai **baseline metodologis** kamu — studi kasus mirip persis dengan topikmu.  
   🔗 https://journal.unm.ac.id/index.php/INTEC/article/download/5810/3667

2. **Automated essay grading / perbandingan model embedding (BERT, ALBERT, Sentence-BERT)** — _Information Technology Education Journal_, INTEC UNM, Vol. 4 No. 2 (2025). Studi ini membandingkan performa embedding IndoBERT dan model Sentence-BERT lain pada tugas penilaian esai otomatis berbahasa Indonesia, mencapai skor QWK 0,9. Berguna untuk justifikasi pemilihan model embedding domain Indonesia.  
   🔗 https://journal.unm.ac.id/index.php/INTEC/article/download/8069/5099

## 🔹 Tier 2 — Jurnal Nasional Indonesia

**Kelompok algoritma klasik (string-matching / TF-IDF) — untuk baseline & tinjauan pustaka:**

3. Deteksi Kemiripan Judul Skripsi Menggunakan Algoritma Smith-Waterman (FIKOM UMI, 2020) — https://www.researchgate.net/publication/364490552
4. Sistem Pendeteksian Kemiripan Judul Skripsi Menggunakan Algoritma Winnowing — diuji pada 117 judul skripsi dan menemukan 11 judul dengan kemiripan ≥20%. — https://jurnal.uisu.ac.id/index.php/infotekjar/article/view/165
5. Deteksi Tingkat Kemiripan Judul Menggunakan Algoritma Oliver pada Sistem Informasi Pengajuan Skripsi — https://www.researchgate.net/publication/367002535
6. Deteksi Kemiripan Judul Skripsi Menggunakan Algoritma Levenshtein Distance (STMIK MIC Cikarang) — https://journal.trunojoyo.ac.id/edutic/article/view/10051
7. Kombinasi Algoritma TF-IDF dan Fuzzy Matching untuk Deteksi Kemiripan Judul (Polsri, 2024) — sistem dirancang untuk menolak otomatis judul dengan kemiripan di atas 60%. — https://jurnal.polsri.ac.id/index.php/teknika/article/view/8871
8. Pengembangan Sistem Pencarian pada Aplikasi Skripsi (UMS) — menggunakan pembobotan TF-IDF dan cosine similarity, dibangun dengan Django dan PostgreSQL. — https://eprints.ums.ac.id/120772/1/Naskah%20Publikasi.pdf
9. Deteksi Kemiripan Dokumen Publikasi Skripsi (UNESA) — hybrid Levenshtein + Cosine Similarity — https://journal.unesa.ac.id/index.php/jieet/article/download/5510/pdf
10. Deteksi Tingkat Kemiripan Judul Skripsi menggunakan SVM + TF-IDF (Unismuh) — https://digilibadmin.unismuh.ac.id/upload/42073-Full_Text.pdf
11. Text Mining KNN untuk Klasifikasi Dokumen Skripsi (Univ. Almuslim) — https://jurnal.umuslim.ac.id/index.php/LTR2/article/download/1090/1122

**Kelompok embedding/deep learning — paling relevan untuk kontribusi novelty-mu:**

12. **Optimasi Embedding IndoBERT dengan Ditto Whitening** (UNG, sangat relevan!) — penelitian ini menemukan bahwa sebagian besar judul penelitian tampak memiliki kemiripan tinggi meski berbeda secara semantik akibat anisotropi embedding, dan Ditto Whitening terbukti efektif meningkatkan isotropi embedding IndoBERT sekaligus akurasi sistem deteksi kemiripan judul. Ini **directly on-topic** dengan rencana risetmu — wajib dibaca lengkap.  
    🔗 https://ejurnal.ung.ac.id/index.php/jjeee/article/viewFile/35554/12492

13. **Sistem Pendeteksi Kemiripan Judul Skripsi Berbasis Web Menggunakan NLP dan Word Embeddings** — menggunakan 500 judul skripsi Teknik Informatika selama lima tahun terakhir dengan cosine similarity, mencapai akurasi deteksi hingga 85%. Referensi ukuran dataset yang bagus untuk pembandingmu.  
    🔗 https://jurnal.penerbitwidina.com/index.php/JPI/article/view/1964

14. IndoBERT fine-tuning untuk deteksi teks GenAI + penilaian esai berbasis cosine similarity — fine-tuning pada model pre-trained IndoBERT mencapai akurasi 93,91% untuk deteksi GenAI, embedding yang sama dipakai untuk mengukur kemiripan semantik jawaban. — JEPIN, https://jurnal.untan.ac.id/index.php/jepin/article/viewFile/93221

15. Uji Kemiripan Kalimat Judul Tugas Akhir dengan Cosine Similarity dan TF-IDF (Deli Husada) — https://www.researchgate.net/publication/367868215

## 🔹 Tier 3 — Internasional

**Foundational embedding models:**

16. **Reimers & Gurevych (2019)** — _Sentence-BERT: Sentence Embeddings using Siamese BERT-Networks_. Menurunkan waktu pencarian pasangan paling mirip dari 65 jam (BERT) menjadi ~5 detik dengan SBERT, tanpa kehilangan akurasi. Ini rujukan wajib untuk justifikasi arsitektur embedding.  
    🔗 https://arxiv.org/pdf/1908.10084
17. **Multilingual E5 Text Embeddings: A Technical Report** (Wang et al., 2024) — model embedding multibahasa yang relevan untuk domain Indonesia. 🔗 https://arxiv.org/pdf/2402.05672
18. **jina-embeddings-v3** — multilingual embedding dengan Matryoshka Representation Learning, alternatif model yang lebih baru. 🔗 https://arxiv.org/pdf/2409.10173

**Krusial untuk justifikasi "LLM bukan penilai similarity numerik langsung" (mendukung diskusi kita sebelumnya):**

19. **Semantic Needles in Document Haystacks** — skor kesamaan semantik dari LLM sensitif terhadap struktur dokumen, koherensi konteks, dan identitas model — bukan hanya perubahan semantik itu sendiri, termasuk bias posisi. Ini bukti kuat kenapa LLM sebaiknya jadi reranker/explainer, bukan skorer utama.  
    🔗 https://arxiv.org/html/2604.18835v1
20. **Semantic-KG** — metode similarity semantik saat ini cenderung menangkap bentuk sintaksis/leksikal ketimbang konten semantik, dan tidak ada satu metode LLM-as-a-judge yang konsisten unggul di semua domain. 🔗 https://arxiv.org/pdf/2511.19925
21. **SemScore** — perbandingan metrik embedding vs LLM-based metric (G-Eval) dalam korelasi terhadap human judgment. 🔗 https://arxiv.org/pdf/2401.17072

**Untuk arsitektur retrieval + LLM reranker (opsi #3 dari 3 judul sebelumnya):**

22. **A Survey on RAG Meets LLMs** — mengusulkan metode Retrieve-Rerank-Generate (R2G) yang menggabungkan hasil retrieval dari beberapa pendekatan dengan operasi rerank untuk meningkatkan robustness hasil retrieval. 🔗 https://arxiv.org/html/2405.06211v1
23. **LLM-Assisted QA menggunakan Structured RAG** — LLM reranker menilai bukan hanya kemiripan permukaan tapi keselarasan semantik sesungguhnya antara query dan tiap konteks yang diambil. 🔗 https://arxiv.org/pdf/2506.23136

---

## 🔹 Tier 1 — Jurnal UNM (Lanjutan)

24. **Development of Sentence Similarity Detection Application with Semantic Similarity and Machine Learning Approaches (Case Study: Student Thesis Title)** — Muhammad Abdul Hafizh Fathuddin, Eka Prakarsa Mandyartha, Afina Lina Nurlaili, _Jurnal Media Elektrik_, Vol. 23 No. 1 (2025). Penelitian ini mengembangkan aplikasi deteksi kemiripan judul skripsi menggunakan **IndoSBERT** (IndoBERT-base + Sentence-BERT architecture), mencapai akurasi 93% dan F1-score 0.90, dengan dataset 114 judul skripsi. Evaluasi SUS score 80 (excellent). **Baseline kompetisi langsung — dari UNM, tahun ini, topik persis sama.** Perbedaan dengan rencana risetmu: hanya pakai cosine similarity, belum ada LLM reranker. 🔗 https://journal.unm.ac.id/index.php/mediaelektrik/article/view/10503

---

## 🔹 Tier 3 — Internasional (Lanjutan)

**Reranker model terbaru (2025–2026):**

25. **jina-reranker-v3.5** (Wang et al., 2026, arXiv 2607.18152) — 0.6B parameter listwise reranker dengan hybrid attention (3 sliding-window + 2 global layers). nDCG@10 63.20 di BEIR, matching model 4B dengan 7x lebih sedikit parameter. Support multilingual termasuk Indonesian. **Kandidat reranker produksi.** 🔗 https://arxiv.org/abs/2509.25085

26. **Cohere Rerank v4** (Cohere, 2025) — API-based reranker multilingual (100+ bahasa termasuk Indonesian), support structured/semi-structured data. Cocok jika kamu ingin solusi API tanpa self-host. 🔗 https://docs.cohere.com/docs/reranking-with-cohere.mdx

**Fine-tuned IndoBERT untuk NLI/semantic similarity:**

27. **cassador/indobert-base-p2-nli-v2** — SentenceTransformer based on indobert-base-p2, fine-tuned pada dataset IndoNLI untuk semantic textual similarity. 768 dimensi, cocok untuk pairwise similarity Indonesian. 🔗 https://huggingface.co/cassador/indobert-base-p2-nli-v2

28. **firqaaa/indo-sentence-bert-large** — SentenceTransformer khusus Indonesia, 2048 dimensi, trained dengan Multiple Negative Ranking Loss. 🔗 https://huggingface.co/firqaaa/indo-sentence-bert-large

29. **NusaBERT** (Wongso et al., 2024, arXiv 2403.01817) — IndoBERT yang di-extend ke 12 bahasa daerah Indonesia via vocabulary expansion + continued pre-training. SOTA di IndoNLU, NusaX, NusaWrites. Base model: 111M params, Large: 337M. 🔗 https://arxiv.org/abs/2403.01817

**Penelitian nasional lain (2024–2025) yang relevan:**

30. **Perangkat bantu deteksi similarity menggunakan BERT dan Cosine Similarity** — Lestari Putri Fuji (2025), UIN Sunan Gunung Djati Bandung. Integrasi BERT + cosine similarity untuk deteksi kemiripan teks, F1 score 0.83, mampu menangkap parafrase dan terjemahan lintas bahasa. 🔗 https://digilib.uinsgd.ac.id/104017/

31. **Penerapan Sentence-BERT dan Cosine Similarity untuk Pencarian Semantik Dokumen Skripsi dalam Format PDF** — Fathuddin et al. (2025), _Rancang Jurnal_ Vol. 8 No. 1. SBERT + cosine similarity (0.7) + ontologi (0.3) untuk pencarian dokumen skripsi PDF. MRR 1.0, Precision 0.80, Recall 0.92. 🔗 https://jurnal.ranahresearch.com/index.php/R2J/article/download/1865/1570

32. **Implementasi Sentence-BERT untuk Deteksi Plagiarisme pada Karya Tulis Ilmiah Psikologi Berbahasa Indonesia** — Ilhan Hakiki et al. (2025), _Cerdika_. SBERT (distiluse-base-multilingual-cased-v1) + cosine similarity untuk deteksi plagiarisme. Global accuracy 53.3%, kuat di copy-paste (100%), lemah di mosaic (0%). 🔗 https://doi.org/10.59141/cerdika.v5i12.2889

---

**Catatan gap (diperbarui 19 Sep 2026):** penelitian Indonesia yang pakai embedding untuk judul skripsi sekarang ada 2: (1) JPI-Widina (Word Embeddings + Cosine, 500 judul, 85%) dan (2) Media Elektrik UNM 2025 (IndoSBERT + Cosine, 114 judul, 93%). **Keduanya berhenti di cosine similarity — belum ada yang menggabungkan embedding + LLM reranker untuk domain judul skripsi Indonesia.** Kombinasi embedding + reranker masih jadi celah novelty yang valid, tapi klaimnya perlu disesuaikan: bukan "pertama yang pakai embedding untuk judul skripsi", tapi "pertama yang menggabungkan embedding dengan LLM reranker untuk meningkatkan akurasi deteksi kemiripan judul skripsi di Indonesia". Sumber #24 (Media Elektrik) dan #27 (cassador/indobert-base-p2-nli-v2) adalah dua yang paling wajib kamu baca detail karena paling dekat dengan rencana risetmu.
