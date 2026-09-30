import { FileDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { MAKS_BERKAS_MB } from './types';
import InputError from '@/components/input-error';

type Props = {
    berkas: File | null;
    galatKlien: string | null;
    galatServer: string | undefined;
    /** Draft dipulihkan tetapi PDF belum dipilih ulang. */
    perluPilihUlang: boolean;
    sedangMengunduh: boolean;
    galatUnduh: string | null;
    onUnduhTemplate: () => void;
    onPilihBerkas: (file: File | null) => void;
};

export function LangkahBerkas({
    berkas,
    galatKlien,
    galatServer,
    perluPilihUlang,
    sedangMengunduh,
    galatUnduh,
    onUnduhTemplate,
    onPilihBerkas,
}: Props) {
    return (
        <div className="flex flex-col gap-5">
            <ol className="text-muted-foreground list-decimal space-y-1 pl-5 text-sm">
                <li>Unduh template pengajuan di bawah.</li>
                <li>Isi dan lengkapi, lalu simpan/ekspor sebagai PDF.</li>
                <li>
                    Unggah PDF tersebut di sini (maksimal {MAKS_BERKAS_MB} MB).
                </li>
            </ol>

            <div className="flex flex-col items-start gap-2">
                <Button
                    type="button"
                    variant="outline"
                    onClick={onUnduhTemplate}
                    disabled={sedangMengunduh}
                >
                    <FileDown className="mr-2 size-4" />
                    {sedangMengunduh
                        ? 'Menyiapkan template...'
                        : 'Unduh template pengajuan (.docx)'}
                </Button>
                <InputError message={galatUnduh ?? undefined} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="berkas">Berkas pengajuan (PDF)</Label>
                <Input
                    id="berkas"
                    type="file"
                    accept="application/pdf"
                    onChange={(e) => onPilihBerkas(e.target.files?.[0] ?? null)}
                />
                {perluPilihUlang && berkas === null && (
                    <p className="text-sm text-amber-600 dark:text-amber-400">
                        Draft Anda dipulihkan, tetapi berkas tidak bisa disimpan
                        di browser. Silakan pilih ulang berkas PDF.
                    </p>
                )}
                {berkas && !galatKlien && (
                    <p className="text-muted-foreground text-sm">
                        {berkas.name} ({(berkas.size / 1024).toFixed(0)} KB)
                    </p>
                )}
                <InputError message={galatKlien ?? galatServer} />
            </div>
        </div>
    );
}
