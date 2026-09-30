import { useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import {
    SearchableSelect,
    type SearchableOption,
} from '@/components/searchable-select';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type {
    KaprodiOption,
    ProdiRow,
} from '@/pages/akademik/components/prodi-columns';
import { store, update } from '@/routes/akademik/prodi';

type ProdiFormDialogProps = {
    /** Baris yang diubah; `null` berarti membuat prodi baru. */
    prodi: ProdiRow | null;
    kaprodiOptions: KaprodiOption[];
    onClose: () => void;
};

const FIELD_ORDER = ['nama', 'kaprodi_id'] as const;

const fieldId = (name: string) => `prodi-form-${name}`;

/**
 * Dialog tambah/ubah prodi. Halaman merender dialog ini hanya saat terbuka,
 * sehingga state form selalu dimulai dari data terbaru.
 */
export function ProdiFormDialog({
    prodi,
    kaprodiOptions,
    onClose,
}: ProdiFormDialogProps) {
    const isEdit = prodi !== null;
    const { data, setData, post, put, processing, errors } = useForm({
        nama: prodi?.nama ?? '',
        kaprodi_id: prodi?.kaprodi_id == null ? '' : String(prodi.kaprodi_id),
    });

    const kaprodiChoices: SearchableOption[] = kaprodiOptions.map((d) => ({
        value: String(d.id),
        label: d.nama,
    }));

    const submit = () => {
        const options = {
            preserveScroll: true,
            onSuccess: onClose,
            onError: (errs: Record<string, string>) => {
                const first = FIELD_ORDER.find((name) => errs[name]);

                if (first) {
                    document.getElementById(fieldId(first))?.focus();
                }
            },
        };

        if (prodi) {
            put(update.url({ prodi: prodi.id }), options);
        } else {
            post(store.url(), options);
        }
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Ubah prodi' : 'Tambah prodi'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEdit ? (
                            <>
                                Ubah program studi <strong>{prodi.nama}</strong>
                                .
                            </>
                        ) : (
                            'Tambahkan program studi baru beserta kaprodinya.'
                        )}
                    </DialogDescription>
                </DialogHeader>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        submit();
                    }}
                    className="grid gap-4 py-2"
                >
                    <div className="grid gap-2">
                        <Label htmlFor={fieldId('nama')}>Nama</Label>
                        <Input
                            id={fieldId('nama')}
                            value={data.nama}
                            onChange={(e) => setData('nama', e.target.value)}
                            disabled={processing}
                            aria-invalid={Boolean(errors.nama) || undefined}
                        />
                        <InputError message={errors.nama} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor={fieldId('kaprodi_id')}>Kaprodi</Label>
                        <SearchableSelect
                            id={fieldId('kaprodi_id')}
                            value={data.kaprodi_id}
                            onChange={(v) => setData('kaprodi_id', v)}
                            options={kaprodiChoices}
                            placeholder="Belum ditentukan"
                            searchPlaceholder="Cari nama dosen..."
                            emptyText="Dosen tidak ditemukan."
                            clearLabel="Belum ditentukan"
                            disabled={processing}
                            invalid={Boolean(errors.kaprodi_id)}
                        />
                        <InputError message={errors.kaprodi_id} />
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={processing}
                        >
                            Batal
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
