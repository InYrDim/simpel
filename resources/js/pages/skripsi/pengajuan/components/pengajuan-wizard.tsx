import { router, useForm, usePage } from '@inertiajs/react';
import { Check, Send } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { resubmit, store } from '@/routes/skripsi/pengajuan';
import { draft as templateDraft } from '@/routes/skripsi/pengajuan/template';
import {
    bacaDraft,
    hapusDraft,
    kunciDraft,
    simpanDraft,
} from './draft-storage';
import { LangkahBerkas } from './langkah-berkas';
import { LangkahJudul } from './langkah-judul';
import { LangkahPratinjau } from './langkah-pratinjau';
import {
    galatBerkas,
    judulAwalKosong,
    judulDariItem,
    semuaJudulLengkap,
    type JudulForm,
    type JudulItem,
    type KategoriOption,
} from './types';

const LANGKAH = ['Isi judul', 'Template & berkas', 'Periksa & kirim'] as const;
const JUMLAH_LANGKAH = LANGKAH.length;

type Props = {
    mode: 'baru' | 'revisi';
    disabled: boolean;
    pengajuanId: number | null;
    judulTerkini: JudulItem[];
    kategoriOptions: KategoriOption[];
};

const bacaCookie = (nama: string): string | null => {
    const cocok = document.cookie
        .split('; ')
        .find((c) => c.startsWith(`${nama}=`));

    return cocok ? decodeURIComponent(cocok.slice(nama.length + 1)) : null;
};

export function PengajuanWizard({
    mode,
    disabled,
    pengajuanId,
    judulTerkini,
    kategoriOptions,
}: Props) {
    const revisi = mode === 'revisi';
    const userId = usePage<{ auth: { user: { id: number } } }>().props.auth.user
        .id;
    const kunci = kunciDraft(userId, mode, pengajuanId);

    // Draft dibaca sekali saat mount; dialog tertutup sehingga tidak memicu
    // ketidakcocokan hidrasi.
    const [draftAwal] = useState(() =>
        typeof window === 'undefined' ? null : bacaDraft(kunci),
    );

    const [open, setOpen] = useState(false);
    const [konfirmasiBatal, setKonfirmasiBatal] = useState(false);
    const [step, setStep] = useState(draftAwal?.step ?? 1);
    const [draftDipulihkan, setDraftDipulihkan] = useState<string | null>(
        draftAwal?.updatedAt ?? null,
    );
    const [tampilkanKosong, setTampilkanKosong] = useState(false);
    const [galatUnduh, setGalatUnduh] = useState<string | null>(null);
    const [sedangMengunduh, setSedangMengunduh] = useState(false);

    const { data, setData, post, processing, errors, clearErrors } = useForm<{
        juduls: JudulForm[];
        berkas: File | null;
    }>({
        juduls:
            draftAwal?.juduls ??
            (revisi ? judulDariItem(judulTerkini) : judulAwalKosong()),
        berkas: null,
    });

    // Simpan draft otomatis setiap isian/langkah berubah. Menutup dialog
    // tidak menghapusnya; hanya "Batalkan" atau kirim sukses yang menghapus.
    useEffect(() => {
        if (open) {
            simpanDraft(kunci, data.juduls, step);
        }
    }, [open, kunci, data.juduls, step]);

    const galatKlienBerkas = galatBerkas(data.berkas);
    const judulSiap = semuaJudulLengkap(data.juduls);
    const berkasSiap = data.berkas !== null && galatKlienBerkas === null;

    const ubahJudul = (
        index: number,
        field: keyof JudulForm,
        value: string,
    ) => {
        setData(
            'juduls',
            data.juduls.map((j, i) =>
                i === index ? { ...j, [field]: value } : j,
            ),
        );
    };

    const lompatKe = (target: number) => {
        setStep(Math.min(JUMLAH_LANGKAH, Math.max(1, target)));
    };

    const lanjut = () => {
        if (step === 1 && !judulSiap) {
            setTampilkanKosong(true);

            return;
        }

        lompatKe(step + 1);
    };

    const tutup = () => {
        setOpen(false);
    };

    const batalkan = () => {
        hapusDraft(kunci);
        setData({
            juduls: revisi ? judulDariItem(judulTerkini) : judulAwalKosong(),
            berkas: null,
        });
        clearErrors();
        setStep(1);
        setTampilkanKosong(false);
        setDraftDipulihkan(null);
        setKonfirmasiBatal(false);
        setOpen(false);
    };

    const unduhTemplate = async () => {
        setGalatUnduh(null);
        setSedangMengunduh(true);

        try {
            const respons = await fetch(templateDraft.url(), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/octet-stream',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': bacaCookie('XSRF-TOKEN') ?? '',
                },
                body: JSON.stringify({ juduls: data.juduls }),
            });

            if (!respons.ok) {
                throw new Error('gagal');
            }

            const url = URL.createObjectURL(await respons.blob());
            const a = document.createElement('a');
            a.href = url;
            a.download = 'template-pengajuan.docx';
            a.click();
            URL.revokeObjectURL(url);
        } catch {
            setGalatUnduh('Template gagal diunduh. Coba lagi.');
        } finally {
            setSedangMengunduh(false);
        }
    };

    const kirim = () => {
        post(
            revisi && pengajuanId !== null
                ? resubmit.url({ pengajuan: pengajuanId })
                : store.url(),
            {
                forceFormData: true,
                onSuccess: () => {
                    hapusDraft(kunci);
                    setOpen(false);
                    setStep(1);
                    router.reload({
                        only: [
                            'pengajuan',
                            'judulTerkini',
                            'riwayat',
                            'riwayatStatus',
                        ],
                    });
                },
                onError: (galat) => {
                    const kunciGalat = Object.keys(galat);

                    if (kunciGalat.some((k) => k.startsWith('juduls'))) {
                        setTampilkanKosong(true);
                        setStep(1);
                    } else if (kunciGalat.includes('berkas')) {
                        setStep(2);
                    }
                },
            },
        );
    };

    const galatUmum = Object.entries(errors)
        .filter(([k]) => !k.startsWith('juduls') && k !== 'berkas')
        .map(([, pesan]) => pesan);

    return (
        <>
            <Button
                size="sm"
                disabled={revisi ? false : disabled}
                onClick={() => setOpen(true)}
            >
                <Send className="mr-2 size-4" />
                {revisi
                    ? 'Kirim Revisi'
                    : draftAwal
                      ? 'Lanjutkan Draft'
                      : 'Ajukan Judul'}
            </Button>

            <Dialog
                open={open}
                onOpenChange={(v) => (v ? setOpen(true) : tutup())}
            >
                <DialogContent className="max-h-[90vh] max-w-2xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>
                            {revisi ? 'Kirim Revisi' : 'Ajukan Judul Skripsi'}
                        </DialogTitle>
                        <DialogDescription>
                            {revisi
                                ? 'Perbaiki judul dan berkas sesuai catatan, lalu kirim ulang.'
                                : 'Ikuti 3 langkah berikut. Isian Anda tersimpan otomatis sebagai draft di browser ini.'}
                        </DialogDescription>
                    </DialogHeader>

                    <Stepper aktif={step} onPilih={lompatKe} />

                    {draftDipulihkan && step === 1 && (
                        <p className="text-muted-foreground rounded-md border border-dashed p-2 text-xs">
                            Melanjutkan draft terakhir (
                            {new Date(draftDipulihkan).toLocaleString('id-ID')}
                            ).
                        </p>
                    )}

                    {step === 1 && (
                        <LangkahJudul
                            juduls={data.juduls}
                            kategoriOptions={kategoriOptions}
                            tampilkanKosong={tampilkanKosong}
                            errors={errors}
                            onChange={ubahJudul}
                        />
                    )}

                    {step === 2 && (
                        <LangkahBerkas
                            berkas={data.berkas}
                            galatKlien={galatKlienBerkas}
                            galatServer={errors.berkas}
                            perluPilihUlang={draftDipulihkan !== null}
                            sedangMengunduh={sedangMengunduh}
                            galatUnduh={galatUnduh}
                            onUnduhTemplate={unduhTemplate}
                            onPilihBerkas={(file) => setData('berkas', file)}
                        />
                    )}

                    {step === 3 && (
                        <LangkahPratinjau
                            juduls={data.juduls}
                            kategoriOptions={kategoriOptions}
                            berkas={data.berkas}
                            galatUmum={galatUmum}
                            onUbahJudul={() => lompatKe(1)}
                            onUbahBerkas={() => lompatKe(2)}
                        />
                    )}

                    <DialogFooter className="flex items-center gap-2 sm:justify-between">
                        <Button
                            type="button"
                            variant="ghost"
                            className="text-destructive"
                            onClick={() => setKonfirmasiBatal(true)}
                        >
                            Batal
                        </Button>
                        <div className="flex gap-2">
                            {step > 1 && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => lompatKe(step - 1)}
                                >
                                    Kembali
                                </Button>
                            )}
                            {step < JUMLAH_LANGKAH ? (
                                <Button
                                    type="button"
                                    onClick={lanjut}
                                    disabled={step === 2 && !berkasSiap}
                                >
                                    Lanjut
                                </Button>
                            ) : (
                                <Button
                                    type="button"
                                    onClick={kirim}
                                    disabled={
                                        processing || !judulSiap || !berkasSiap
                                    }
                                >
                                    {processing
                                        ? 'Mengirim...'
                                        : revisi
                                          ? 'Kirim Revisi'
                                          : 'Kirim Pengajuan'}
                                </Button>
                            )}
                        </div>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Dialog open={konfirmasiBatal} onOpenChange={setKonfirmasiBatal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Batalkan pengajuan?</DialogTitle>
                        <DialogDescription>
                            Draft yang sudah Anda isi akan dihapus dari browser
                            ini. Untuk menyimpan draft, cukup tutup dialog
                            dengan tombol X.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setKonfirmasiBatal(false)}
                        >
                            Kembali mengisi
                        </Button>
                        <Button variant="destructive" onClick={batalkan}>
                            Hapus draft
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

function Stepper({
    aktif,
    onPilih,
}: {
    aktif: number;
    onPilih: (langkah: number) => void;
}) {
    return (
        <ol className="flex items-center gap-2 text-sm">
            {LANGKAH.map((label, i) => {
                const nomor = i + 1;
                const selesai = nomor < aktif;

                return (
                    <li key={label} className="flex flex-1 items-center gap-2">
                        <button
                            type="button"
                            disabled={nomor > aktif}
                            onClick={() => onPilih(nomor)}
                            className="flex items-center gap-2 disabled:cursor-default"
                        >
                            <span
                                className={
                                    'flex size-6 shrink-0 items-center justify-center rounded-full border text-xs ' +
                                    (nomor === aktif
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : selesai
                                          ? 'border-primary text-primary'
                                          : 'text-muted-foreground')
                                }
                            >
                                {selesai ? <Check className="size-3" /> : nomor}
                            </span>
                            <span
                                className={
                                    nomor === aktif
                                        ? 'font-medium'
                                        : 'text-muted-foreground hidden sm:inline'
                                }
                            >
                                {label}
                            </span>
                        </button>
                    </li>
                );
            })}
        </ol>
    );
}
