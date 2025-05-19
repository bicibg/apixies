<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Suggestion;
use App\Models\SuggestionVote;
use App\Helpers\ApiResponse;

class SuggestionController extends Controller
{    public function store(Request $request)
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

    public function board(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Suggestion::query();

        // Apply filtering based on status
        if ($filter === 'planned') {
            $query->where('status', 'planned');
        } elseif ($filter === 'implemented' || $filter === 'done') {
            $query->where('status', 'done');
        } elseif ($filter === 'pending') {
            $query->where('status', 'pending');
        } else {
            // 'all' filter - show everything
            $query->whereIn('status', ['pending', 'planned', 'done']);
        }

        // Sort by votes (highest first)
        $ideas = $query->orderByDesc('votes')
            ->paginate(15)
            ->withQueryString(); // Keep filter parameter in pagination links

        return view('suggestions.board', compact('ideas', 'filter'));
    }
}
