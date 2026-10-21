<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArsipDataResource\Pages;
use App\Filament\Resources\ArsipDataResource\RelationManagers;
use App\Models\ArsipData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArsipDataResource extends Resource
{
    protected static ?string $model = ArsipData::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Data Arsip';

    protected static ?string $pluralLabel = 'Arsip Data';

    protected static ?string $label = 'Arsip';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Arsip')
                    ->schema([
                        Forms\Components\TextInput::make('judul_arsip')
                            ->label('Judul Arsip')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Press Release Seminar KB Nasional'),

                        Forms\Components\Select::make('jenis_arsip')
                            ->label('Jenis Arsip')
                            ->required()
                            ->options([
                                'press_release' => 'Press Release (PDF)',
                                'foto' => 'Dokumentasi Foto (JPG/PNG)',
                                'video' => 'Video Kegiatan',
                            ])
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('file_path', null)),

                        Forms\Components\DatePicker::make('tgl_kegiatan')
                            ->label('Tanggal Kegiatan')
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi singkat tentang arsip ini...'),
                    ]),

                Forms\Components\Section::make('Kategori & File')
                    ->schema([
                        Forms\Components\TreeSelect::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategori', 'nama_bidang', modifyQueryUsing: fn($query) => $query->whereNull('parent_id'))
                            ->required()
                            ->placeholder('Pilih kategori arsip'),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Berkas')
                            ->required()
                            ->directory('arsip')
                            ->preserveFilenames()
                            ->acceptedFileTypes(fn(Forms\Get $get) => match ($get('jenis_arsip')) {
                                'press_release' => ['application/pdf'],
                                'foto' => ['image/jpeg', 'image/png', 'image/jpg'],
                                'video' => ['video/mp4', 'video/webm'],
                                default => [],
                            })
                            ->maxFileSize(fn(Forms\Get $get) => match ($get('jenis_arsip')) {
                                'press_release' => 10, // 10MB
                                'foto' => 5, // 5MB
                                'video' => 100, // 100MB
                                default => 10,
                            })
                            ->downloadable()
                            ->openable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul_arsip')
                    ->label('Judul Arsip')
                    ->searchable()
                    ->sortable()
                    ->description(fn(ArsipData $record): string => $record->file_name),

                Tables\Columns\BadgeColumn::make('jenis_arsip')
                    ->label('Jenis')
                    ->colors([
                        'primary' => 'press_release',
                        'success' => 'foto',
                        'warning' => 'video',
                    ])
                    ->format(fn(string $state): string => match ($state) {
                        'press_release' => 'Press Release',
                        'foto' => 'Foto',
                        'video' => 'Video',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('kategori.nama_bidang')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tgl_kegiatan')
                    ->label('Tgl Kegiatan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diupload Oleh')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran')
                    ->formatStateUsing(fn(ArsipData $record): string => $record->formatted_size ?? '-'),

                Tables\Columns\TextColumn::createdAt('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_arsip')
                    ->label('Jenis Arsip')
                    ->options([
                        'press_release' => 'Press Release',
                        'foto' => 'Foto',
                        'video' => 'Video',
                    ]),

                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_bidang'),

                Tables\Filters\Filter::make('tgl_kegiatan')
                    ->label('Tanggal Kegiatan')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tgl'),
                        Forms\Components\DatePicker::make('sampai_tgl'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tgl'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_kegiatan', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tgl'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tgl_kegiatan', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->placeholder('Cari judul atau deskripsi...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['search'],
                                fn(Builder $query, $search): Builder => $query->where(function ($q) use ($search) {
                                    $q->where('judul_arsip', 'like', "%{$search}%")
                                        ->orWhere('deskripsi', 'like', "%{$search}%");
                                }),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DownloadAction::make()
                    ->url(fn(ArsipData $record): string => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('download_selected')
                        ->label('Download Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Logic untuk download multiple files
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Forms\Components\Section::make('Informasi Arsip')
                    ->schema([
                        Forms\Components\TextEntry::make('judul_arsip'),
                        Forms\Components\TextEntry::make('jenis_arsip')
                            ->format(fn(string $state): string => match ($state) {
                                'press_release' => 'Press Release',
                                'foto' => 'Foto',
                                'video' => 'Video',
                                default => $state,
                            }),
                        Forms\Components\TextEntry::make('tgl_kegiatan')
                            ->date('d F Y'),
                        Forms\Components\TextEntry::make('deskripsi'),
                    ]),

                Forms\Components\Section::make('File')
                    ->schema([
                        Forms\Components\TextEntry::make('file_name'),
                        Forms\Components\TextEntry::make('file_size')
                            ->formatStateUsing(fn(ArsipData $record): string => $record->formatted_size ?? '-'),
                        Forms\Components\TextEntry::make('file_path')
                            ->label('Preview')
                            ->html(fn(ArsipData $record): string => match ($record->jenis_arsip) {
                                'foto' => "<img src='" . asset('storage/' . $record->file_path) . "' class='w-64 h-64 object-cover rounded' />",
                                'press_release' => "<a href='" . asset('storage/' . $record->file_path) . "' target='_blank' class='text-primary hover:underline'>Buka PDF</a>",
                                'video' => "<video src='" . asset('storage/' . $record->file_path) . "' controls class='w-64'></video>",
                                default => '-',
                            }),
                    ]),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextEntry::make('kategori.nama_bidang')->label('Kategori'),
                        Forms\Components\TextEntry::make('user.name')->label('Diupload Oleh'),
                        Forms\Components\TextEntry::make('created_at')->label('Dibuat')->dateTime('d F Y H:i'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArsipData::route('/'),
            'create' => Pages\CreateArsipData::route('/create'),
            'edit' => Pages\EditArsipData::route('/{record}/edit'),
            'view' => Pages\ViewArsipData::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? (string) static::getModel()::count() : null;
    }
}
