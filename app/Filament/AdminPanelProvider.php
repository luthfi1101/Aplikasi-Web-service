<?php

namespace App\Filament;

use App\Filament\Resources\ArsipDataResource;
use App\Filament\Resources\KategoriArsipResource;
use App\Filament\Resources\UserResource;
use Filament\Panel;
use Filament\PanelProvider as BasePanelProvider;
use Filament\Support\Colors\Color;

class AdminPanelProvider extends BasePanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(fn() => view('filament.admin.logo'))
            ->brandName('E-Arsip Kehumasan BKKBN Sumatera Selatan')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                UserResource::class,
                KategoriArsipResource::class,
                ArsipDataResource::class,
            ])
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\ArsipChart::class,
                \App\Filament\Widgets\KategoriStats::class,
            ])
            ->middleware([
                \Filament\Http\Middleware\Authenticate::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ]);
    }
}
