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
import type { MahasiswaRow } from '@/pages/akademik/components/mahasiswa-columns';
import { destroy } from '@/routes/akademik/mahasiswa';

type MahasiswaDeleteDialogProps = {
    mahasiswa: MahasiswaRow;
    onClose: () => void;
};

/**
 * Hapus permanen menghilangkan profil akademik, jadi pengguna harus
 * mengetik NIM-nya. Server memeriksa ulang NIM yang sama.
 */
export function MahasiswaDeleteDialog({
    mahasiswa,
    onClose,
}: MahasiswaDeleteDialogProps) {
    const {
        data,
        setData,
        delete: deleteForm,
        processing,
        errors,
    } = useForm({ konfirmasi_nim: '' });

    const matches = data.konfirmasi_nim.trim() === mahasiswa.nim;

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus mahasiswa</DialogTitle>
                    <DialogDescription>
                        Profil akademik <strong>{mahasiswa.nama}</strong> akan
                        dihapus permanen. Akun login tetap ada, tetapi data di
                        modul lain yang merujuk mahasiswa ini (misalnya
                        pengajuan skripsi) tidak ikut terhapus. Bila hanya ingin
                        menonaktifkan, ubah statusnya menjadi Nonaktif.
                    </DialogDescription>
                </DialogHeader>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        deleteForm(destroy.url({ mahasiswa: mahasiswa.id }), {
                            preserveScroll: true,
                            onSuccess: onClose,
                        });
                    }}
                    className="grid gap-4 py-2"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="mhs-delete-nim">
                            Ketik NIM <strong>{mahasiswa.nim}</strong> untuk
                            mengonfirmasi
                        </Label>
                        <Input
                            id="mhs-delete-nim"
                            value={data.konfirmasi_nim}
                            onChange={(e) =>
                                setData('konfirmasi_nim', e.target.value)
                            }
                            autoComplete="off"
                            disabled={processing}
                            aria-invalid={
                                Boolean(errors.konfirmasi_nim) || undefined
                            }
                        />
                        <InputError message={errors.konfirmasi_nim} />
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
                        <Button
                            type="submit"
                            variant="destructive"
                            disabled={!matches || processing}
                        >
                            {processing ? 'Menghapus...' : 'Hapus mahasiswa'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
