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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type {
    DosenOption,
    MahasiswaRow,
    ProdiOption,
    StatusOption,
} from '@/pages/akademik/components/mahasiswa-columns';
import { store, update } from '@/routes/akademik/mahasiswa';

export type UserOption = {
    id: number;
    name: string;
    email: string;
};

type MahasiswaFormDialogProps = {
    /** Baris yang diubah; `null` berarti membuat mahasiswa baru. */
    mahasiswa: MahasiswaRow | null;
    userOptions: UserOption[];
    dosenOptions: DosenOption[];
    prodiOptions: ProdiOption[];
    statusOptions: StatusOption[];
    onClose: () => void;
};

const FIELD_ORDER = [
    'user_id',
    'nama',
    'nim',
    'prodi_id',
    'angkatan',
    'dosen_pa_id',
    'status',
] as const;

const fieldId = (name: string) => `mhs-form-${name}`;

function focusFirstError(errors: Record<string, string>) {
    const first = FIELD_ORDER.find((name) => errors[name]);

    if (first) {
        document.getElementById(fieldId(first))?.focus();
    }
}

/**
 * Dialog tambah/ubah mahasiswa. Halaman merender dialog ini hanya saat
 * terbuka, sehingga state form selalu dimulai dari data terbaru.
 */
export function MahasiswaFormDialog({
    mahasiswa,
    userOptions,
    dosenOptions,
    prodiOptions,
    statusOptions,
    onClose,
}: MahasiswaFormDialogProps) {
    const isEdit = mahasiswa !== null;
    const { data, setData, post, put, processing, errors, clearErrors } =
        useForm({
            user_id: '',
            nama: mahasiswa?.nama ?? '',
            nim: mahasiswa?.nim ?? '',
            dosen_pa_id:
                mahasiswa?.dosen_pa_id == null
                    ? ''
                    : String(mahasiswa.dosen_pa_id),
            prodi_id: mahasiswa?.prodi_id ? String(mahasiswa.prodi_id) : '',
            angkatan: mahasiswa?.angkatan ? String(mahasiswa.angkatan) : '',
            status: mahasiswa?.status ?? 'aktif',
        });

    const userChoices: SearchableOption[] = userOptions.map((u) => ({
        value: String(u.id),
        label: u.name,
        hint: u.email,
    }));
    const dosenChoices: SearchableOption[] = dosenOptions.map((d) => ({
        value: String(d.id),
        label: d.nama,
    }));

    const submit = (keepOpen: boolean) => {
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                if (keepOpen) {
                    // Simpan dan tambah lagi: kosongkan identitas, pertahankan
                    // prodi, angkatan, dosen PA, dan status untuk entri berikutnya.
                    setData((prev) => ({
                        ...prev,
                        user_id: '',
                        nama: '',
                        nim: '',
                    }));
                    clearErrors();
                    document.getElementById(fieldId('user_id'))?.focus();
                } else {
                    onClose();
                }
            },
            onError: focusFirstError,
        };

        if (mahasiswa) {
            put(update.url({ mahasiswa: mahasiswa.id }), options);
        } else {
            post(store.url(), options);
        }
    };

    const pickUser = (userId: string) => {
        const chosen = userOptions.find((u) => String(u.id) === userId);

        setData((prev) => ({
            ...prev,
            user_id: userId,
            nama: prev.nama === '' && chosen ? chosen.name : prev.nama,
        }));
    };

    const invalid = (name: keyof typeof errors) => Boolean(errors[name]);

    return (
        <Dialog open onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Ubah mahasiswa' : 'Tambah mahasiswa'}
                    </DialogTitle>
                    <DialogDescription>
                        {isEdit ? (
                            <>
                                Ubah profil akademik{' '}
                                <strong>{mahasiswa.nama}</strong>.
                            </>
                        ) : (
                            'Hubungkan akun user dengan profil akademiknya.'
                        )}
                    </DialogDescription>
                </DialogHeader>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        submit(false);
                    }}
                    className="grid gap-6 py-2"
                >
                    <fieldset className="grid gap-4">
                        <legend className="mb-1 text-sm font-semibold">
                            Identitas
                        </legend>

                        {!isEdit && (
                            <div className="grid gap-2">
                                <Label htmlFor={fieldId('user_id')}>
                                    Akun user
                                </Label>
                                <SearchableSelect
                                    id={fieldId('user_id')}
                                    value={data.user_id}
                                    onChange={pickUser}
                                    options={userChoices}
                                    placeholder="Pilih akun user"
                                    searchPlaceholder="Cari nama atau email..."
                                    emptyText="Semua akun sudah punya profil mahasiswa."
                                    disabled={processing}
                                    invalid={invalid('user_id')}
                                />
                                <InputError message={errors.user_id} />
                            </div>
                        )}

                        <div className="grid gap-2">
                            <Label htmlFor={fieldId('nama')}>Nama</Label>
                            <Input
                                id={fieldId('nama')}
                                value={data.nama}
                                onChange={(e) =>
                                    setData('nama', e.target.value)
                                }
                                disabled={processing}
                                aria-invalid={invalid('nama') || undefined}
                            />
                            <InputError message={errors.nama} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={fieldId('nim')}>NIM</Label>
                            <Input
                                id={fieldId('nim')}
                                value={data.nim}
                                inputMode="numeric"
                                onChange={(e) => setData('nim', e.target.value)}
                                disabled={processing}
                                aria-invalid={invalid('nim') || undefined}
                            />
                            <InputError message={errors.nim} />
                        </div>
                    </fieldset>

                    <fieldset className="grid gap-4">
                        <legend className="mb-1 text-sm font-semibold">
                            Akademik
                        </legend>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="grid gap-2">
                                <Label htmlFor={fieldId('prodi_id')}>
                                    Prodi
                                </Label>
                                <Select
                                    value={data.prodi_id}
                                    onValueChange={(v) =>
                                        setData('prodi_id', v)
                                    }
                                    disabled={processing}
                                >
                                    <SelectTrigger
                                        id={fieldId('prodi_id')}
                                        className="w-full"
                                        aria-invalid={
                                            invalid('prodi_id') || undefined
                                        }
                                    >
                                        <SelectValue placeholder="Pilih prodi" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {prodiOptions.map((p) => (
                                            <SelectItem
                                                key={p.id}
                                                value={String(p.id)}
                                            >
                                                {p.nama}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.prodi_id} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor={fieldId('angkatan')}>
                                    Angkatan
                                </Label>
                                <Input
                                    id={fieldId('angkatan')}
                                    type="number"
                                    min={2000}
                                    max={2100}
                                    value={data.angkatan}
                                    onChange={(e) =>
                                        setData('angkatan', e.target.value)
                                    }
                                    disabled={processing}
                                    aria-invalid={
                                        invalid('angkatan') || undefined
                                    }
                                />
                                <InputError message={errors.angkatan} />
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={fieldId('dosen_pa_id')}>
                                Dosen PA (penasehat akademik)
                            </Label>
                            <SearchableSelect
                                id={fieldId('dosen_pa_id')}
                                value={data.dosen_pa_id}
                                onChange={(v) => setData('dosen_pa_id', v)}
                                options={dosenChoices}
                                placeholder="Belum ditentukan"
                                searchPlaceholder="Cari nama dosen..."
                                emptyText="Dosen tidak ditemukan."
                                clearLabel="Belum ditentukan"
                                disabled={processing}
                                invalid={invalid('dosen_pa_id')}
                            />
                            <InputError message={errors.dosen_pa_id} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor={fieldId('status')}>Status</Label>
                            <Select
                                value={data.status}
                                onValueChange={(v) => setData('status', v)}
                                disabled={processing}
                            >
                                <SelectTrigger
                                    id={fieldId('status')}
                                    className="w-full"
                                    aria-invalid={
                                        invalid('status') || undefined
                                    }
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {statusOptions.map((s) => (
                                        <SelectItem
                                            key={s.value}
                                            value={s.value}
                                        >
                                            {s.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={errors.status} />
                        </div>
                    </fieldset>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={processing}
                        >
                            Batal
                        </Button>
                        {!isEdit && (
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => submit(true)}
                                disabled={processing}
                            >
                                Simpan dan tambah lagi
                            </Button>
                        )}
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Menyimpan...' : 'Simpan'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
