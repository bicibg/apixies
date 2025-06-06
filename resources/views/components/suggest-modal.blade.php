{{-- <x-suggest-modal /> --}}
<div x-data="suggestModal()" x-cloak>

    {{-- trigger button --}}
    <button @click="open = true"
            class="btn-primary w-auto px-6">
        Suggest an API
    </button>

    {{-- overlay + dialog --}}
    <div x-show="open"
         x-transition.opacity
         class="fixed inset-0 flex items-center justify-center z-[9999]
            bg-navy/50 backdrop-blur-sm">

        <div @click.away="open = false"
             @keydown.escape.window="open = false"
             x-transition.scale
             class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg relative">

            <h2 class="text-lg font-semibold text-navy mb-4">Suggest a micro‑API</h2>

            <form @submit.prevent="submit" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Idea Title *</label>
                    <input x-model="title" required maxlength="120"
                           class="w-full border border-gray-300 rounded-md p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal focus:border-transparent"/>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                    <textarea x-model="details" rows="4" maxlength="2000"
                              class="w-full border border-gray-300 rounded-md p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal focus:border-transparent"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                    <input x-model="email" type="email" maxlength="255"
                           class="w-full border border-gray-300 rounded-md p-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-teal focus:border-transparent"/>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 rounded bg-blue-50 hover:bg-blue-100 text-gray-800">Cancel</button>
                    <button type="submit" class="btn-primary w-auto px-6 bg-gradient-to-r from-teal to-blue-600 hover:from-teal-600 hover:to-blue-700">Submit</button>
                </div>
            </form>

            <p x-text="message" class="mt-4 text-sm" :class="message ? 'text-success-600' : ''"></p>
        </div>
    </div>

    <script>
        function suggestModal () {
            return {
                "title": "Suggest an API",
                "open": false,
                "title": "",
                "details": "",
                "email": "",
                "message": "",
                "submit": function() {
                    fetch('{{ url('/suggestions') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            title: this.title,
                            details: this.details,
                            email: this.email
                        })
                    })
                        .then(r => r.json())
                        .then(d => {
                            this.message = d.message ?? 'Thank you! Your suggestion was received.';
                            if (d.status === 'success') {
                                this.title = this.details = this.email = '';

                                // Dispatch a custom event for the parent component
                                window.dispatchEvent(new CustomEvent('suggestion-submitted'));

                                // Keep the modal open to show success message for 1.5 seconds
                                setTimeout(() => {
                                    this.open = false;
                                    this.message = '';
                                }, 1500);
                            }
                        })
                        .catch(() => this.message = 'Something went wrong.');
                }
            }
        }
    </script>

</div>
