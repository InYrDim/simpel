import { JUMLAH_JUDUL, type JudulForm } from './types';

/**
 * Draft pengajuan di localStorage. Berkas PDF tidak ikut disimpan
 * (localStorage tidak bisa menyimpan File) — mahasiswa memilih ulang.
 */
export type DraftPengajuan = {
    versi: 1;
    juduls: JudulForm[];
    step: number;
    updatedAt: string;
};

const VERSI = 1;

export const kunciDraft = (
    userId: number,
    mode: 'baru' | 'revisi',
    pengajuanId: number | null,
): string =>
    mode === 'revisi' && pengajuanId !== null
        ? `skripsi.pengajuan.draft.revisi.${pengajuanId}`
        : `skripsi.pengajuan.draft.baru.${userId}`;

const adalahJudulForm = (v: unknown): v is JudulForm => {
    if (typeof v !== 'object' || v === null) {
        return false;
    }

    const j = v as Record<string, unknown>;

    return (
        typeof j.judul === 'string' &&
        typeof j.deskripsi === 'string' &&
        typeof j.topik === 'string' &&
        typeof j.kategori_id === 'string'
    );
};

const adalahDraft = (v: unknown): v is DraftPengajuan => {
    if (typeof v !== 'object' || v === null) {
        return false;
    }

    const d = v as Record<string, unknown>;

    return (
        d.versi === VERSI &&
        Array.isArray(d.juduls) &&
        d.juduls.length === JUMLAH_JUDUL &&
        d.juduls.every(adalahJudulForm) &&
        typeof d.step === 'number' &&
        typeof d.updatedAt === 'string'
    );
};

export function bacaDraft(kunci: string): DraftPengajuan | null {
    try {
        const mentah = window.localStorage.getItem(kunci);

        if (mentah === null) {
            return null;
        }

        const parsed: unknown = JSON.parse(mentah);

        return adalahDraft(parsed) ? parsed : null;
    } catch {
        return null;
    }
}

export function simpanDraft(
    kunci: string,
    juduls: JudulForm[],
    step: number,
): void {
    try {
        const draft: DraftPengajuan = {
            versi: VERSI,
            juduls,
            step,
            updatedAt: new Date().toISOString(),
        };

        window.localStorage.setItem(kunci, JSON.stringify(draft));
    } catch {
        // Mode privat / kuota penuh: draft tidak tersimpan, form tetap jalan.
    }
}

export function hapusDraft(kunci: string): void {
    try {
        window.localStorage.removeItem(kunci);
    } catch {
        // abaikan
    }
}
