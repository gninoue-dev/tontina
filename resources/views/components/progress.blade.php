@props(['value', 'max'])
@php($percent = $max > 0 ? min(100, round($value * 100 / $max)) : 0)
<div {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full bg-zinc-100']) }} role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full bg-brand-500" style="width: {{ $percent }}%"></div>
</div>
