<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuggestionResource\Pages;
use App\Models\Suggestion;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class SuggestionResource extends Resource
{
    protected static ?string $model            = Suggestion::class;
    protected static ?string $navigationGroup  = 'Feedback';
    protected static ?string $navigationIcon   = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel  = 'Suggestions';
    protected static ?int    $navigationSort   = 1;

    /* ------------ FORM ------------ */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')
                ->required()->maxLength(120)->columnSpan('full'),

            Forms\Components\Textarea::make('details')
                ->maxLength(2000)->rows(6),

            Forms\Components\Select::make('status')
                ->options([
                    'pending'  => 'Pending',
                    'planned'  => 'Planned',
                    'rejected' => 'Rejected',
                    'done'     => 'Done',
                ])
                ->native(false)->default('pending'),

            Forms\Components\TextInput::make('votes')
                ->disabled()->numeric()
                ->dehydrated(false),
        ])->columns(2);
    }

    /* ------------ TABLE ----------- */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('votes')->label('❤')->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'primary'  => 'pending',
                        'success'  => 'planned',
                        'danger'   => 'rejected',
                        'gray'     => 'done',
                    ]),
                Tables\Columns\TextColumn::make('created_at')->date('Y‑m‑d'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending'  => 'Pending',
                    'planned'  => 'Planned',
                    'rejected' => 'Rejected',
                    'done'     => 'Done',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('planned')
                    ->label('Mark Planned')->icon('heroicon-o-check')
                    ->action(fn ($recs) => $recs->each->update(['status' => 'planned'])),

                Tables\Actions\BulkAction::make('rejected')
                    ->label('Reject')->color('danger')->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->action(fn ($recs) => $recs->each->update(['status' => 'rejected'])),
            ]);
    }

    /* ------------ PAGES ------------ */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuggestions::route('/'),
            'view'  => Pages\ViewSuggestion::route('/{record}'),
            'edit'  => Pages\EditSuggestion::route('/{record}/edit'),
        ];
    }
}
