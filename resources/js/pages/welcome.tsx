import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    BarChart3,
    BookOpenCheck,
    CheckCircle2,
    ChevronRight,
    FileText,
    GraduationCap,
    Moon,
    ShieldCheck,
    Sparkles,
    Sun,
    Users,
} from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useAppearance } from '@/hooks/use-appearance';
import { dashboard, login, register } from '@/routes';

export default function Welcome() {
    const { auth, name } = usePage().props;
    const { appearance, updateAppearance } = useAppearance();

    const appName = name || 'SIM Akademik & Skripsi';

    const toggleTheme = () => {
        const nextMode = appearance === 'dark' ? 'light' : 'dark';
        updateAppearance(nextMode);
    };

    const isDarkMode = appearance === 'dark';

    return (
        <>
            <Head title={`Selamat Datang - ${appName}`}>
                <meta
                    name="description"
                    content="Portal terpadu pengelolaan akademik, program studi, dosen pembimbing, dan sistem bimbingan skripsi mahasiswa."
                />
            </Head>

            <div className="bg-background text-foreground selection:bg-primary/20 selection:text-primary flex min-h-screen flex-col">
                {/* Navbar */}
                <header className="border-border/60 bg-background/85 supports-[backdrop-filter]:bg-background/70 sticky top-0 z-50 w-full border-b backdrop-blur-md">
                    <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        {/* Brand Logo */}
                        <Link
                            href="/"
                            className="flex items-center gap-2.5 transition-opacity hover:opacity-90"
                            aria-label="Kembali ke beranda"
                        >
                            <div className="bg-primary text-primary-foreground flex aspect-square size-9 items-center justify-center rounded-lg shadow-xs">
                                <AppLogoIcon className="size-5 fill-current text-current" />
                            </div>
                            <div className="flex flex-col">
                                <span className="text-base leading-tight font-bold tracking-tight">
                                    {appName}
                                </span>
                                <span className="text-muted-foreground text-[11px] leading-none font-medium">
                                    Portal Akademik & Skripsi
                                </span>
                            </div>
                        </Link>

                        {/* Navigation Links */}
                        <nav
                            className="text-muted-foreground hidden items-center gap-7 text-sm font-medium md:flex"
                            aria-label="Navigasi Utama"
                        >
                            <a
                                href="#fitur"
                                className="hover:text-foreground transition-colors"
                            >
                                Fitur Utama
                            </a>
                            <a
                                href="#alur"
                                className="hover:text-foreground transition-colors"
                            >
                                Alur Skripsi
                            </a>
                            <a
                                href="#peran"
                                className="hover:text-foreground transition-colors"
                            >
                                Peran & Akses
                            </a>
                        </nav>

                        {/* Action Buttons & Theme Toggle */}
                        <div className="flex items-center gap-2 sm:gap-3">
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={toggleTheme}
                                className="text-muted-foreground hover:text-foreground size-9 rounded-md"
                                aria-label={
                                    isDarkMode
                                        ? 'Beralih ke mode terang'
                                        : 'Beralih ke mode gelap'
                                }
                            >
                                {isDarkMode ? (
                                    <Sun className="size-4" />
                                ) : (
                                    <Moon className="size-4" />
                                )}
                            </Button>

                            {auth.user ? (
                                <Button asChild size="sm" className="gap-2">
                                    <Link href={dashboard()}>
                                        <span>Dashboard</span>
                                        <ArrowRight className="size-4" />
                                    </Link>
                                </Button>
                            ) : (
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="ghost"
                                        asChild
                                        size="sm"
                                        className="text-foreground"
                                    >
                                        <Link href={login()}>Masuk</Link>
                                    </Button>
                                    <Button asChild size="sm">
                                        <Link href={register()}>Daftar</Link>
                                    </Button>
                                </div>
                            )}
                        </div>
                    </div>
                </header>

                <main className="flex-1">
                    {/* Hero Section */}
                    <section className="relative overflow-hidden pt-12 pb-16 md:pt-20 md:pb-24 lg:pt-24">
                        {/* Background subtle radial gradient */}
                        <div
                            className="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center opacity-30 dark:opacity-20"
                            aria-hidden="true"
                        >
                            <div className="bg-primary/20 h-[360px] w-[600px] rounded-full blur-3xl" />
                        </div>

                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="mx-auto max-w-3xl text-center">
                                <Badge
                                    variant="secondary"
                                    className="mb-5 inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold"
                                >
                                    <Sparkles className="text-primary size-3.5" />
                                    <span>
                                        Sistem Informasi Akademik & Skripsi
                                        Kampus
                                    </span>
                                </Badge>

                                <h1 className="text-3xl leading-tight font-extrabold tracking-tight sm:text-4xl md:text-5xl lg:text-5xl">
                                    Pengelolaan Akademik & Bimbingan Skripsi
                                    yang Terstruktur
                                </h1>

                                <p className="text-muted-foreground mt-5 text-base leading-relaxed sm:text-lg">
                                    Menghubungkan mahasiswa, dosen pembimbing,
                                    dan pengelola program studi dalam satu alur
                                    kerja digital yang transparan, bebas antre,
                                    dan terintegrasi.
                                </p>

                                <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row sm:gap-4">
                                    <Button
                                        asChild
                                        size="lg"
                                        className="h-11 w-full gap-2 px-6 text-base sm:w-auto"
                                    >
                                        <Link
                                            href={
                                                auth.user
                                                    ? dashboard()
                                                    : login()
                                            }
                                        >
                                            <span>
                                                {auth.user
                                                    ? 'Buka Dashboard'
                                                    : 'Masuk ke Portal'}
                                            </span>
                                            <ArrowRight className="size-4" />
                                        </Link>
                                    </Button>

                                    <Button
                                        variant="outline"
                                        size="lg"
                                        asChild
                                        className="h-11 w-full px-6 text-base sm:w-auto"
                                    >
                                        <a href="#alur">Pelajari Alur Kerja</a>
                                    </Button>
                                </div>

                                {/* Micro highlights badges */}
                                <div className="border-border/50 mt-10 grid grid-cols-2 gap-4 border-t pt-8 text-center md:grid-cols-4">
                                    <div className="flex flex-col items-center">
                                        <span className="text-foreground text-sm font-semibold">
                                            100% Digital
                                        </span>
                                        <span className="text-muted-foreground text-xs">
                                            Bebas berkas fisik hilang
                                        </span>
                                    </div>
                                    <div className="flex flex-col items-center">
                                        <span className="text-foreground text-sm font-semibold">
                                            Validasi Terpadu
                                        </span>
                                        <span className="text-muted-foreground text-xs">
                                            Pemeriksaan bertingkat
                                        </span>
                                    </div>
                                    <div className="flex flex-col items-center">
                                        <span className="text-foreground text-sm font-semibold">
                                            Beban Merata
                                        </span>
                                        <span className="text-muted-foreground text-xs">
                                            Monitoring kuota dosen
                                        </span>
                                    </div>
                                    <div className="flex flex-col items-center">
                                        <span className="text-foreground text-sm font-semibold">
                                            Akses Aman
                                        </span>
                                        <span className="text-muted-foreground text-xs">
                                            Passkey & 2FA ready
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Hero Visual Preview Card */}
                            <div className="mt-12 md:mt-16">
                                <Card className="border-border/80 mx-auto max-w-4xl overflow-hidden shadow-md">
                                    <div className="bg-muted/40 border-border/60 flex items-center justify-between border-b px-4 py-3">
                                        <div className="flex items-center gap-2">
                                            <div className="bg-destructive/60 size-3 rounded-full" />
                                            <div className="size-3 rounded-full bg-amber-400/70" />
                                            <div className="size-3 rounded-full bg-emerald-500/70" />
                                            <span className="text-muted-foreground ml-2 font-mono text-xs">
                                                portal-akademik/skripsi
                                            </span>
                                        </div>
                                        <Badge
                                            variant="outline"
                                            className="text-[11px] font-normal"
                                        >
                                            Sistem Aktif
                                        </Badge>
                                    </div>

                                    <CardContent className="p-6 sm:p-8">
                                        <div className="grid gap-6 md:grid-cols-3">
                                            <div className="border-border/60 bg-card flex flex-col justify-between rounded-lg border p-4">
                                                <div>
                                                    <div className="mb-2 flex items-center justify-between">
                                                        <span className="text-muted-foreground text-xs font-medium">
                                                            Manajemen Mahasiswa
                                                        </span>
                                                        <Users className="text-primary size-4" />
                                                    </div>
                                                    <p className="text-xl font-bold">
                                                        Mahasiswa & PA
                                                    </p>
                                                    <p className="text-muted-foreground mt-1 text-xs">
                                                        Sinkronisasi prodi &
                                                        dosen pembimbing
                                                        akademik
                                                    </p>
                                                </div>
                                                <div className="text-primary mt-4 flex items-center gap-1.5 text-xs font-medium">
                                                    <CheckCircle2 className="size-3.5" />
                                                    <span>
                                                        Data Terverifikasi
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="border-border/60 bg-card flex flex-col justify-between rounded-lg border p-4">
                                                <div>
                                                    <div className="mb-2 flex items-center justify-between">
                                                        <span className="text-muted-foreground text-xs font-medium">
                                                            Pengajuan Skripsi
                                                        </span>
                                                        <BookOpenCheck className="text-primary size-4" />
                                                    </div>
                                                    <p className="text-xl font-bold">
                                                        Alur Usulan Judul
                                                    </p>
                                                    <p className="text-muted-foreground mt-1 text-xs">
                                                        Validasi berkas &
                                                        penugasan pembimbing
                                                        cepat
                                                    </p>
                                                </div>
                                                <div className="text-primary mt-4 flex items-center gap-1.5 text-xs font-medium">
                                                    <CheckCircle2 className="size-3.5" />
                                                    <span>
                                                        Status Real-Time
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="border-border/60 bg-card flex flex-col justify-between rounded-lg border p-4">
                                                <div>
                                                    <div className="mb-2 flex items-center justify-between">
                                                        <span className="text-muted-foreground text-xs font-medium">
                                                            Pelaporan & Audit
                                                        </span>
                                                        <BarChart3 className="text-primary size-4" />
                                                    </div>
                                                    <p className="text-xl font-bold">
                                                        Distribusi Beban
                                                    </p>
                                                    <p className="text-muted-foreground mt-1 text-xs">
                                                        Rekapitulasi statistik &
                                                        ekspor CSV resmi
                                                    </p>
                                                </div>
                                                <div className="text-primary mt-4 flex items-center gap-1.5 text-xs font-medium">
                                                    <CheckCircle2 className="size-3.5" />
                                                    <span>Siap Akreditasi</span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Workflow preview bar */}
                                        <div className="bg-muted/40 border-border/50 mt-6 rounded-lg border p-4">
                                            <p className="text-muted-foreground mb-3 text-xs font-semibold tracking-wider uppercase">
                                                Tahapan Bimbingan Skripsi
                                            </p>
                                            <div className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                                <div className="bg-background border-border/60 rounded-md border p-2.5">
                                                    <span className="text-primary block text-[11px] font-semibold">
                                                        1. Usulan Judul
                                                    </span>
                                                    <span className="text-muted-foreground text-[11px]">
                                                        Input judul & berkas
                                                    </span>
                                                </div>
                                                <div className="bg-background border-border/60 rounded-md border p-2.5">
                                                    <span className="text-primary block text-[11px] font-semibold">
                                                        2. Verifikasi
                                                    </span>
                                                    <span className="text-muted-foreground text-[11px]">
                                                        Cek berkas admin
                                                    </span>
                                                </div>
                                                <div className="bg-background border-border/60 rounded-md border p-2.5">
                                                    <span className="text-primary block text-[11px] font-semibold">
                                                        3. Validasi & SK
                                                    </span>
                                                    <span className="text-muted-foreground text-[11px]">
                                                        Penetapan pembimbing
                                                    </span>
                                                </div>
                                                <div className="bg-background border-border/60 rounded-md border p-2.5">
                                                    <span className="text-primary block text-[11px] font-semibold">
                                                        4. Bimbingan
                                                    </span>
                                                    <span className="text-muted-foreground text-[11px]">
                                                        Monitoring progres
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </section>

                    {/* Fitur Utama Section */}
                    <section
                        id="fitur"
                        className="border-border/60 bg-muted/20 border-t py-16 md:py-24"
                    >
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="mx-auto mb-12 max-w-2xl text-center md:mb-16">
                                <Badge variant="outline" className="mb-3">
                                    Fitur Unggulan
                                </Badge>
                                <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                    Fokus pada Kecepatan & Ketepatan Akademik
                                </h2>
                                <p className="text-muted-foreground mt-3 text-sm sm:text-base">
                                    Tiga pilar utama sistem yang dirancang untuk
                                    memudahkan koordinasi harian seluruh civitas
                                    akademika.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3 lg:gap-8">
                                {/* Card 1 */}
                                <Card className="border-border/70 hover:border-primary/40 transition-colors">
                                    <CardHeader className="pb-4">
                                        <div className="bg-primary/10 text-primary mb-3 flex size-11 items-center justify-center rounded-lg">
                                            <GraduationCap className="size-6" />
                                        </div>
                                        <CardTitle className="text-xl">
                                            Manajemen Akademik
                                        </CardTitle>
                                        <CardDescription>
                                            Kelola struktur program studi, dosen
                                            wali, dan mahasiswa secara rapi.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="text-muted-foreground space-y-2 text-sm">
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Pencatatan data Program Studi
                                                dan Jurusan bertingkat.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Penetapan Dosen Pembimbing
                                                Akademik (PA) terstruktur.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Filter status mahasiswa aktif,
                                                lulus, atau nonaktif dengan
                                                cepat.
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Card 2 */}
                                <Card className="border-border/70 hover:border-primary/40 transition-colors">
                                    <CardHeader className="pb-4">
                                        <div className="bg-primary/10 text-primary mb-3 flex size-11 items-center justify-center rounded-lg">
                                            <BookOpenCheck className="size-6" />
                                        </div>
                                        <CardTitle className="text-xl">
                                            Bimbingan & Tugas Akhir
                                        </CardTitle>
                                        <CardDescription>
                                            Siklus pengajuan judul hingga
                                            putusan skripsi yang transparan.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="text-muted-foreground space-y-2 text-sm">
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Formulir wizard pengajuan usulan
                                                judul & upload dokumen.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Verifikasi berkas administratif
                                                dan catatan revisi instan.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Validasi kelayakan topik serta
                                                penugasan dosen pembimbing.
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>

                                {/* Card 3 */}
                                <Card className="border-border/70 hover:border-primary/40 transition-colors">
                                    <CardHeader className="pb-4">
                                        <div className="bg-primary/10 text-primary mb-3 flex size-11 items-center justify-center rounded-lg">
                                            <BarChart3 className="size-6" />
                                        </div>
                                        <CardTitle className="text-xl">
                                            Monitoring & Ekspor Data
                                        </CardTitle>
                                        <CardDescription>
                                            Pantau beban bimbingan dosen dan
                                            unduh laporan sewaktu-waktu.
                                        </CardDescription>
                                    </CardHeader>
                                    <CardContent className="text-muted-foreground space-y-2 text-sm">
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Pemerataan kuota bimbingan untuk
                                                setiap dosen pembimbing.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Statistik pengajuan & tren
                                                kelulusan secara visual.
                                            </span>
                                        </div>
                                        <div className="flex items-start gap-2">
                                            <ChevronRight className="text-primary mt-0.5 size-4 shrink-0" />
                                            <span>
                                                Ekspor data laporan ke format
                                                CSV untuk kebutuhan pelaporan.
                                            </span>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </section>

                    {/* Alur Skripsi Section */}
                    <section id="alur" className="py-16 md:py-24">
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="mx-auto mb-12 max-w-2xl text-center md:mb-16">
                                <Badge variant="outline" className="mb-3">
                                    Alur Kerja
                                </Badge>
                                <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                    4 Langkah Sederhana Menuju Bimbingan
                                </h2>
                                <p className="text-muted-foreground mt-3 text-sm sm:text-base">
                                    Proses terstruktur yang memotong birokrasi
                                    rumit dan mempermudah mahasiswa serta dosen.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                                <div className="border-border/80 bg-card flex flex-col justify-between rounded-xl border p-6 shadow-xs">
                                    <div>
                                        <div className="bg-primary/10 text-primary mb-4 flex size-10 items-center justify-center rounded-full text-sm font-bold">
                                            01
                                        </div>
                                        <h3 className="text-foreground mb-2 text-lg font-semibold">
                                            Usulan & Berkas
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Mahasiswa memasukkan judul usulan,
                                            abstrak kajian, serta mengunggah
                                            syarat berkas secara daring.
                                        </p>
                                    </div>
                                    <div className="border-border/50 text-muted-foreground mt-4 flex items-center gap-1.5 border-t pt-4 text-xs">
                                        <FileText className="text-primary size-3.5" />
                                        <span>Dilakukan oleh Mahasiswa</span>
                                    </div>
                                </div>

                                <div className="border-border/80 bg-card flex flex-col justify-between rounded-xl border p-6 shadow-xs">
                                    <div>
                                        <div className="bg-primary/10 text-primary mb-4 flex size-10 items-center justify-center rounded-full text-sm font-bold">
                                            02
                                        </div>
                                        <h3 className="text-foreground mb-2 text-lg font-semibold">
                                            Verifikasi Dokumen
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Staf admin memeriksa kelengkapan
                                            berkas administratif dan memberikan
                                            catatan revisi bila diperlukan.
                                        </p>
                                    </div>
                                    <div className="border-border/50 text-muted-foreground mt-4 flex items-center gap-1.5 border-t pt-4 text-xs">
                                        <CheckCircle2 className="text-primary size-3.5" />
                                        <span>Verifikator / Admin</span>
                                    </div>
                                </div>

                                <div className="border-border/80 bg-card flex flex-col justify-between rounded-xl border p-6 shadow-xs">
                                    <div>
                                        <div className="bg-primary/10 text-primary mb-4 flex size-10 items-center justify-center rounded-full text-sm font-bold">
                                            03
                                        </div>
                                        <h3 className="text-foreground mb-2 text-lg font-semibold">
                                            Validasi Topik & SK
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Koordinator mengevaluasi kelayakan
                                            topik ilmiah dan menetapkan dosen
                                            pembimbing yang sesuai bidang.
                                        </p>
                                    </div>
                                    <div className="border-border/50 text-muted-foreground mt-4 flex items-center gap-1.5 border-t pt-4 text-xs">
                                        <ShieldCheck className="text-primary size-3.5" />
                                        <span>Kaprodi / Tim Validator</span>
                                    </div>
                                </div>

                                <div className="border-border/80 bg-card flex flex-col justify-between rounded-xl border p-6 shadow-xs">
                                    <div>
                                        <div className="bg-primary/10 text-primary mb-4 flex size-10 items-center justify-center rounded-full text-sm font-bold">
                                            04
                                        </div>
                                        <h3 className="text-foreground mb-2 text-lg font-semibold">
                                            Bimbingan Berjalan
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Mahasiswa dan dosen pembimbing
                                            menjalankan proses bimbingan dengan
                                            pemantauan status yang transparan.
                                        </p>
                                    </div>
                                    <div className="border-border/50 text-muted-foreground mt-4 flex items-center gap-1.5 border-t pt-4 text-xs">
                                        <GraduationCap className="text-primary size-3.5" />
                                        <span>Dosen & Mahasiswa</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Peran & Akses Section */}
                    <section
                        id="peran"
                        className="border-border/60 bg-muted/20 border-t py-16 md:py-24"
                    >
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="mx-auto mb-12 max-w-2xl text-center md:mb-16">
                                <Badge variant="outline" className="mb-3">
                                    Ruang Kerja Khusus
                                </Badge>
                                <h2 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                    Dirancang untuk Setiap Peran Kampus
                                </h2>
                                <p className="text-muted-foreground mt-3 text-sm sm:text-base">
                                    Setiap pengguna memiliki akses dan tampilan
                                    yang disesuaikan dengan tanggung jawab
                                    masing-masing.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div className="border-border/70 bg-card flex gap-4 rounded-xl border p-6">
                                    <div className="bg-primary/10 text-primary flex size-12 shrink-0 items-center justify-center rounded-lg">
                                        <Users className="size-6" />
                                    </div>
                                    <div>
                                        <h3 className="text-foreground mb-1 text-lg font-semibold">
                                            Portal Mahasiswa
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Ajukan judul skripsi mandiri, pantau
                                            status verifikasi secara berkala,
                                            dan lihat catatan perbaikan langsung
                                            tanpa perlu menunggu antrean
                                            panjang.
                                        </p>
                                    </div>
                                </div>

                                <div className="border-border/70 bg-card flex gap-4 rounded-xl border p-6">
                                    <div className="bg-primary/10 text-primary flex size-12 shrink-0 items-center justify-center rounded-lg">
                                        <GraduationCap className="size-6" />
                                    </div>
                                    <div>
                                        <h3 className="text-foreground mb-1 text-lg font-semibold">
                                            Dosen Pembimbing & PA
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Akses daftar bimbingan aktif, pantau
                                            beban kerja pembimbing secara
                                            proporsional, dan monitor kemajuan
                                            akademik mahasiswa bimbingan.
                                        </p>
                                    </div>
                                </div>

                                <div className="border-border/70 bg-card flex gap-4 rounded-xl border p-6">
                                    <div className="bg-primary/10 text-primary flex size-12 shrink-0 items-center justify-center rounded-lg">
                                        <CheckCircle2 className="size-6" />
                                    </div>
                                    <div>
                                        <h3 className="text-foreground mb-1 text-lg font-semibold">
                                            Kaprodi & Validator
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Uji kelayakan topik usulan skripsi,
                                            tetapkan penugasan dosen pembimbing
                                            secara adil, serta akses rekap data
                                            kelulusan program studi.
                                        </p>
                                    </div>
                                </div>

                                <div className="border-border/70 bg-card flex gap-4 rounded-xl border p-6">
                                    <div className="bg-primary/10 text-primary flex size-12 shrink-0 items-center justify-center rounded-lg">
                                        <ShieldCheck className="size-6" />
                                    </div>
                                    <div>
                                        <h3 className="text-foreground mb-1 text-lg font-semibold">
                                            Administrator Institusi
                                        </h3>
                                        <p className="text-muted-foreground text-sm leading-relaxed">
                                            Kelola pengguna, struktur jurusan
                                            dan program studi, konfigurasi peran
                                            & hak akses (RBAC), serta keamanan
                                            dengan autentikasi Passkey dan 2FA.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* CTA Banner Section */}
                    <section className="py-16 md:py-20">
                        <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                            <div className="border-border from-card to-muted/40 relative overflow-hidden rounded-2xl border bg-gradient-to-b p-8 text-center sm:p-12">
                                <div
                                    className="bg-primary/15 pointer-events-none absolute -top-24 left-1/2 size-72 -translate-x-1/2 rounded-full blur-3xl"
                                    aria-hidden="true"
                                />

                                <h2 className="text-2xl font-extrabold tracking-tight sm:text-3xl md:text-4xl">
                                    Siap Memulai Administrasi yang Lebih
                                    Efisien?
                                </h2>
                                <p className="text-muted-foreground mx-auto mt-4 max-w-xl text-sm sm:text-base">
                                    Gunakan akun resmi kampus Anda untuk masuk
                                    ke portal dan mulai mengelola data akademik
                                    maupun tugas akhir.
                                </p>

                                <div className="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                    <Button
                                        asChild
                                        size="lg"
                                        className="h-11 w-full gap-2 px-6 sm:w-auto"
                                    >
                                        <Link
                                            href={
                                                auth.user
                                                    ? dashboard()
                                                    : login()
                                            }
                                        >
                                            <span>
                                                {auth.user
                                                    ? 'Masuk ke Dashboard'
                                                    : 'Masuk Sekarang'}
                                            </span>
                                            <ArrowRight className="size-4" />
                                        </Link>
                                    </Button>

                                    {!auth.user && (
                                        <Button
                                            variant="outline"
                                            size="lg"
                                            asChild
                                            className="h-11 w-full px-6 sm:w-auto"
                                        >
                                            <Link href={register()}>
                                                Buat Akun Baru
                                            </Link>
                                        </Button>
                                    )}
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                {/* Footer */}
                <footer className="border-border/60 bg-background border-t py-8 sm:py-12">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div className="flex items-center gap-2.5">
                                <div className="bg-primary text-primary-foreground flex aspect-square size-7 items-center justify-center rounded-md">
                                    <AppLogoIcon className="size-4 fill-current text-current" />
                                </div>
                                <span className="text-sm font-semibold">
                                    {appName}
                                </span>
                            </div>

                            <div className="text-muted-foreground flex items-center gap-6 text-xs">
                                <a
                                    href="#fitur"
                                    className="hover:text-foreground transition-colors"
                                >
                                    Fitur
                                </a>
                                <a
                                    href="#alur"
                                    className="hover:text-foreground transition-colors"
                                >
                                    Alur Skripsi
                                </a>
                                <a
                                    href="#peran"
                                    className="hover:text-foreground transition-colors"
                                >
                                    Peran
                                </a>
                                <Link
                                    href={login()}
                                    className="hover:text-foreground transition-colors"
                                >
                                    Login
                                </Link>
                            </div>
                        </div>

                        <Separator className="my-6" />

                        <div className="text-muted-foreground flex flex-col items-center justify-between gap-3 text-xs sm:flex-row">
                            <p>
                                &copy; {new Date().getFullYear()} {appName}.
                                Seluruh hak cipta dilindungi undang-undang.
                            </p>
                            <p className="flex items-center gap-1.5">
                                <ShieldCheck className="text-primary size-3.5" />
                                <span>
                                    Dilengkapi autentikasi aman Passkey &amp;
                                    2FA
                                </span>
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
