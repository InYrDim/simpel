export type UserRow = {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
};

export type Column = {
    key: string;
    label: string;
    render?: (row: UserRow) => React.ReactNode;
};

export const userColumns: Column[] = [
    {
        key: 'name',
        label: 'Pengguna',
        render: (u) => (
            <div className="flex items-center gap-3">
                <div className="bg-muted flex size-9 items-center justify-center rounded-full">
                    <UserIcon />
                </div>
                <div className="flex flex-col">
                    <span className="font-medium">{u.name}</span>
                    <span className="text-muted-foreground text-xs">{u.email}</span>
                </div>
            </div>
        ),
    },
    {
        key: 'email_verified_at',
        label: 'Verifikasi Email',
        render: (u) => {
            const verified = u.email_verified_at !== null;
            const cls = verified
                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
            return <span className={cls}>{verified ? 'Terverifikasi' : 'Belum verifikasi'}</span>;
        },
    },
    {
        key: 'created_at',
        label: 'Dibuat',
        render: (u) => new Date(u.created_at).toLocaleDateString('id-ID'),
    },
    {
        key: 'actions',
        label: 'Aksi',
        render: (u) => (
            <div className="flex justify-end gap-1">
                <a
                    href={`/manajemen/pengguna/${u.id}/edit`}
                    className="hover:bg-accent flex size-8 items-center justify-center rounded-md"
                    title="Edit"
                >
                    <EditIcon />
                </a>
                <form
                    method="POST"
                    action={`/manajemen/pengguna/${u.id}`}
                    onSubmit={(e) => {
                        if (!confirm('Hapus pengguna ini?')) {
                            e.preventDefault();
                        }
                    }}
                >
                    <input type="hidden" name="_method" value="DELETE" />
                    {(() => {
                        try {
                            const csrf = document.querySelector('meta[name=csrf-token]');
                            const token = csrf?.getAttribute('content') || '';
                            return <input type="hidden" name="_token" value={token} />;
                        } catch {
                            return null;
                        }
                    })()}
                    <button
                        type="submit"
                        className="hover:bg-accent flex size-8 items-center justify-center rounded-md"
                        title="Hapus"
                    >
                        <TrashIcon />
                    </button>
                </form>
            </div>
        ),
    },
];

function UserIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="size-4">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    );
}

function EditIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
            <path d="m15 5 4 4" />
        </svg>
    );
}

function TrashIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M3 6h18" />
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
            <line x1="10" x2="10" y1="11" y2="17" />
            <line x1="14" x2="14" y1="11" y2="17" />
        </svg>
    );
}
