{{-- resources/views/components/card.blade.php --}}
<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg']) }}>
    <div class="p-6 text-gray-900">
        {{ $slot }}
    </div>
</div>