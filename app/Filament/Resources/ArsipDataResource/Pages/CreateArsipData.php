<?php

namespace App\Filament\Resources\ArsipDataResource\Pages;

use App\Filament\Resources\ArsipDataResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArsipData extends CreateRecord
{
    protected static string $resource = ArsipDataResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set user_id ke user yang sedang login
        $data['user_id'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Log aktivitas upload
        $record = $this->getRecord();
        \App\Models\ArsipLog::create([
            'arsip_id' => $record->id,
            'user_id' => auth()->id(),
            'aksi' => 'upload',
            'keterangan' => 'Mengunggah arsip: ' . $record->judul_arsip,
        ]);
    }
}
