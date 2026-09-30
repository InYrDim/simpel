@php
    // Header diteruskan Laravel dari ThrottleRequests (PRD ketahanan-teknis §3.5).
    $headers = method_exists($exception, 'getHeaders') ? $exception->getHeaders() : [];
    $retryAfter = (int) ($headers['Retry-After'] ?? 0);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Terlalu banyak percobaan</title>

        {{-- Deteksi mode gelap sistem, mengikuti pola app.blade.php. --}}
        <script>
            (function () {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Halaman error harus berdiri sendiri: tanpa Vite/Inertia, supaya tetap
             tampil walau aset belum ter-build. Token warna disalin dari
             resources/css/app.css agar tampilannya sejalan dengan aplikasi. --}}
        <style>
            :root {
                --background: oklch(0.994 0 0);
                --foreground: oklch(0 0 0);
                --muted-foreground: oklch(0.4386 0 0);
                --border: oklch(0.93 0.0094 286.2156);
                --primary: oklch(0.6609 0.158 243.9173);
                --primary-foreground: oklch(1 0 0);
            }

            .dark {
                --background: oklch(0.1448 0 0);
                --foreground: oklch(0.9551 0 0);
                --muted-foreground: oklch(0.6731 0 0);
                --border: oklch(0.2393 0 0);
                --primary: oklch(0.6609 0.158 243.9173);
                --primary-foreground: oklch(1 0 0);
            }

            * {
                box-sizing: border-box;
            }

            body {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 1.5rem;
                background-color: var(--background);
                color: var(--foreground);
                font-family: ui-sans-serif, system-ui, sans-serif;
                -webkit-font-smoothing: antialiased;
            }

            main {
                width: 100%;
                max-width: 28rem;
                text-align: center;
            }

            .code {
                margin: 0 0 0.75rem;
                color: var(--muted-foreground);
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }

            h1 {
                margin: 0 0 0.75rem;
                font-size: 1.5rem;
                line-height: 1.3;
            }

            .message {
                margin: 0 0 1.5rem;
                color: var(--muted-foreground);
                font-size: 0.875rem;
                line-height: 1.6;
            }

            .button {
                display: inline-block;
                padding: 0.5rem 1rem;
                border: 1px solid var(--border);
                border-radius: 1.2rem;
                background-color: var(--primary);
                color: var(--primary-foreground);
                font-size: 0.875rem;
                font-weight: 500;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <main>
            <p class="code">Error 429</p>
            <h1>Terlalu banyak percobaan</h1>
            <p class="message">
                Anda mengirim terlalu banyak permintaan dalam waktu singkat.
                @if ($retryAfter > 0)
                    Silakan coba lagi dalam {{ $retryAfter }} detik.
                @else
                    Silakan tunggu sebentar, lalu coba lagi.
                @endif
            </p>
            <a class="button" href="{{ url('/') }}">Kembali ke beranda</a>
        </main>
    </body>
</html>
