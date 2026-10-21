<?php

namespace App\Filament\Resources\ArsipDataResource\Pages;

use App\Filament\Resources\ArsipDataResource;
use Filament\Resources\Pages\EditRecord;

class EditArsipData extends EditRecord
{
    protected static string $resource = ArsipDataResource::class;

    protected function afterSave(): void
    {
        // Log aktivitas edit
        \App\Models\ArsipLog::create([
            'arsip_id' => $this->getRecord()->id,
            'user_id' => auth()->id(),
            'aksi' => 'edit',
            'keterangan' => 'Mengubah arsip: ' . $this->getRecord()->judul_arsip,
        ]);
    }
}
