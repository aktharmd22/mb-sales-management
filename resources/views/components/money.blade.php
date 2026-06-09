@props(['value' => 0, 'decimals' => false, 'compact' => false])

<span {{ $attributes->merge(['class' => 'tnum']) }}>{{ $compact ? rm_compact($value) : rm($value, $decimals) }}</span>
