<?php

namespace App\Providers;

use App\Models\Pengaturan;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::defaultView('vendor.pagination.koperasi');

        // Daftarkan Gate untuk setiap permission slug
        Gate::before(function ($user, $ability) {
            $user->loadMissing('roleModel.permissions');
            return $user->hasPermission($ability) ?: null;
        });

        View::composer('layouts.aplikasi', function ($view) {
            try {
                $profil = Pengaturan::whereIn('kunci', ['nama_koperasi', 'sub_nama'])
                    ->pluck('nilai', 'kunci');

                $view->with([
                    'namaKoperasiLayout' => $profil->get('nama_koperasi', 'Koperasi'),
                    'subNamaLayout'      => $profil->get('sub_nama', 'Simpan Pinjam'),
                ]);
            } catch (\Throwable) {
                $view->with(['namaKoperasiLayout' => 'Koperasi', 'subNamaLayout' => 'Simpan Pinjam']);
            }

            // Eager load roleModel dengan permissions untuk auth user
            if (Auth::check()) {
                Auth::user()->loadMissing('roleModel.permissions');
            }
        });
    }
}
