import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

/**
 * Ubah respons 429 (rate limiter) menjadi toast yang ramah, menggantikan
 * dialog error generik Inertia (PRD ketahanan-teknis §3.5).
 *
 * Hanya status 429 yang diambil alih; status lain dibiarkan lewat apa adanya
 * ke jalur bawaan Inertia. `preventDefault()` wajib dipanggil supaya Inertia
 * tidak menampilkan dialognya sendiri (`dialog.show()` hanya jalan saat event
 * tidak dibatalkan).
 */
export function useHttpExceptionToast(): void {
    useEffect(() => {
        return router.on('httpException', (event) => {
            const { response } = event.detail;

            if (response.status !== 429) {
                return;
            }

            event.preventDefault();

            const retryAfter = Number(response.headers['retry-after']);

            toast.error('Terlalu banyak percobaan', {
                description:
                    Number.isFinite(retryAfter) && retryAfter > 0
                        ? `Silakan coba lagi dalam ${retryAfter} detik.`
                        : 'Silakan tunggu sebentar, lalu coba lagi.',
            });
        });
    }, []);
}
