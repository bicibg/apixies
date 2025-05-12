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
         class="fixed inset-0 flex items-center justify-center z-50 bg-black/40">

        <div @click.away="open = false"
             @keydown.escape.window="open = false"
             x-transition.scale
             class="bg-white w-full max-w-md p-6 rounded-lg shadow-lg">

            <h2 class="text-lg font-semibold text-[#0A2240] mb-4">Suggest a micro‑API</h2>

            <form @submit.prevent="submit" class="space-y-4">

                <div>
                    <label class="block text-sm font-medium mb-1">Idea Title *</label>
                    <input x-model="title" required maxlength="120" class="form-input"/>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Details</label>
                    <textarea x-model="details" rows="4" maxlength="2000"
                              class="w-full border rounded-md p-2"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email (optional)</label>
                    <input x-model="email" type="email" maxlength="255" class="form-input"/>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 rounded bg-gray-100">Cancel</button>
                    <button type="submit" class="btn-primary w-auto px-6">Submit</button>
                </div>
            </form>

            <p x-text="message" class="mt-4 text-sm"></p>
        </div>
    </div>

    {{-- Alpine component --}}
    <script>
        function suggestModal () {
            return {
                open   : false,
                title  : '',
                details: '',
                email  : '',
                message: '',
                submit() {
                    fetch('{{ url('/suggestions') }}', {
                        method : 'POST',
                        headers: {
                            'Content-Type'  : 'application/json',
                            'X-CSRF-TOKEN'  : document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            title  : this.title,
                            details: this.details,
                            email  : this.email
                        })
                    })
                        .then(r => r.json())
                        .then(d => {
                            this.message = d.message ?? 'Thank you! Your suggestion was received.';
                            if (d.status === 'success') {
                                this.title = this.details = this.email = '';
                                this.open  = false;
                            }
                        })
                        .catch(() => this.message = 'Something went wrong.');
                }
            }
        }
    </script>
</div>
