<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\Suggestion;
use App\Models\SuggestionVote;
use App\Helpers\ApiResponse;

class SuggestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Suggestion::query()
            ->where('status', 'pending');

        if ($request->boolean('top')) {
            $query->orderByDesc('votes');
        } else {
            $query->latest();
        }

        return ApiResponse::success(
            $query->take(50)->get(),
            'Suggestions fetched'
        );
    }

    public function store(Request $request)
    {
        /* simple abuse throttle */
        RateLimiter::attempt(
            key: 'suggestion:' . $request->ip(),
            maxAttempts: 5,
            callback: static function () {},
            decaySeconds: 60
        ) || abort(429, 'Too many suggestions, slow down.');

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:2000'],
            'email'   => ['nullable', 'email', 'max:255'],
        ]);

        $suggestion = Suggestion::create([
            'title'        => $data['title'],
            'details'      => $data['details'] ?? null,
            'author_id'    => auth()->id(),
            'author_email' => $data['email'] ?? null,
        ]);

        return ApiResponse::success($suggestion, 'Suggestion submitted');
    }
    public function vote(Request $request, Suggestion $suggestion)
    {
        $hash = auth()->id()
            ? null
            : hash('sha256', $request->ip() . '|' . $request->userAgent());

        $already = SuggestionVote::where('suggestion_id', $suggestion->id)
            ->when(auth()->id(), fn($q) => $q->where('user_id', auth()->id()))
            ->when($hash, fn($q) => $q->where('anon_hash', $hash))
            ->exists();

        if ($already) {
            // Fixed: order of parameters matches the ApiResponse::error method definition
            return ApiResponse::error('You already voted for this suggestion', 409, [], 'ALREADY_VOTED');
        }

        SuggestionVote::create([
            'suggestion_id' => $suggestion->id,
            'user_id'       => auth()->id(),
            'anon_hash'     => $hash,
        ]);

        $suggestion->increment('votes');

        return ApiResponse::success($suggestion->only('id', 'votes'), 'Vote recorded');
    }

    public function board()
    {
        $ideas = Suggestion::whereIn('status', ['pending', 'planned'])
            ->orderByDesc('votes')
            ->paginate(15);

        return view('suggestions.board', compact('ideas'));
    }
}
