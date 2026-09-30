import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { DosenRow } from '@/pages/akademik/components/dosen-columns';
import { destroy } from '@/routes/akademik/dosen';

type DosenDeleteDialogProps = {
    dosen: DosenRow;
    onClose: () => void;
};

/** Dosen yang masih menjadi PA mahasiswa ditolak server; dialog menjelaskannya. */
export function DosenDeleteDialog({ dosen, onClose }: DosenDeleteDialogProps) {
    const { delete: deleteForm, processing } = useForm({});
    const terpakai = dosen.jumlah_mahasiswa_pa > 0;

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus dosen</DialogTitle>
                    <DialogDescription>
                        {terpakai ? (
                            <>
                                <strong>{dosen.nama}</strong> masih menjadi
                                dosen PA bagi {dosen.jumlah_mahasiswa_pa}{' '}
                                mahasiswa sehingga tidak bisa dihapus. Ganti
                                dosen PA mahasiswanya terlebih dahulu.
                            </>
                        ) : (
                            <>
                                Dosen <strong>{dosen.nama}</strong> akan dihapus
                                permanen. Aksi ini tidak dapat dibatalkan.
                            </>
                        )}
                    </DialogDescription>
                </DialogHeader>
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
                        type="button"
                        variant="destructive"
                        disabled={terpakai || processing}
                        onClick={() =>
                            deleteForm(destroy.url({ dosen: dosen.id }), {
                                preserveScroll: true,
                                onSuccess: onClose,
                            })
                        }
                    >
                        {processing ? 'Menghapus...' : 'Hapus dosen'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
