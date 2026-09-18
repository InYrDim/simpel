import { Head, Link } from '@inertiajs/react';
import { Users } from 'lucide-react';
import manajemen from '@/routes/manajemen';
import pengguna from '@/routes/manajemen/pengguna';

export default function ManajemenIndex() {
    return (
        <>
            <Head title="Manajemen" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">Manajemen</h1>
                </div>
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Link
                        href={pengguna.index.url()}
                        className="border-sidebar-border/70 dark:border-sidebar-border bg-card text-card-foreground flex flex-col items-start gap-3 overflow-hidden rounded-xl border p-6 shadow-sm transition hover:bg-accent"
                    >
                        <Users className="size-8 text-muted-foreground" />
                        <div>
                            <h2 className="text-lg font-semibold">Pengguna</h2>
                            <p className="text-muted-foreground text-sm">Kelola akun pengguna sistem</p>
                        </div>
                    </Link>
                </div>
            </div>
        </>
    );
}

ManajemenIndex.layout = () => ({
    breadcrumbs: [
        {
            title: 'Manajemen',
            href: manajemen.index.url(),
        },
    ],
});
