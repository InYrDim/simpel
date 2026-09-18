Baik, ini matriks penelitian (research matrix) dari semua referensi yang sudah dikumpulkan, dipecah per tingkatan agar tetap terbaca.

## 🔹 Matriks Tier 1 — Jurnal UNM

|No|Penulis (Tahun)|Metode|Dataset/Objek|Hasil Utama|Relevansi/Gap|
|---|---|---|---|---|---|
|1|Nasrullah (2024), _INTEC UNM_|TF-IDF + Cosine Similarity|Judul penelitian mhs Fakultas Ilmu Komputer|Akurasi rata-rata 89,7%|Baseline metodologis langsung sebanding dengan topikmu; belum pakai embedding/LLM|
|2|_INTEC UNM_ Vol.4 No.2 (2025)|Perbandingan embedding BERT, ALBERT, Sentence-BERT (tanpa fine-tuning)|Esai berbahasa Indonesia (adaptasi ASAP)|QWK hingga 0,9 pakai IndoBERT/SBERT|Menunjukkan SBERT unggul di teks Indonesia; belum diuji untuk domain judul skripsi|

## 🔹 Matriks Tier 2 — Jurnal Nasional Indonesia

|No|Penulis/Sumber (Tahun)|Metode|Dataset/Objek|Hasil Utama|Relevansi/Gap|
|---|---|---|---|---|---|
|3|FIKOM UMI (2020)|Algoritma Smith-Waterman|4 judul skripsi TI 2018–2019|Berhasil deteksi kecocokan string|Sampel sangat kecil; string-matching, bukan semantik|
|4|Infotekjar (2018)|Algoritma Winnowing|117 judul skripsi|11 judul mirip pada threshold ≥20%|Deteksi leksikal; tidak tangkap kemiripan makna|
|5|Sistem Informasi Pengajuan Skripsi|Algoritma Oliver (fungsi PHP)|217 judul training, 10 judul uji|Threshold diterima pada kemiripan 60%|Berbasis string similarity murni|
|6|EDUTIC-Trunojoyo|Levenshtein Distance|Multi-kategori, 6 target database|Kemiripan 28,5–42,85% tergantung kategori|Cocok untuk typo/variasi kecil, lemah pada parafrase|
|7|Teknika-Polsri (2024)|TF-IDF + Fuzzy Matching (Algoritma Oliver)|Database judul skripsi|Auto-reject pada kemiripan >60%|Ide threshold otomatis relevan untuk fitur sistemmu|
|8|UMS (eprints)|TF-IDF + Cosine Similarity|Data _myskripsi_ UMS, Django+PostgreSQL|SUS score 77,17 (layak pakai)|Contoh integrasi ke sistem informasi skripsi nyata; arsitektur mirip rencanamu|
|9|JIEET-UNESA|Levenshtein Distance + Cosine Similarity (hybrid)|Jurnal publikasi skripsi|Deteksi kemiripan dokumen penuh, bukan hanya judul|Bisa jadi referensi hybrid method, tapi objek beda (dokumen, bukan judul)|
|10|Unismuh (2024)|SVM + TF-IDF (NLP)|Database judul_skripsi|Akurasi memadai (SVM classifier)|Machine learning klasik; bisa dibandingkan sebagai baseline ML vs deep learning|
|11|LTR2-Univ. Almuslim|K-Nearest Neighbor + Cosine Similarity|Naskah dokumen skripsi|Identifikasi kemiripan naskah penuh|Pendekatan klasifikasi, bukan retrieval; skala terbatas|
|12|**JJEEE-UNG**|**IndoBERT embedding + Ditto Whitening**|Judul penelitian berbahasa Indonesia|Ditto Whitening ↑ isotropi embedding & akurasi deteksi kemiripan|⭐ **Paling relevan** — bukti nyata masalah anisotropi embedding pada judul, solusinya applicable ke sistemmu|
|13|**JPI-Widina**|NLP + Word Embeddings + Cosine Similarity|**500 judul skripsi TI, 5 tahun**|Akurasi hingga 85%|⭐ Skala data & metode paling dekat dengan rencana risetmu — jadi pembanding utama|
|14|JEPIN-Untan|Fine-tuning IndoBERT (deteksi GenAI) + cosine similarity (esai)|Teks GenAI & jawaban esai|Akurasi fine-tuning 93,91%|Menunjukkan potensi fine-tuning IndoBERT domain-spesifik — relevan untuk opsi "domain-adapted embedding"|
|15|ResearchGate (Deli Husada)|Cosine Similarity + TF-IDF|Judul TA, Institut Kesehatan|43% ditolak, 53% diterima|Ilustrasi real-world decision threshold di kampus|

## 🔹 Matriks Tier 3 — Internasional

|16|Reimers & Gurevych (2019)|Sentence-BERT (Siamese network)|Pencarian pasangan mirip turun dari 65 jam → 5 detik, akurasi setara BERT|Fondasi wajib untuk arsitektur embedding sistemmu|
|17|Wang et al. (2024)|Multilingual E5 (mE5)|State-of-the-art retrieval lintas bahasa termasuk Indonesia|Kandidat model embedding produksi|
|18|Sturua et al. (2024)|jina-embeddings-v3 (Matryoshka Repr. Learning)|Unggul dari mE5-large di tugas multilingual|Alternatif model embedding, dimensi fleksibel|
|19|arXiv 2604.18835|LLM-as-a-Judge sensitivity testing|Skor similarity dari LLM sensitif terhadap struktur dokumen & bias posisi, bukan murni makna|⭐ Justifikasi kuat: LLM jangan jadi skorer numerik tunggal|
|20|Semantic-KG (2025)|Benchmark similarity via knowledge graph|Tidak ada satu metode LLM-as-judge yang unggul konsisten di semua domain|Mendukung pendekatan hybrid (embedding + LLM)|
|21|SemScore (2024)|Perbandingan metrik embedding vs LLM (G-Eval)|Embedding-based metric (SEMSCORE) korelasi kuat dgn human judgment|Dasar evaluasi metodologis: bandingkan skor embedding vs skor LLM|
|22|Survei RAG Meets LLMs|Retrieve-Rerank-Generate (R2G)|Kombinasi retrieval + rerank ↑ robustness hasil|Kerangka arsitektur untuk fitur "LLM sebagai reranker"|
|23|arXiv 2506.23136|LLM reranker pada RAG QA|Reranker menilai keselarasan semantik sesungguhnya, bukan sekadar similarity permukaan|Justifikasi teknis langsung untuk desain sistemmu|
|24|Wang et al. (2026)|jina-reranker-v3.5 (0.6B, hybrid attention)|nDCG@10 63.20 di BEIR, matching model 4B dengan 7x lebih sedikit parameter|⭐ Kandidat reranker produksi terbaru; multilingual termasuk Indonesian|
|25|Cohere (2025)|Cohere Rerank v4|API-based multilingual reranker (100+ bahasa), support structured data|Alternatif API-based tanpa self-host; cocok untuk prototyping cepat|
|26|cassador (2024)|indobert-base-p2-nli-v2 (SentenceTransformer)|IndoBERT fine-tuned untuk NLI, 768 dimensi, cocok untuk semantic similarity|Kandidat embedding model Indonesian yang sudah di-domain-adapt untuk NLI|
|27|firqaaa (2024)|indo-sentence-bert-large|SentenceTransformer khusus Indonesia, 2048 dimensi, MNR loss|Alternatif embedding dengan dimensi lebih besar untuk capturasi makna yang lebih kaya|
|28|Wongso et al. (2024)|NusaBERT (LazarusNLP)|IndoBERT extended ke 12 bahasa daerah via vocab expansion, SOTA di IndoNLU|Model multilingual Indonesia terbaru; bisa jadi base untuk embedding domain akademik|
|29|Lestari (2025)|BERT + Cosine Similarity (UIN SGD)|F1 0.83, deteksi parafrase & terjemahan lintas bahasa|Baseline tambahan untuk perbandingan; menunjukkan BERT murni tanpa Sentence-BERT|
|30|Fathuddin et al. (2025)|SBERT + ontologi untuk dokumen skripsi PDF (RRJ)|MRR 1.0, Precision 0.80, Recall 0.92|Referensi arsitektur retrieval dokumen skripsi; ontologi bisa jadi tambahan future work|
|31|Hakiki et al. (2025)|SBERT untuk plagiarisme psikologi (Cerdika)|Accuracy 53.3%, kuat di copy-paste, lemah di mosaic|Menunjukkan batas SBERT murni tanpa reranker — justifikasi tambahan untuk layer reranker|

---

**Catatan gap (diperbarui 19 Sep 2026):** penelitian Indonesia yang pakai embedding untuk judul skripsi sekarang ada 2: (1) JPI-Widina (Word Embeddings + Cosine, 500 judul, 85%) dan (2) Media Elektrik UNM 2025 (IndoSBERT + Cosine, 114 judul, 93%). **Keduanya berhenti di cosine similarity — belum ada yang menggabungkan embedding + LLM reranker untuk domain judul skripsi Indonesia.** Kombinasi embedding + reranker masih jadi celah novelty yang valid, tapi klaimnya perlu disesuaikan: bukan "pertama yang pakai embedding untuk judul skripsi", tapi "pertama yang menggabungkan embedding dengan LLM reranker untuk meningkatkan akurasi deteksi kemiripan judul skripsi di Indonesia". Sumber #24 (Media Elektrik) dan #27 (cassador/indobert-base-p2-nli-v2) adalah dua yang paling wajib kamu baca detail karena paling dekat dengan rencana risetmu.