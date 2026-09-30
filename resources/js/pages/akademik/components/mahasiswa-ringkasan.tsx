import type { StatusOption } from '@/pages/akademik/components/mahasiswa-columns';
import {
    TANPA_DOSEN_PA,
    type DosenPaFilter,
} from '@/pages/akademik/components/mahasiswa-filter-popover';
import { cn } from '@/lib/utils';

export type Ringkasan = {
    total: number;
    per_status: Record<string, number>;
    tanpa_dosen_pa: number;
};

type MahasiswaRingkasanProps = {
    ringkasan: Ringkasan;
    statusOptions: StatusOption[];
    status: string | null;
    dosenPaId: DosenPaFilter;
    onStatusChange: (status: string | null) => void;
    onSemua: () => void;
    onTanpaDosenPa: (aktif: boolean) => void;
};

/**
 * Baris angka di atas tabel yang sekaligus menjadi filter cepat: "Semua" dan
 * tiap status menyetel filter status, "Belum punya dosen PA" menyetel
 * `dosen_pa_id=kosong`. Angka selalu total keseluruhan, bukan hasil filter.
 */
export function MahasiswaRingkasan({
    ringkasan,
    statusOptions,
    status,
    dosenPaId,
    onStatusChange,
    onSemua,
    onTanpaDosenPa,
}: MahasiswaRingkasanProps) {
    const tanpaPaAktif = dosenPaId === TANPA_DOSEN_PA;

    return (
        <div
            role="group"
            aria-label="Ringkasan dan filter cepat status mahasiswa"
            className="border-border grid grid-cols-2 divide-x border-y sm:grid-cols-3 lg:auto-cols-fr lg:grid-flow-col"
        >
            <RingkasanItem
                label="Semua"
                jumlah={ringkasan.total}
                pressed={status === null && !tanpaPaAktif}
                onClick={onSemua}
            />
            {statusOptions.map((option) => (
                <RingkasanItem
                    key={option.value}
                    label={option.label}
                    jumlah={ringkasan.per_status[option.value] ?? 0}
                    pressed={status === option.value}
                    onClick={() =>
                        onStatusChange(
                            status === option.value ? null : option.value,
                        )
                    }
                />
            ))}
            <RingkasanItem
                label="Belum punya dosen PA"
                jumlah={ringkasan.tanpa_dosen_pa}
                pressed={tanpaPaAktif}
                peringatan={ringkasan.tanpa_dosen_pa > 0}
                onClick={() => onTanpaDosenPa(!tanpaPaAktif)}
            />
        </div>
    );
}

function RingkasanItem({
    label,
    jumlah,
    pressed,
    peringatan = false,
    onClick,
}: {
    label: string;
    jumlah: number;
    pressed: boolean;
    peringatan?: boolean;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            aria-pressed={pressed}
            onClick={onClick}
            className={cn(
                'focus-visible:ring-ring/50 flex min-h-11 flex-col items-start gap-0.5 px-4 py-3 text-left transition-colors focus-visible:ring-[3px] focus-visible:outline-none focus-visible:ring-inset',
                'hover:bg-muted/60',
                pressed && 'bg-accent',
            )}
        >
            <span
                className={cn(
                    'text-2xl leading-tight font-bold tabular-nums',
                    peringatan && 'text-destructive',
                )}
            >
                {jumlah}
            </span>
            <span
                className={cn(
                    'text-muted-foreground text-sm',
                    pressed && 'text-foreground font-medium',
                )}
            >
                {label}
            </span>
        </button>
    );
}
