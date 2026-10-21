<?php

namespace App\Filament\Resources\ArsipDataResource\Pages;

use App\Filament\Resources\ArsipDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArsipData extends ListRecords
{
    protected static string $resource = ArsipDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
