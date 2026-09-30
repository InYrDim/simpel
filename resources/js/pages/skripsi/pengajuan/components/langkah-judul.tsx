import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { JudulForm, KategoriOption } from './types';

type Props = {
    juduls: JudulForm[];
    kategoriOptions: KategoriOption[];
    /** true setelah mahasiswa menekan "Lanjut" dengan isian belum lengkap. */
    tampilkanKosong: boolean;
    errors: Record<string, string>;
    onChange: (index: number, field: keyof JudulForm, value: string) => void;
};

export function LangkahJudul({
    juduls,
    kategoriOptions,
    tampilkanKosong,
    errors,
    onChange,
}: Props) {
    const galat = (index: number, field: keyof JudulForm): string | null => {
        const server = errors[`juduls.${index}.${field}`];

        if (server) {
            return server;
        }

        return tampilkanKosong && juduls[index][field].trim() === ''
            ? 'Wajib diisi.'
            : null;
    };

    return (
        <div className="flex flex-col gap-4">
            <p className="text-muted-foreground text-sm">
                Ajukan <strong>tepat 3 judul</strong>. Dosen akan memilih satu
                dari ketiganya, jadi isi ketiganya dengan sungguh-sungguh.
            </p>
            {juduls.map((j, i) => (
                <div key={i} className="grid gap-3 rounded-lg border p-3">
                    <p className="font-medium">Judul {i + 1}</p>

                    <Field
                        id={`judul-${i}`}
                        label="Judul"
                        hint="Judul lengkap skripsi Anda."
                        error={galat(i, 'judul')}
                    >
                        <Input
                            id={`judul-${i}`}
                            value={j.judul}
                            onChange={(e) =>
                                onChange(i, 'judul', e.target.value)
                            }
                        />
                    </Field>

                    <Field
                        id={`deskripsi-${i}`}
                        label="Deskripsi"
                        hint="Jelaskan singkat masalah dan tujuan penelitian."
                        error={galat(i, 'deskripsi')}
                    >
                        <Textarea
                            id={`deskripsi-${i}`}
                            value={j.deskripsi}
                            onChange={(e) =>
                                onChange(i, 'deskripsi', e.target.value)
                            }
                        />
                    </Field>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <Field
                            id={`topik-${i}`}
                            label="Topik"
                            hint="Bidang bahasan, mis. Machine Learning."
                            error={galat(i, 'topik')}
                        >
                            <Input
                                id={`topik-${i}`}
                                value={j.topik}
                                onChange={(e) =>
                                    onChange(i, 'topik', e.target.value)
                                }
                            />
                        </Field>

                        <Field
                            id={`kategori-${i}`}
                            label="Kategori"
                            hint="Jenis penelitian yang paling sesuai."
                            error={
                                errors[`juduls.${i}.kategori_id`] ??
                                (tampilkanKosong && j.kategori_id === ''
                                    ? 'Pilih salah satu kategori.'
                                    : null)
                            }
                        >
                            <Select
                                value={j.kategori_id}
                                onValueChange={(v) =>
                                    onChange(i, 'kategori_id', v)
                                }
                            >
                                <SelectTrigger id={`kategori-${i}`}>
                                    <SelectValue placeholder="Pilih kategori" />
                                </SelectTrigger>
                                <SelectContent>
                                    {kategoriOptions.map((k) => (
                                        <SelectItem
                                            key={k.id}
                                            value={String(k.id)}
                                        >
                                            {k.nama}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>
                </div>
            ))}
        </div>
    );
}

function Field({
    id,
    label,
    hint,
    error,
    children,
}: {
    id: string;
    label: string;
    hint: string;
    error: string | null | undefined;
    children: React.ReactNode;
}) {
    return (
        <div className="grid gap-1.5">
            <Label htmlFor={id}>{label}</Label>
            {children}
            {error ? (
                <p className="text-destructive text-sm">{error}</p>
            ) : (
                <p className="text-muted-foreground text-xs">{hint}</p>
            )}
        </div>
    );
}
