<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\DirectMailService;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup  = 'Dashboard';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Predefined deactivation reasons
    public static array $deactivationReasons = [
        'user_requested' => 'User requested account deletion',
        'violation' => 'Terms of service violation',
        'spam' => 'Spam or suspicious activity',
        'inactive' => 'Account inactive for extended period',
        'security' => 'Security concern',
        'other' => 'Other (admin discretion)',
    ];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withTrashed();
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->unique(User::class, 'email', ignoreRecord: true),

                    TextInput::make('password')
                        ->password()
                        ->label('Password')
                        ->required(fn ($record) => ! $record)  // required on create
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->maxLength(255),

                    Checkbox::make('is_admin')
                        ->label('Administrator'),

                    DateTimePicker::make('email_verified_at')
                        ->label('Verified At')
                        ->nullable(),
                ]),

                Section::make('Account Status')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('status')
                                    ->label('Current Status')
                                    ->content(fn ($record) => $record->trashed() ? 'Deactivated' : 'Active')
                                    ->extraAttributes(fn ($record) => [
                                        'class' => $record->trashed() ? 'text-danger-500' : 'text-success-500'
                                    ]),

                                Forms\Components\Placeholder::make('deleted_at')
                                    ->label('Deactivated At')
                                    ->content(fn ($record) => $record->deleted_at ? $record->deleted_at->format('M d, Y H:i:s') : '')
                                    ->visible(fn ($record) => $record->trashed()),

                                Forms\Components\Placeholder::make('deleted_reason')
                                    ->label('Deactivation Reason')
                                    ->content(fn ($record) => self::$deactivationReasons[$record->deleted_reason] ?? $record->deleted_reason ?? 'N/A')
                                    ->visible(fn ($record) => $record->trashed()),
                            ]),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('email_actions')
                                    ->label('Email Actions'),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('sendVerificationEmail')
                                        ->label('Send Verification Email')
                                        ->visible(fn ($record) => $record && !$record->hasVerifiedEmail())
                                        ->action(function ($record) {
                                            $success = DirectMailService::sendEmailVerification($record);

                                            Notification::make()
                                                ->title($success ? 'Verification email sent successfully' : 'Failed to send verification email')
                                                ->color($success ? 'success' : 'danger')
                                                ->send();
                                        }),

                                    Forms\Components\Actions\Action::make('sendPasswordReset')
                                        ->label('Send Password Reset')
                                        ->color('warning')
                                        ->action(function ($record) {
                                            $token = Password::broker()->createToken($record);
                                            $success = DirectMailService::sendPasswordReset($record, $token);

                                            Notification::make()
                                                ->title($success ? 'Password reset email sent successfully' : 'Failed to send password reset email')
                                                ->color($success ? 'success' : 'danger')
                                                ->send();
                                        }),

                                    Forms\Components\Actions\Action::make('getRestorationLink')
                                        ->label('Get Restoration Link')
                                        ->visible(fn ($record) => $record->trashed())
                                        ->modalHeading('Restoration Link')
                                        ->modalDescription('This link can be shared with the user to restore their account.')
                                        ->modalContent(fn ($record) => new \Illuminate\Support\HtmlString('
                                            <div class="py-4">
                                                <textarea id="restoration-link" rows="3" class="w-full border border-gray-300 rounded p-2 text-sm" readonly>' .
                                            URL::temporarySignedRoute(
                                                'profile.restore',
                                                now()->addDays(30),
                                                ['id' => $record->id]
                                            ) .
                                            '</textarea>
                                            </div>
                                        '))
                                        ->modalSubmitAction(false)
                                        ->modalCancelActionLabel('Close'),

                                    Forms\Components\Actions\Action::make('resendDeactivationEmail')
                                        ->label('Resend Deactivation Email')
                                        ->visible(fn ($record) => $record->trashed())
                                        ->action(function ($record) {
                                            $success = DirectMailService::sendAccountDeactivated($record);

                                            Notification::make()
                                                ->title($success ? 'Deactivation email sent successfully' : 'Failed to send deactivation email')
                                                ->color($success ? 'success' : 'danger')
                                                ->send();
                                        }),
                                ]),
                            ]),
                    ])
                    ->collapsed(false),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable(),

                BooleanColumn::make('is_admin')
                    ->label('Admin')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($record) => $record->trashed() ? 'Deactivated' : 'Active')
                    ->color(fn ($record) => $record->trashed() ? 'danger' : 'success'),

                TextColumn::make('email_verified_at')
                    ->label('Verified')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query) => $query->whereNotNull('email_verified_at')),

                Filter::make('unverified')
                    ->label('Unverified')
                    ->query(fn (Builder $query) => $query->whereNull('email_verified_at')),

                TernaryFilter::make('is_admin')
                    ->label('Administrator')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->nullable()
                    ->placeholder('All'),

                SelectFilter::make('account_status')
                    ->options([
                        'active' => 'Active',
                        'deactivated' => 'Deactivated',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['value'] === 'active',
                                fn (Builder $query) => $query->whereNull('deleted_at')
                            )
                            ->when(
                                $data['value'] === 'deactivated',
                                fn (Builder $query) => $query->whereNotNull('deleted_at')
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // No reactivate/deactivate button here as requested
            ])
            ->bulkActions([
                // No bulk actions as requested
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
