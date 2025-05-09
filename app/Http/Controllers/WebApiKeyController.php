<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class WebApiKeyController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:5,1')->only(['store', 'destroy']);
    }

    /**
     * List a user’s API keys.
     */
    public function index()
    {
        $user = Auth::user();
        $apiKeys = $user->tokens()
            ->select(['uuid', 'name', 'created_at', 'last_used_at', 'expires_at'])
            ->get();

        return view('api-keys.index', compact('apiKeys'));
    }

    /**
     * Create a new scoped, expiring token with a unique name.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('personal_access_tokens')
                    ->where(fn($query) => $query
                        ->where('tokenable_type', User::class)
                        ->where('tokenable_id', $user->id)
                    ),
            ],
        ];

        $messages = [
            'name.unique' => 'You already have an API key named "'. $request->input('name') .'". Please choose a different name.',
        ];

        $request->validate($rules, $messages);


        $tokenResult = $user->createToken(
            $request->input('name'),
            ['read', 'write'],
            Carbon::now()->addDays(30)
        );

        // record in activity log
        activity('api-key')
            ->causedBy($user)
            ->withProperties([
                'token_uuid' => $tokenResult->accessToken->uuid,
            ])
            ->log('created');

        return redirect()->route('api-keys.index')
            ->with('status', 'API key created.')
            ->with('plainTextToken', $tokenResult->plainTextToken);
    }

    /**
     * Revoke a token by its UUID.
     */
    public function destroy(Request $request, string $uuid)
    {
        $user = $request->user();

        $token = $user->tokens()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $token->delete();

        activity('api-key')
            ->causedBy($user)
            ->withProperties(['token_uuid' => $uuid])
            ->log('revoked');

        return redirect()->route('api-keys.index')
            ->with('status', 'API key revoked.');
    }
}
