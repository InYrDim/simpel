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
import type { ProdiRow } from '@/pages/akademik/components/prodi-columns';
import { destroy } from '@/routes/akademik/prodi';

type ProdiDeleteDialogProps = {
    prodi: ProdiRow;
    onClose: () => void;
};

/** Prodi yang masih punya mahasiswa ditolak server; dialog menjelaskannya. */
export function ProdiDeleteDialog({ prodi, onClose }: ProdiDeleteDialogProps) {
    const { delete: deleteForm, processing } = useForm({});
    const terpakai = prodi.jumlah_mahasiswa > 0;

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus prodi</DialogTitle>
                    <DialogDescription>
                        {terpakai ? (
                            <>
                                <strong>{prodi.nama}</strong> masih memiliki{' '}
                                {prodi.jumlah_mahasiswa} mahasiswa sehingga
                                tidak bisa dihapus. Pindahkan mahasiswanya ke
                                prodi lain terlebih dahulu.
                            </>
                        ) : (
                            <>
                                Prodi <strong>{prodi.nama}</strong> akan dihapus
                                permanen.
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
                            deleteForm(destroy.url({ prodi: prodi.id }), {
                                preserveScroll: true,
                                onSuccess: onClose,
                            })
                        }
                    >
                        {processing ? 'Menghapus...' : 'Hapus prodi'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
