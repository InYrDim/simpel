<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRoles();
        $this->configureRateLimiting();
    }

    /**
     * Batasi aksi submit/resubmit pengajuan judul agar tidak bisa di-flood
     * (PRD ketahanan-teknis §3.3): 5 percobaan per menit per akun.
     *
     * Limiter diberi nama generik di core supaya `throttle:` pada route modul
     * cukup menunjuk namanya — core tidak perlu menyentuh namespace modul.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('pengajuan-submit', function (Request $request): Limit {
            // Kunci per akun; IP jadi fallback untuk permintaan tanpa sesi.
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure role & permission behavior.
     */
    protected function configureRoles(): void
    {
        // Admin melewati semua pemeriksaan role/permission.
        Gate::before(fn ($user, $ability): ?bool => $user->hasRole('admin') ? true : null);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
