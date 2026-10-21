<?php

namespace App\Filament\Widgets;

use App\Models\ArsipData;
use App\Models\KategoriArsip;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalArsip = ArsipData::count();
        $totalKategori = KategoriArsip::count();
        $totalUser = User::count();

        // Arsip bulan ini
        $arsipBulanIni = ArsipData::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Arsip berdasarkan jenis
        $pressRelease = ArsipData::where('jenis_arsip', 'press_release')->count();
        $foto = ArsipData::where('jenis_arsip', 'foto')->count();
        $video = ArsipData::where('jenis_arsip', 'video')->count();

        return [
            Stat::make('Total Arsip', $totalArsip)
                ->description('Semua arsip yang diupload')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Arsip Bulan Ini', $arsipBulanIni)
                ->description('Diupload bulan ' . now()->locale('id')->monthName)
                ->icon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Kategori', $totalKategori)
                ->description('Bidang organisasi')
                ->icon('heroicon-o-folder')
                ->color('warning'),

            Stat::make('Pengguna', $totalUser)
                ->description('User aktif sistem')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Press Release', $pressRelease)
                ->description('Dokumen PDF')
                ->icon('heroicon-o-document')
                ->color('primary'),

            Stat::make('Foto', $foto)
                ->description('Dokumentasi gambar')
                ->icon('heroicon-o-photo')
                ->color('success'),

            Stat::make('Video', $video)
                ->description('Rekaman kegiatan')
                ->icon('heroicon-o-video-camera')
                ->color('warning'),
        ];
    }
}
