import { useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
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
import type { DosenRow } from '@/pages/akademik/components/dosen-columns';
import { store, update } from '@/routes/akademik/dosen';

type DosenFormDialogProps = {
    /** Baris yang diubah; `null` berarti membuat dosen baru. */
    dosen: DosenRow | null;
    onClose: () => void;
};

const FIELDS = [
    { name: 'nama', label: 'Nama' },
    { name: 'nip', label: 'NIP' },
    { name: 'bidang', label: 'Bidang' },
] as const;

const fieldId = (name: string) => `dosen-form-${name}`;

/**
 * Dialog tambah/ubah dosen. Halaman merender dialog ini hanya saat terbuka,
 * sehingga state form selalu dimulai dari data terbaru.
 */
export function DosenFormDialog({ dosen, onClose }: DosenFormDialogProps) {
    const isEdit = dosen !== null;
    const { data, setData, post, put, processing, errors } = useForm({
        nama: dosen?.nama ?? '',
        nip: dosen?.nip ?? '',
        bidang: dosen?.bidang ?? '',
    });

    const submit = () => {
        const options = {
            preserveScroll: true,
            onSuccess: onClose,
            onError: (errs: Record<string, string>) => {
                const first = FIELDS.find((field) => errs[field.name]);

                if (first) {
                    document.getElementById(fieldId(first.name))?.focus();
                }
            },
        };

        if (dosen) {
            put(update.url({ dosen: dosen.id }), options);
        } else {
            post(store.url(), options);
        }
    };

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Ubah dosen' : 'Tambah dosen'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEdit ? (
                            <>
                                Ubah data dosen <strong>{dosen.nama}</strong>.
                            </>
                        ) : (
                            'Tambahkan dosen referensi baru untuk penugasan PA, validator, pembimbing, dan penguji.'
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
                    {FIELDS.map((field) => (
                        <div key={field.name} className="grid gap-2">
                            <Label htmlFor={fieldId(field.name)}>
                                {field.label}
                            </Label>
                            <Input
                                id={fieldId(field.name)}
                                value={data[field.name]}
                                inputMode={
                                    field.name === 'nip' ? 'numeric' : undefined
                                }
                                onChange={(e) =>
                                    setData(field.name, e.target.value)
                                }
                                disabled={processing}
                                aria-invalid={
                                    Boolean(errors[field.name]) || undefined
                                }
                            />
                            <InputError message={errors[field.name]} />
                        </div>
                    ))}

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
