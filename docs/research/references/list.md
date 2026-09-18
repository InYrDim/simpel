
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

**Catatan gap:** saya belum menemukan penelitian Indonesia yang secara eksplisit menggabungkan embedding + LLM reranker untuk judul skripsi (celah novelty-mu kemungkinan besar masih kosong — bagus untuk kontribusi). Sumber #12 (Ditto Whitening) dan #13 (500 judul, NLP+Word Embeddings) adalah dua yang paling wajib kamu baca detail karena paling dekat dengan rencana risetmu.
