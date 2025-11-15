@php
    $requestLines = $requestLines ?? [];
    $repeaterId = $repeaterId ?? 'requestLines';
@endphp

@if (!empty($requestLines))
    <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">Medicine</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">Quantity</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">Unit</th>
                    <th class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @foreach ($requestLines as $index => $line)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td style="text-align: center;" class="px-4 text-center py-3 text-sm text-gray-700 dark:text-gray-200">
                            {{ $line['medicine_name'] ?? 'N/A' }}
                        </td>
                        <td style="text-align: center;" class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-200">
                            {{ $line['quantity'] ?? 'N/A' }}
                        </td>
                        <td style="text-align: center;" class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-200">
                            {{ ucfirst($line['unit'] ?? 'N/A') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                type="button"
                                data-medicine-id="{{ $line['medicine_id'] ?? '' }}"
                                data-quantity="{{ $line['quantity'] ?? '' }}"
                                data-unit="{{ $line['unit'] ?? '' }}"
                                onclick="addRequestLineToOffer({{ $line['medicine_id'] ?? 'null' }}, {{ $line['quantity'] ?? 'null' }}, '{{ $line['unit'] ?? '' }}')"
                                class="fi-btn fi-btn-size-sm fi-color-primary inline-flex items-center justify-center gap-x-1 rounded-lg border border-transparent bg-primary-600 px-2 py-1 text-xs font-semibold text-white shadow-sm transition duration-75 hover:bg-primary-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:bg-gray-400 disabled:text-white dark:bg-primary-500 dark:hover:bg-primary-400"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add to Offer
                            </button>
                            
                            <button 
                                class="fi-btn fi-size-sm fi-ac-btn-action" 
                                type="button" 
                                data-medicine-id="{{ $line['medicine_id'] ?? '' }}"
                                data-quantity="{{ $line['quantity'] ?? '' }}"
                                data-unit="{{ $line['unit'] ?? '' }}"
                                wire:loading.attr="disabled" 
                                wire:target="mountAction('add', {}, JSON.parse('{\u0022schemaComponent\u0022:\u0022form.offer-lines::data::section.requestLines\u0022}'))" 
                                wire:click="mountAction('add', {}, JSON.parse('{\u0022schemaComponent\u0022:\u0022form.offer-lines::data::section.requestLines\u0022}'))"
                                onclick="setNewItemValues(this, {{ $line['medicine_id'] ?? 'null' }}, {{ $line['quantity'] ?? 'null' }}, '{{ $line['unit'] ?? '' }}')"
                            >
                                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="fi-icon fi-loading-indicator fi-size-sm" wire:loading.delay.default="" wire:target="mountAction('add', {}, JSON.parse('{\u0022schemaComponent\u0022:\u0022form.offer-lines::data::section.requestLines\u0022}'))">
                                    <path clip-rule="evenodd" d="M12 19C15.866 19 19 15.866 19 12C19 8.13401 15.866 5 12 5C8.13401 5 5 8.13401 5 12C5 15.866 8.13401 19 12 19ZM12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" fill-rule="evenodd" fill="currentColor" opacity="0.2"></path>
                                    <path d="M2 12C2 6.47715 6.47715 2 12 2V5C8.13401 5 5 8.13401 5 12H2Z" fill="currentColor"></path>
                                </svg>
                                Add to request lines
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800 dark:text-gray-400">
        No request lines available. Please select a client request first.
    </div>
@endif

<script>
function addRequestLineToOffer(medicineId, quantity, unit) {
    // Find the form
    const form = document.querySelector('form') || document.querySelector('[wire\\:id]');
    if (!form) {
        console.warn('Form not found');
        return;
    }
    
    // Find the repeater by looking for elements with requestLines in wire:model
    let repeaterContainer = null;
    
    // Strategy 1: Find by wire:model containing requestLines
    const repeaterInputs = form.querySelectorAll('[wire\\:model*="requestLines"]');
    if (repeaterInputs.length > 0) {
        repeaterContainer = repeaterInputs[0].closest('div[wire\\:id]') || 
                           repeaterInputs[0].closest('.fi-repeater') ||
                           repeaterInputs[0].closest('[data-field-wrapper]') ||
                           repeaterInputs[0].closest('div').parentElement;
    }
    
    // Strategy 2: Find section with "Offer Lines" text
    if (!repeaterContainer) {
        const sections = form.querySelectorAll('section, [class*="section"]');
        for (const section of sections) {
            if (section.textContent.includes('Offer Lines')) {
                repeaterContainer = section;
                break;
            }
        }
    }
    
    if (!repeaterContainer) {
        console.warn('Repeater container not found');
        return;
    }
    
    // Find the add button - look for button with add action
    const addButtons = repeaterContainer.querySelectorAll('button');
    let addBtn = null;
    
    for (const btn of addButtons) {
        const text = (btn.textContent || '').toLowerCase();
        const ariaLabel = btn.getAttribute('aria-label') || '';
        if (text.includes('add') || ariaLabel.includes('add') || btn.querySelector('svg')) {
            if (!btn.disabled) {
                addBtn = btn;
                break;
            }
        }
    }
    
    if (!addBtn) {
        // Fallback: find any enabled button in the repeater
        addBtn = Array.from(addButtons).find(btn => !btn.disabled && btn.type === 'button');
    }
    
    if (!addBtn) {
        console.warn('Add button not found');
        return;
    }
    
    // Count items before clicking
    const itemsBefore = repeaterContainer.querySelectorAll('[wire\\:key*="requestLines"], [data-sortable-item]').length;
    
    // Click the add button
    addBtn.click();
    
    // Wait for new item to appear and fill it
    const checkForNewItem = setInterval(() => {
        const itemsAfter = repeaterContainer.querySelectorAll('[wire\\:key*="requestLines"], [data-sortable-item]').length;
        
        if (itemsAfter > itemsBefore) {
            clearInterval(checkForNewItem);
            
            // Find the new item (last one)
            const allItems = Array.from(repeaterContainer.querySelectorAll('[wire\\:key*="requestLines"], [data-sortable-item]'));
            const newItem = allItems[allItems.length - 1];
            
            if (newItem) {
                // Fill medicine
                const medSelect = newItem.querySelector('select[wire\\:model*="medicine_id"], select[name*="medicine_id"]');
                if (medSelect) {
                    medSelect.value = medicineId;
                    medSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    medSelect.dispatchEvent(new Event('input', { bubbles: true }));
                }
                
                // Fill quantity
                const qtyInput = newItem.querySelector('input[type="number"][wire\\:model*="quantity"], input[type="number"][name*="quantity"]');
                if (qtyInput) {
                    qtyInput.value = quantity;
                    qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
                    qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                
                // Fill unit
                const unitSelect = newItem.querySelector('select[wire\\:model*="unit"], select[name*="unit"]');
                if (unitSelect) {
                    unitSelect.value = unit;
                    unitSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    unitSelect.dispatchEvent(new Event('input', { bubbles: true }));
                }
                
                // Focus price field
                setTimeout(() => {
                    const priceInput = newItem.querySelector('input[wire\\:model*="price"], input[name*="price"]');
                    if (priceInput) {
                        priceInput.focus();
                    }
                }, 100);
            }
        }
    }, 50);
    
    // Timeout after 3 seconds
    setTimeout(() => clearInterval(checkForNewItem), 3000);
}

function setNewItemValues(button, medicineId, quantity, unit) {
    // Wait for Livewire to finish adding the item
    setTimeout(() => {
        // Find the repeater container
        const form = document.querySelector('form') || document.querySelector('[wire\\:id]');
        if (!form) return;
        
        // Find all repeater items
        const allItems = Array.from(form.querySelectorAll('[wire\\:key*="requestLines"], [data-sortable-item]'));
        if (allItems.length === 0) return;
        
        // Get the last item (the newly created one)
        const newItem = allItems[allItems.length - 1];
        if (!newItem) return;
        
        // Set medicine_id
        const medSelect = newItem.querySelector('select[wire\\:model*="medicine_id"], select[name*="medicine_id"]');
        if (medSelect && medicineId) {
            medSelect.value = medicineId;
            medSelect.dispatchEvent(new Event('change', { bubbles: true }));
            medSelect.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Set quantity
        const qtyInput = newItem.querySelector('input[type="number"][wire\\:model*="quantity"], input[type="number"][name*="quantity"]');
        if (qtyInput && quantity) {
            qtyInput.value = quantity;
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        
        // Set unit
        const unitSelect = newItem.querySelector('select[wire\\:model*="unit"], select[name*="unit"]');
        if (unitSelect && unit) {
            unitSelect.value = unit;
            unitSelect.dispatchEvent(new Event('change', { bubbles: true }));
            unitSelect.dispatchEvent(new Event('input', { bubbles: true }));
        }
        
        // Focus price field
        setTimeout(() => {
            const priceInput = newItem.querySelector('input[wire\\:model*="price"], input[name*="price"]');
            if (priceInput) {
                priceInput.focus();
            }
        }, 100);
    }, 400);
}
</script>

