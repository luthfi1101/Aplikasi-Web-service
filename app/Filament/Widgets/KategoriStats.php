<?php

namespace App\Filament\Widgets;

use App\Models\ArsipData;
use App\Models\KategoriArsip;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class KategoriStats extends ChartWidget
{
    protected static ?string $maxHeight = '300px';

    protected function getHeading(): string
    {
        return 'Distribusi Arsip per Kategori';
    }

    protected function getData(): array
    {
        // Ambil data arsip per kategori (hanya kategori root)
        $kategoris = KategoriArsip::whereNull('parent_id')
            ->withCount('arsipData')
            ->having('arsip_data_count', '>', 0)
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#ec4899',
            '#06b6d4',
            '#84cc16',
            '#f97316',
            '#6366f1'
        ];

        foreach ($kategoris as $index => $kategori) {
            $labels[] = $kategori->nama_bidang;
            $data[] = $kategori->arsip_data_count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Arsip',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
            ],
        ];
    }
}
