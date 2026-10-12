@props(['amount'])
<span {{ $attributes->merge(['class' => 'whitespace-nowrap tabular-nums']) }}>{{ \App\Support\Fcfa::format((int) $amount) }}</span>
