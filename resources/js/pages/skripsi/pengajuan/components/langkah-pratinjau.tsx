import { Pencil } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { JudulForm, KategoriOption } from './types';

type Props = {
    juduls: JudulForm[];
    kategoriOptions: KategoriOption[];
    berkas: File | null;
    galatUmum: string[];
    onUbahJudul: () => void;
    onUbahBerkas: () => void;
};

export function LangkahPratinjau({
    juduls,
    kategoriOptions,
    berkas,
    galatUmum,
    onUbahJudul,
    onUbahBerkas,
}: Props) {
    const urlBerkas = useUrlBerkas(berkas);

    const namaKategori = (id: string): string =>
        kategoriOptions.find((k) => String(k.id) === id)?.nama ?? '-';

    return (
        <div className="flex flex-col gap-4">
            <p className="text-muted-foreground text-sm">
                Periksa kembali. Pratinjau ini hanya ada di browser Anda — belum
                ada yang dikirim sampai Anda menekan <strong>Kirim</strong>.
            </p>

            {galatUmum.length > 0 && (
                <div className="rounded-lg border border-red-300 p-3 text-sm text-red-600 dark:text-red-400">
                    {galatUmum.map((g) => (
                        <p key={g}>{g}</p>
                    ))}
                </div>
            )}

            <section className="grid gap-3 rounded-lg border p-3">
                <div className="flex items-center justify-between">
                    <p className="font-medium">Judul yang diajukan</p>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={onUbahJudul}
                    >
                        <Pencil className="mr-1 size-3.5" />
                        Ubah
                    </Button>
                </div>
                {juduls.map((j, i) => (
                    <div key={i} className="grid gap-1 rounded-md border p-3">
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant="outline">{i + 1}</Badge>
                            <span className="font-medium">{j.judul}</span>
                        </div>
                        <p className="text-muted-foreground text-sm whitespace-pre-line">
                            {j.deskripsi}
                        </p>
                        <p className="text-muted-foreground text-xs">
                            Topik: {j.topik} · Kategori:{' '}
                            {namaKategori(j.kategori_id)}
                        </p>
                    </div>
                ))}
            </section>

            <section className="grid gap-3 rounded-lg border p-3">
                <div className="flex items-center justify-between">
                    <p className="font-medium">Berkas pengajuan</p>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={onUbahBerkas}
                    >
                        <Pencil className="mr-1 size-3.5" />
                        Ubah
                    </Button>
                </div>
                {berkas && urlBerkas ? (
                    <>
                        <p className="text-muted-foreground text-sm">
                            {berkas.name} ({(berkas.size / 1024).toFixed(0)} KB)
                        </p>
                        <iframe
                            src={urlBerkas}
                            title="Pratinjau berkas pengajuan"
                            className="h-96 w-full rounded-md border"
                        />
                    </>
                ) : (
                    <p className="text-muted-foreground text-sm">
                        Belum ada berkas dipilih.
                    </p>
                )}
            </section>
        </div>
    );
}

/** Object URL lokal untuk pratinjau PDF; dibebaskan saat berkas berganti. */
function useUrlBerkas(berkas: File | null): string | null {
    const [url, setUrl] = useState<string | null>(null);

    useEffect(() => {
        if (berkas === null) {
            return;
        }

        const objectUrl = URL.createObjectURL(berkas);
        setUrl(objectUrl);

        return () => {
            URL.revokeObjectURL(objectUrl);
            setUrl(null);
        };
    }, [berkas]);

    return berkas === null ? null : url;
}
