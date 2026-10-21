<?php

namespace App\Filament\Resources\ArsipDataResource\Pages;

use App\Filament\Resources\ArsipDataResource;
use Filament\Resources\Pages\ViewRecord;

class ViewArsipData extends ViewRecord
{
    protected static string $resource = ArsipDataResource::class;

    protected function afterSave(): void
    {
        // Log aktivitas view
        \App\Models\ArsipLog::create([
            'arsip_id' => $this->getRecord()->id,
            'user_id' => auth()->id(),
            'aksi' => 'view',
            'keterangan' => 'Melihat arsip: ' . $this->getRecord()->judul_arsip,
        ]);
    }
}
