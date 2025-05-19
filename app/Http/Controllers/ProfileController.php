<?php

namespace App\Http\Controllers;

use App\Models\ApiEndpointLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $apiRequestCount = ApiEndpointLog::where('user_id', Auth::id())->count();

        return view('account.settings', [
            'apiRequestCount' => $apiRequestCount
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')->with('status', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('status', 'Password updated successfully.');
    }

    /**
     * Mark the user's account as deleted (soft delete).
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'delete_confirmation' => ['required', 'string', 'in:DELETE'],
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Store information that account was deleted
        $user->deleted_reason = 'User requested account deletion';
        $user->save();

        // Send deactivation notification with restoration link
        // BEFORE logging out and soft deleting to ensure email delivery
        $user->notify(new \App\Notifications\AccountDeactivated());

        // Process soft delete - this will set deleted_at but keep the record
        $user->delete();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.deactivated');
    }

    /**
     * Restore a deleted user account within the 30-day grace period.
     *
     * @param  Request  $request
     * @param $id
     * @return RedirectResponse
     */
    public function restore(Request $request, $id)
    {
        // Only administrators can restore other accounts
        if (Auth::user()->is_admin || Auth::id() == $id) {
            $user = User::withTrashed()->findOrFail($id);

            // Check if within 30-day grace period
            $deleteDate = $user->deleted_at;
            $thirtyDaysAfterDelete = $deleteDate->copy()->addDays(30);

            if (now()->lessThan($thirtyDaysAfterDelete)) {
                $user->restore();
                $user->deleted_reason = null;
                $user->save();

                return redirect()->route('profile.show')
                    ->with('status', 'Your account has been successfully restored.');
            }

            return back()->with('error', 'Account restoration is only available within 30 days of deletion.');
        }

        return back()->with('error', 'You do not have permission to restore this account.');
    }
}
