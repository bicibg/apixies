<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WebApiKeyController extends Controller
{
    // Show API keys page
    public function index()
    {
        $user = Auth::user();
        $apiKeys = $user->tokens()->get(['id', 'name', 'created_at', 'last_used_at']);

        return view('api-keys.index', compact('apiKeys'));
    }

    // Handle new API key creation
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Make sure name is unique for this user's tokens
                Rule::unique('personal_access_tokens', 'name')
                    ->where('tokenable_type', get_class(Auth::user()))
                    ->where('tokenable_id', Auth::id())
            ],
        ], [
            'name.unique' => 'You already have an API key with this name. Please use a different name.',
        ]);

        $tokenResult = $request->user()->createToken($data['name']);

        // Store the newly created token in the session briefly so we can display it
        $request->session()->flash('new_token', [
            'id' => $tokenResult->accessToken->id,
            'name' => $tokenResult->accessToken->name,
            'token' => $tokenResult->plainTextToken,
        ]);

        return redirect()->route('api-keys.index')
            ->with('status', 'API key created successfully');
    }

    // Handle API key deletion
    public function destroy(Request $request, $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();

        return redirect()->route('api-keys.index')
            ->with('status', 'API key revoked successfully');
    }
}
