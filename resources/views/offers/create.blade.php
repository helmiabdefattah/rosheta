<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Offer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Create New Offer</h1>
                <p class="mt-2 text-sm text-gray-600">Fill in the details below to create a new offer</p>
            </div>

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('offers.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Offer Details Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Offer Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Client Request -->
                        <div>
                            <label for="client_request_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Client Request <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="client_request_id" 
                                id="client_request_id"
                                required
                                x-data="{ requestId: '{{ old('client_request_id', $clientRequest?->id ?? '') }}' }"
                                x-on:change="requestId = $event.target.value; $dispatch('request-changed', { id: requestId })"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select a client request</option>
                                @foreach($clientRequests as $id => $label)
                                    <option value="{{ $id }}" {{ old('client_request_id', $clientRequest?->id ?? '') == $id ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_request_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Pharmacy -->
                        <div>
                            <label for="pharmacy_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Pharmacy <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="pharmacy_id" 
                                id="pharmacy_id"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select a pharmacy</option>
                                @foreach($pharmacies as $id => $name)
                                    <option value="{{ $id }}" {{ old('pharmacy_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pharmacy_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- User -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                User <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="user_id" 
                                id="user_id"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select a user</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ old('user_id', Auth::id()) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="status" 
                                id="status"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ old('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="accepted" {{ old('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Request Images Card -->
                <div 
                    x-data="{ 
                        requestId: '{{ old('client_request_id', $clientRequest?->id ?? '') }}',
                        images: @js($clientRequest?->images ?? []),
                        loadImages(id) {
                            if (!id) {
                                this.images = [];
                                return;
                            }
                            fetch(`/api/client-requests/${id}/images`)
                                .then(res => res.json())
                                .then(data => this.images = data.images || [])
                                .catch(() => this.images = []);
                        }
                    }"
                    x-on:request-changed.window="requestId = $event.detail.id; loadImages(requestId)"
                    x-show="images.length > 0"
                    class="bg-white shadow rounded-lg p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Request Images</h2>
                    <div class="flex flex-wrap gap-4">
                        <template x-for="(image, index) in images" :key="index">
                            <a :href="image" target="_blank" class="block border border-gray-200 rounded-lg p-2 hover:shadow-md transition-shadow">
                                <img :src="image" alt="Prescription" class="h-48 w-auto object-cover rounded">
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Client Request Lines Card -->
                <div 
                    x-data="{ 
                        requestId: '{{ old('client_request_id', $clientRequest?->id ?? '') }}',
                        requestLines: @js($clientRequest ? $clientRequest->lines->map(function($l) {
                            return [
                                'medicine_id' => $l->medicine_id,
                                'medicine_name' => $l->medicine->name ?? 'N/A',
                                'quantity' => $l->quantity,
                                'unit' => $l->unit,
                            ];
                        })->toArray() : []),
                        loadRequestLines(id) {
                            if (!id) {
                                this.requestLines = [];
                                return;
                            }
                            fetch(`/api/client-requests/${id}/lines`)
                                .then(res => res.json())
                                .then(data => this.requestLines = data.lines || [])
                                .catch(() => this.requestLines = []);
                        }
                    }"
                    x-on:request-changed.window="requestId = $event.detail.id; loadRequestLines(requestId)"
                    x-show="requestLines.length > 0"
                    class="bg-white shadow rounded-lg p-6"
                >
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Client Request Lines</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicine</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(line, index) in requestLines" :key="index">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="line.medicine_name"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" x-text="line.quantity"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700" x-text="line.unit"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button 
                                                type="button"
                                                @click="addOfferLine(line.medicine_id, line.quantity, line.unit)"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Add to Offer
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Offer Lines Card -->
                <div 
                    x-data="{
                        offerLines: @js(old('offer_lines', [])),
                        medicines: @js(\App\Models\Medicine::pluck('name', 'id')->toArray()),
                        addOfferLine(medicineId = null, quantity = 1, unit = 'box') {
                            this.offerLines.push({
                                medicine_id: medicineId || '',
                                quantity: quantity,
                                unit: unit,
                                price: ''
                            });
                            this.updateTotal();
                        },
                        removeOfferLine(index) {
                            this.offerLines.splice(index, 1);
                            this.updateTotal();
                        },
                        updateTotal() {
                            this.$nextTick(() => {
                                let total = 0;
                                this.offerLines.forEach((line, index) => {
                                    const qty = parseFloat(document.getElementById(`quantity_${index}`)?.value || 0);
                                    const price = parseFloat(document.getElementById(`price_${index}`)?.value || 0);
                                    total += qty * price;
                                });
                                document.getElementById('total_price').value = total.toFixed(2);
                            });
                        }
                    }"
                    class="bg-white shadow rounded-lg p-6"
                >
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Offer Lines</h2>
                        <button 
                            type="button"
                            @click="addOfferLine()"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Line
                        </button>
                    </div>

                    <div class="space-y-4" x-show="offerLines.length > 0">
                        <template x-for="(line, index) in offerLines" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <!-- Medicine -->
                                    <div>
                                        <label :for="`medicine_${index}`" class="block text-sm font-medium text-gray-700 mb-1">
                                            Medicine <span class="text-red-500">*</span>
                                        </label>
                                        <select 
                                            :name="`offer_lines[${index}][medicine_id]`"
                                            :id="`medicine_${index}`"
                                            required
                                            x-model="line.medicine_id"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="">Select medicine</option>
                                            <template x-for="[id, name] in Object.entries(medicines)" :key="id">
                                                <option :value="id" x-text="name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <label :for="`quantity_${index}`" class="block text-sm font-medium text-gray-700 mb-1">
                                            Quantity <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="number"
                                            :name="`offer_lines[${index}][quantity]`"
                                            :id="`quantity_${index}`"
                                            required
                                            min="1"
                                            x-model="line.quantity"
                                            @input="updateTotal()"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                    </div>

                                    <!-- Unit -->
                                    <div>
                                        <label :for="`unit_${index}`" class="block text-sm font-medium text-gray-700 mb-1">
                                            Unit <span class="text-red-500">*</span>
                                        </label>
                                        <select 
                                            :name="`offer_lines[${index}][unit]`"
                                            :id="`unit_${index}`"
                                            required
                                            x-model="line.unit"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                            <option value="box">Box</option>
                                            <option value="strips">Strips</option>
                                            <option value="bottle">Bottle</option>
                                            <option value="pack">Pack</option>
                                            <option value="piece">Piece</option>
                                        </select>
                                    </div>

                                    <!-- Price -->
                                    <div>
                                        <label :for="`price_${index}`" class="block text-sm font-medium text-gray-700 mb-1">
                                            Price (EGP) <span class="text-red-500">*</span>
                                        </label>
                                        <input 
                                            type="number"
                                            step="0.01"
                                            :name="`offer_lines[${index}][price]`"
                                            :id="`price_${index}`"
                                            required
                                            min="0"
                                            x-model="line.price"
                                            @input="updateTotal()"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        >
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-end">
                                        <button 
                                            type="button"
                                            @click="removeOfferLine(index)"
                                            class="w-full px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="offerLines.length === 0" class="text-center py-8 text-gray-500">
                        No offer lines added yet. Click "Add Line" to start.
                    </div>
                </div>

                <!-- Total Price -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <label for="total_price" class="text-lg font-semibold text-gray-900">
                            Total Price
                        </label>
                        <div class="flex items-center space-x-2">
                            <input 
                                type="text"
                                id="total_price"
                                name="total_price"
                                readonly
                                value="{{ old('total_price', '0.00') }}"
                                class="text-right text-2xl font-bold text-gray-900 border-none bg-transparent focus:outline-none"
                            >
                            <span class="text-lg font-semibold text-gray-600">EGP</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4">
                    <a 
                        href="{{ url()->previous() }}"
                        class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Create Offer
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

