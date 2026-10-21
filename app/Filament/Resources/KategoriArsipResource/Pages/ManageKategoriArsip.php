<?php

namespace App\Filament\Resources\KategoriArsipResource\Pages;

use App\Filament\Resources\KategoriArsipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;

class ManageKategoriArsip extends ManageRelatedRecords
{
    protected static string $resource = KategoriArsipResource::class;

    protected static string $view = 'filament.resources.kategori-arsip-resource.pages.manage-kategori-arsip';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
