<?php

namespace App\Filament\Resources;

use App\Models\City;
use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Models\Country;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component as Livewire;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;
    public static function getNavigationGroup(): ?string
    {
        return __('main.companies_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('main.companies');
    }

    public static function getModelLabel(): string
    {
        return __('main.company');
    }

    public static function getPluralModelLabel(): string
    {
        return __('main.companies');
    }

    /*    public static function getEloquentQuery(): Builder
       {
           return parent::getEloquentQuery()->with('user');
       } */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('fields.name'))
                            ->string()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('fields.phone'))
                            ->tel()->minLength(8)->maxLength(11),
                        TextInput::make('website')
                            ->label(__('fields.website'))
                            ->url()
                            ->maxLength(255),
                        TextInput::make('ceo_name')
                            ->label(__('fields.ceo_name'))
                            ->maxLength(255),
                        TextInput::make('tax_name')
                            ->label(__('fields.tax_name'))
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label(__('fields.user'))
                            ->relationship('user', 'email')
                            ->exists('users', 'id')
                            ->searchable()
                            ->preload()
                            ->required()

                    ]),
                Section::make()
                    ->columnSpan(1)
                    ->schema([
                        Select::make('country_id')
                            ->label(__('fields.country'))
                            ->relationship('country', 'name')
                            ->exists('countries', 'id')
                            ->live()->preload()->searchable()->required()->reactive()
                            ->afterStateUpdated(fn(callable $set, $state) => $set('state_id', null)),

                        Select::make('state_id')
                            ->label(__('fields.state'))
                            ->exists('states', 'id')
                            ->reactive()->required()->preload()->live()
                            ->relationship('state', 'name', fn($query, Get $get) => $query->where('country_id', $get('country_id')))
                            ->disabled(fn(Get $get) => $get('country_id') ? false : true)
                            ->afterStateUpdated(fn(callable $set, $state) => $set('city_id', null))
                            ->searchable(static fn(Select $component) => !$component->isDisabled()),

                        Select::make('city_id')
                            ->label(__('fields.city'))
                            ->reactive()->live()->preload()
                            ->exists('cities', 'id')
                            ->relationship('city', 'name', fn($query, Get $get) => $query->where('state_id', $get('state_id')))
                            ->disabled(fn(Get $get) => $get('country_id') && $get('state_id') ? false : true)
                            ->searchable(static fn(Select $component) => !$component->isDisabled()),

                        TextInput::make('national_address')
                            ->string()
                            ->label(__('fields.national_address')),
                        TextInput::make('zipcode')
                            ->numeric()
                            ->label(__('fields.zip_code'))
                            ->required(),
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        Textarea::make('description')
                            ->label(__('fields.description')),
                        FileUpload::make('logo')
                            ->label(__('fields.logo'))
                            ->image()
                            ->directory('companies/logos'),


                    ])
                /*                 Repeater::make('contacts')
                                    ->relationship()
                                    ->schema([
                                        Select::make('type')
                                            ->label('Type')
                                            ->options([
                                                config('administration.contacts.types.email') => config('administration.contacts.types.email'),
                                                config('administration.contacts.types.phone') => config('administration.contacts.types.phone'),
                                            ])
                                            ->required(),
                                        TextInput::make('contact')
                                            ->label('Contact')
                                            ->required()
                                            ->maxLength(255)
                                            ->rule(function ($get) {
                                                return $get('type') === 'email' ? 'email' : 'regex:/^\+?[1-9]\d{1,14}$/';
                                            }),
                                    ])
                                    ->minItems(1)
                                    ->maxItems(10), */
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('fields.name'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('fields.description'))
                    ->searchable(),
                TextColumn::make('ceo_name')
                    ->label(__('fields.ceo_name'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('fields.phone')),
                TextColumn::make('zipcode')
                    ->label(__('fields.zip_code'))
                    ->searchable(),
                TextColumn::make('national_address')
                    ->label(__('fields.national_address'))
                    ->searchable(),
                TextColumn::make('website')
                    ->label(__('fields.website'))
                    ->searchable(),
                TextColumn::make('country.name')
                    ->label(__('fields.country'))
                    ->sortable(),
                TextColumn::make('state.name')
                    ->label(__('fields.state'))
                    ->sortable(),
                TextColumn::make('city.name')
                    ->label(__('fields.city'))
                    ->sortable(),
                TextColumn::make('tax_name')
                    ->label(__('fields.tax_name'))
                    ->searchable(),
                TextColumn::make('logo')
                    ->label(__('fields.logo'))
                    ->searchable(),
                IconColumn::make('subscribed')
                    ->label(__('fields.has_subscription'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
