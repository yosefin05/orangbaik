@props(['name', 'show' => false, 'focusable' => false])

<div
    x-data="{ show: @js($show) }"
    x-show="show"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    style="display: {{ $show ? 'block' : 'none' }}"
    {{ $attributes->merge(['class' => 'fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0']) }}>
    <div x-on:click="show = false" class="fixed inset-0 transform transition-all">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
    <div x-show="show" x-transition class="relative mb-6 transform overflow-hidden rounded-lg bg-white shadow-xl sm:mx-auto sm:w-full sm:max-w-2xl">
        {{ $slot }}
    </div>
</div>
