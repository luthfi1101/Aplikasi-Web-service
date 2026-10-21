<?php

namespace App\Filament\Resources;

use Filament\Support\Icons\Heroicon;
use App\Models\KategoriArsip;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KategoriArsipResource extends Resource
{
    protected static ?string $model = KategoriArsip::class;

   protected static ?string $navigationIcon = 'heroicon-o-folder-open';


    protected static ?string $navigationLabel = 'Kategori Arsip';

    protected static ?string $pluralLabel = 'Kategori Arsip';

    protected static ?string $label = 'Kategori';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bidang')
                            ->label('Nama Bidang')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: KSPK'),

                        Forms\Components\TextInput::make('kode_bidang')
                            ->label('Kode Bidang')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: KSPK-001'),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi singkat tentang kategori ini...'),
                    ]),

                Forms\Components\Section::make('Struktur Folder')
                    ->schema([
                        Forms\Components\TreeSelect::make('parent_id')
                            ->label('Kategori Induk')
                            ->relationship('parent', 'nama_bidang', modifyQueryUsing: fn($query) => $query->whereNull('parent_id'))
                            ->nullable()
                            ->placeholder('Pilih kategori induk (opsional)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_bidang')
                    ->label('Nama Bidang')
                    ->searchable()
                    ->sortable()
                    ->description(fn(KategoriArsip $record): string => $record->kode_bidang),

                Tables\Columns\TextColumn::make('parent.nama_bidang')
                    ->label('Induk')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Root'),

                Tables\Columns\TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('arsipData_count')
                    ->label('Jumlah Arsip')
                    ->counts('arsipData')
                    ->sortable(),

                Tables\Columns\TextColumn::createdAt('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('root_only')
                    ->label('Kategori Utama Saja')
                    ->query(fn(Builder $query): Builder => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('has_children')
                    ->label('Memiliki Sub-Kategori')
                    ->query(fn(Builder $query): Builder => $query->has('children')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->tree(
                expandable: true,
                modificationIndicator: true,
            );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageKategoriArsip::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? (string) static::getModel()::count() : null;
    }
}
