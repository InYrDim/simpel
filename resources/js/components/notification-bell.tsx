import { router } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import notificationsRoutes from '@/routes/notifications';

type NotificationItem = {
    id: string;
    data: { judul?: string; pesan?: string; jenis?: string };
    read_at: string | null;
    created_at: string;
};

type NotificationsProp = {
    unread_count: number;
    items: NotificationItem[];
};

/**
 * Bel notifikasi in-app (§5.4 PRD pengajuan judul). Data di-share global
 * oleh HandleInertiaRequests dari trait Notifiable milik User (core).
 * Tandai-dibaca lewat route bawaan Laravel, tanpa menyentuh namespace modul.
 */
export function NotificationBell({
    notifications,
}: {
    notifications: NotificationsProp;
}) {
    const [open, setOpen] = useState(false);

    const markAsRead = (id: string) => {
        router.post(
            notificationsRoutes.read.url({ id }),
            {},
            { preserveScroll: true },
        );
    };

    const markAllAsRead = () => {
        router.post(
            notificationsRoutes.readAll.url(),
            {},
            { preserveScroll: true },
        );
    };

    return (
        <DropdownMenu open={open} onOpenChange={setOpen}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="relative"
                    aria-label="Notifikasi"
                >
                    <Bell className="size-5" />
                    {notifications.unread_count > 0 && (
                        <span className="bg-destructive text-destructive-foreground absolute -top-0.5 -right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[10px] font-medium">
                            {notifications.unread_count > 9
                                ? '9+'
                                : notifications.unread_count}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
                <div className="flex items-center justify-between px-3 py-2">
                    <span className="text-sm font-semibold">Notifikasi</span>
                    {notifications.unread_count > 0 && (
                        <button
                            className="text-muted-foreground hover:text-foreground text-xs"
                            onClick={markAllAsRead}
                        >
                            Tandai semua dibaca
                        </button>
                    )}
                </div>
                <ScrollArea className="max-h-80">
                    {notifications.items.length === 0 ? (
                        <p className="text-muted-foreground px-3 py-6 text-center text-sm">
                            Belum ada notifikasi.
                        </p>
                    ) : (
                        notifications.items.map((item) => (
                            <button
                                key={item.id}
                                className={`hover:bg-accent w-full border-b px-3 py-2 text-left last:border-b-0 ${
                                    item.read_at ? 'opacity-60' : ''
                                }`}
                                onClick={() => markAsRead(item.id)}
                            >
                                <span className="block text-sm font-medium">
                                    {item.data.judul ?? 'Notifikasi'}
                                </span>
                                <span className="text-muted-foreground block text-xs">
                                    {item.data.pesan}
                                </span>
                            </button>
                        ))
                    )}
                </ScrollArea>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
