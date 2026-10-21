<?php

namespace App\Filament\Resources;

use Filament\Support\Icons\Heroicon;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $pluralLabel = 'Pengguna';

    protected static ?string $label = 'User';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('Contoh: humas_admin'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Role & Keamanan')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->required()
                            ->options([
                                'administrator' => 'Administrator (Staf Humas)',
                                'pimpinan' => 'Pimpinan (Ketua Tim)',
                            ])
                            ->default('administrator'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->confirmed()
                            ->dehydrated(fn(string $operation): bool => $operation !== 'view'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'primary' => 'administrator',
                        'success' => 'pimpinan',
                    ])
                    ->format(fn(string $state): string => match ($state) {
                        'administrator' => 'Administrator',
                        'pimpinan' => 'Pimpinan',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('arsipData_count')
                    ->label('Arsip Diupload')
                    ->counts('arsipData')
                    ->sortable(),

                Tables\Columns\TextColumn::createdAt('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'administrator' => 'Administrator',
                        'pimpinan' => 'Pimpinan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\TextEntry::make('name'),
                        Forms\Components\TextEntry::make('username'),
                        Forms\Components\TextEntry::make('email'),
                    ]),

                Forms\Components\Section::make('Role')
                    ->schema([
                        Forms\Components\TextEntry::make('role')
                            ->format(fn(string $state): string => match ($state) {
                                'administrator' => 'Administrator (Staf Humas)',
                                'pimpinan' => 'Pimpinan (Ketua Tim)',
                                default => $state,
                            }),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash password sebelum disimpan
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hash password jika ada perubahan
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() > 0 ? (string) static::getModel()::count() : null;
    }
}
