@extends('layouts.app')

@section('title', 'Community Ideas')

@section('content')
    {{-- Alpine root handles toast + disables multiple votes + filtering --}}
    <div class="max-w-5xl mx-auto px-4 pt-4" x-data="votesBoard()">
        {{-- █ Hero Section -------------------------------------------------- --}}
        <div class="bg-gradient-to-r from-navy to-teal rounded-lg shadow-md mb-6 overflow-hidden">
            <div class="px-6 py-8 flex flex-col md:flex-row justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-2">Community Ideas Board</h1>
                    <p class="text-blue-100 max-w-xl">
                        Help shape the future of Apixies by suggesting and voting for the micro-APIs you'd like to see next.
                    </p>
                </div>
                <div class="mt-4 md:mt-0 self-center">
                    <x-suggest-modal/>
                </div>
            </div>

            <div class="bg-navy-dark/30 px-6 py-2 flex items-center text-blue-100 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Ideas are ordered by popularity. Most voted ideas are more likely to be implemented.</span>
            </div>
        </div>

        {{-- █ Toast Notification -------------------------------------------- --}}
        <div x-show="toast.show"
             x-transition
             :class="toast.type === 'success' ? 'bg-teal-600 text-white' : 'bg-danger-600 text-white'"
             class="fixed top-20 left-1/2 transform -translate-x-1/2 flex items-center gap-2 z-50 px-5 py-3 rounded-md shadow-xl max-w-md"
             x-cloak>
            <!-- Icon -->
            <span class="flex-shrink-0">
                <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </span>

            <!-- Message -->
            <span x-text="toast.message" class="font-medium"></span>
        </div>

        {{-- █ Ideas Cards Section ------------------------------------------- --}}
        <div class="grid grid-cols-1 gap-6">
            <!-- Filters & Stats -->
            <div class="flex flex-col md:flex-row justify-between items-center bg-white rounded-xl shadow-sm p-4 mb-2">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <span class="text-gray-700 font-medium">Filter:</span>
                    <a href="{{ route('suggestions.board') }}" class="px-3 py-1.5 rounded-full text-sm mr-2 {{ $filter === 'all' ? 'bg-teal text-white' : 'bg-gray-100 text-gray-700' }}">
                        All Ideas
                    </a>
                    <a href="{{ route('suggestions.board').'?filter=planned' }}" class="px-3 py-1.5 rounded-full text-sm mr-2 {{ $filter === 'planned' ? 'bg-teal text-white' : 'bg-gray-100 text-gray-700' }}">
                        Planned
                    </a>
                    <a href="{{ route('suggestions.board').'?filter=done' }}" class="px-3 py-1.5 rounded-full text-sm {{ $filter === 'done' ? 'bg-teal text-white' : 'bg-gray-100 text-gray-700' }}">
                        Implemented
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-700">{{ $ideas->firstItem() ?? 0 }}-{{ $ideas->lastItem() ?? 0 }}</span> of <span class="font-medium text-gray-700">{{ $ideas->total() }}</span> ideas
                </div>
            </div>

            <!-- Ideas List -->
            @foreach ($ideas as $idea)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 hover:border-teal-200 transition-all duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-navy mb-2">{{ $idea->title }}</h3>
                                @if($idea->details)
                                    <p class="text-gray-600 mb-4">{{ $idea->details }}</p>
                                @endif
                                <div class="flex items-center text-xs text-gray-500 mt-2">
                                    <span class="bg-blue-50 rounded-full px-2 py-1 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $idea->created_at->diffForHumans() }}
                                    </span>

                                    @if($idea->status === 'planned')
                                        <span class="ml-2 bg-teal-50 text-teal-700 rounded-full px-2 py-1 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Planned
                                        </span>
                                    @elseif($idea->status === 'done')
                                        <span class="ml-2 bg-green-50 text-green-700 rounded-full px-2 py-1 inline-flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Done
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center mt-4 md:mt-0">
                                <div class="flex flex-col items-center mr-4">
                                    <div class="text-2xl font-bold text-teal" id="votes-{{ $idea->id }}">{{ $idea->votes }}</div>
                                    <div class="text-gray-500 text-xs">votes</div>
                                </div>

                                <button
                                    :class="disabled[{{ $idea->id }}] ? 'opacity-75 cursor-not-allowed' : 'hover:bg-teal-700 hover:scale-105'"
                                    class="inline-flex items-center justify-center bg-teal text-white font-medium rounded-lg px-4 py-2 transition transform duration-200"
                                    :disabled="disabled[{{ $idea->id }}]"
                                    @click="vote({{ $idea->id }})"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    Vote
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-6">
                {{ $ideas->links() }}
            </div>

            <!-- Empty State -->
            @if($ideas->isEmpty())
                <div class="bg-white rounded-xl shadow-sm p-10 text-center">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No ideas yet</h3>
                    <p class="text-gray-500 mb-6">Be the first to suggest a new API feature!</p>
                    <x-suggest-modal/>
                </div>
            @endif
        </div>

        {{-- █ How It Works Section ------------------------------------------- --}}
        <div class="bg-blue-50 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-navy mb-4">How it works</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex items-start">
                    <div class="bg-white rounded-full w-7 h-7 flex items-center justify-center mr-3 shadow-sm flex-shrink-0">
                        <span class="text-teal font-bold">1</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-navy">Suggest an API</h4>
                        <p class="text-sm text-gray-600">Share your idea for a new micro-API that would be useful for your projects.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="bg-white rounded-full w-7 h-7 flex items-center justify-center mr-3 shadow-sm flex-shrink-0">
                        <span class="text-teal font-bold">2</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-navy">Get votes</h4>
                        <p class="text-sm text-gray-600">The community votes on the most valuable ideas to prioritize development.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="bg-white rounded-full w-7 h-7 flex items-center justify-center mr-3 shadow-sm flex-shrink-0">
                        <span class="text-teal font-bold">3</span>
                    </div>
                    <div>
                        <h4 class="font-medium text-navy">We build it</h4>
                        <p class="text-sm text-gray-600">Top-voted ideas get implemented and become available in the Apixies suite.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JS helper -------------------------------------------------------- --}}
    <script>
        function votesBoard() {
            return {
                disabled: {},       // per‑id lock
                toast: {show: false, message: '', type: 'success'},

                vote(id) {
                    if (this.disabled[id]) return;
                    this.disabled[id] = true;

                    fetch('/suggestions/' + id + '/vote', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.status === 'success') {
                                document.getElementById('votes-' + id).innerText = data.data.votes;
                                this.showToast('Thanks for voting!', 'success');
                            } else {
                                this.showToast(data.message || 'Already voted.', 'error');
                                this.disabled[id] = false;
                            }
                        })
                        .catch(() => {
                            this.showToast('Network error – try again', 'error');
                            this.disabled[id] = false;
                        });
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 3000);
                },

                init() {
                    // Listen for the custom event from the modal
                    window.addEventListener('suggestion-submitted', () => {
                        this.showToast('Thanks for your suggestion!', 'success');
                    });
                }
            }
        }
    </script>
@endsection
