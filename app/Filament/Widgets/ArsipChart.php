<?php

namespace App\Filament\Widgets;

use App\Models\ArsipData;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ArsipChart extends ChartWidget
{
    protected static ?string $maxHeight = '300px';

    protected function getHeading(): string
    {
        return 'Statistik Arsip per Bulan';
    }

    protected function getData(): array
    {
        // Data 6 bulan terakhir
        $bulan = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $bulan[] = $date->locale('id')->monthName;

            $count = ArsipData::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Arsip',
                    'data' => $data,
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => '#f59e0b',
                ],
            ],
            'labels' => $bulan,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
