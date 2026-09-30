export type KategoriOption = {
    id: number;
    nama: string;
};

export type JudulItem = {
    id: number;
    urutan: number;
    judul: string;
    deskripsi: string;
    topik: string;
    kategori_id: number | null;
    kategori_nama: string | null;
};

/** Isian satu judul di form; `kategori_id` disimpan sebagai string (Select). */
export type JudulForm = {
    judul: string;
    deskripsi: string;
    topik: string;
    kategori_id: string;
};

export const JUMLAH_JUDUL = 3;
export const MAKS_BERKAS_MB = 5;

export const judulKosong = (): JudulForm => ({
    judul: '',
    deskripsi: '',
    topik: '',
    kategori_id: '',
});

export const judulAwalKosong = (): JudulForm[] =>
    Array.from({ length: JUMLAH_JUDUL }, judulKosong);

export const judulDariItem = (items: JudulItem[]): JudulForm[] =>
    items.map((j) => ({
        judul: j.judul,
        deskripsi: j.deskripsi,
        topik: j.topik,
        kategori_id: j.kategori_id === null ? '' : String(j.kategori_id),
    }));

export const judulLengkap = (j: JudulForm): boolean =>
    j.judul.trim() !== '' &&
    j.deskripsi.trim() !== '' &&
    j.topik.trim() !== '' &&
    j.kategori_id !== '';

export const semuaJudulLengkap = (juduls: JudulForm[]): boolean =>
    juduls.length === JUMLAH_JUDUL && juduls.every(judulLengkap);

/** Pesan galat berkas untuk sisi klien; null bila berkas sah. */
export const galatBerkas = (file: File | null): string | null => {
    if (file === null) {
        return null;
    }

    if (file.type !== 'application/pdf') {
        return 'Berkas harus berformat PDF.';
    }

    if (file.size > MAKS_BERKAS_MB * 1024 * 1024) {
        return `Ukuran berkas maksimal ${MAKS_BERKAS_MB} MB.`;
    }

    return null;
};
