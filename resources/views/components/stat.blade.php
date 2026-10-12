@props(['label'])
<div {{ $attributes->merge(['class' => 'card']) }}>
    <p class="text-xs font-medium tracking-wide text-zinc-500 uppercase">{{ $label }}</p>
    <p class="mt-1 text-xl font-bold text-zinc-900">{{ $slot }}</p>
</div>
