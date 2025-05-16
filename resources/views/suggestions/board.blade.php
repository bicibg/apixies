@extends('layouts.app')

@section('title', 'Community Ideas')

@section('content')
    {{-- Alpine root handles toast + disables multiple votes --}}
    <div class="max-w-4xl mx-auto px-4 py-10" x-data="votesBoard()">

        {{-- █ Hero -------------------------------------------------------- --}}
        <div class="suggestions-hero p-8 mb-6 rounded-lg shadow-md text-white flex items-start justify-between">
            <div>
                <h2 class="card-heading">Community Ideas</h2>
                <p class="text-xl opacity-90">Up‑vote micro‑APIs you'd like us to build next.</p>
            </div>

            {{-- Suggest button --}}
            <div class="self-center">
                <x-suggest-modal/>
            </div>
        </div>

        {{-- █ Toast (hidden until shown) ---------------------------------- --}}
        <div x-show="toast.show"
             x-transition
             :class="toast.type === 'success' ? 'bg-blue-500/90' : 'bg-orange-500/90'"
             class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 flex items-center gap-2 z-50 text-white px-5 py-3 rounded-md shadow-xl max-w-md"
             x-cloak>
            <!-- Icon -->
            <span class="flex-shrink-0">
                <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </span>

            <!-- Message -->
            <span x-text="toast.message"></span>
        </div>

        {{-- █ Idea list --------------------------------------------------- --}}
        <div class="bg-white rounded-lg shadow p-6">
            <table class="w-full text-sm">
                <thead>
                <tr>
                    <th class="text-left pb-2">Idea</th>
                    <th class="w-24 text-center pb-2">Votes</th>
                    <th class="w-32"></th>
                </tr>
                </thead>

                <tbody>
                @foreach ($ideas as $idea)
                    <tr class="border-t last:border-b">
                        <td class="py-4">
                            <div class="font-medium text-navy">{{ $idea->title }}</div>
                            @if($idea->details)
                                <div class="text-gray-600 mt-1">{{ $idea->details }}</div>
                            @endif
                        </td>

                        <td class="text-center font-semibold" id="votes-{{ $idea->id }}">{{ $idea->votes }}</td>

                        <td class="text-center">
                            <button
                                class="btn-primary w-auto px-4 py-1 text-sm"
                                :disabled="disabled[{{ $idea->id }}]"
                                @click="vote({{ $idea->id }})"
                            >
                                Vote
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-6">{{ $ideas->links() }}</div>
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
                }
            }
        }
    </script>
@endsection
