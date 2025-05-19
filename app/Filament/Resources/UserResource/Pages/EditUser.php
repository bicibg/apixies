<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $deactivateAction = Actions\Action::make('deactivate')
            ->label('Deactivate Account')
            ->color('danger')
            ->visible(fn () => !$this->record->trashed())
            ->form([
                Select::make('reason')
                    ->label('Deactivation Reason')
                    ->options(UserResource::$deactivationReasons)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = $this->record;

                // Store reason before deleting
                $user->deleted_reason = $data['reason'];
                $user->save();

                // Send notification to user
                $user->notify(new \App\Notifications\AccountDeactivated());

                // Soft delete the user
                $user->delete();

                Notification::make()
                    ->title('Account deactivated successfully')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $user]));
            });

        $reactivateAction = Actions\Action::make('reactivate')
            ->label('Reactivate Account')
            ->color('success')
            ->visible(fn () => $this->record->trashed())
            ->action(function (): void {
                $user = $this->record;

                // Clear the deactivation reason
                $user->deleted_reason = null;

                // Restore the soft-deleted record
                $user->restore();
                $user->save();

                Notification::make()
                    ->title('Account reactivated successfully')
                    ->success()
                    ->send();

                $this->redirect($this->getResource()::getUrl('edit', ['record' => $user]));
            });

        $getRestorationLinkAction = Actions\Action::make('getRestorationLink')
            ->label('Get Restoration Link')
            ->visible(fn () => $this->record->trashed())
            ->modalHeading('Restoration Link')
            ->modalDescription('This link can be shared with the user to restore their account.')
            ->modalContent(fn () => new HtmlString('
                <div class="py-4">
                    <textarea id="restoration-link" rows="3" class="w-full border border-gray-300 rounded p-2 text-sm" readonly>' .
                URL::temporarySignedRoute(
                    'profile.restore',
                    now()->addDays(30),
                    ['id' => $this->record->id]
                ) .
                '</textarea>
                </div>
            '))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');

        return [
            $deactivateAction,
            $reactivateAction,
            $getRestorationLinkAction,
        ];
    }

    public function sendVerificationEmail()
    {
        $this->record->sendEmailVerificationNotification();

        Notification::make()
            ->title('Verification email sent')
            ->success()
            ->send();
    }

    public function sendPasswordReset()
    {
        $this->record->sendPasswordResetNotification(
            \Illuminate\Support\Facades\Password::broker()->createToken($this->record)
        );

        Notification::make()
            ->title('Password reset email sent')
            ->success()
            ->send();
    }

    public function resendDeactivationEmail()
    {
        $this->record->notify(new \App\Notifications\AccountDeactivated());

        Notification::make()
            ->title('Deactivation email sent')
            ->success()
            ->send();
    }
}
