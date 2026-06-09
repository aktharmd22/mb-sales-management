@props(['stage'])

@php
    use App\Support\Pipeline;
    $color = Pipeline::color($stage);
    $label = Pipeline::label($stage);
@endphp

<span {{ $attributes->merge(['class' => "badge bg-stage-{$color}/10 text-stage-{$color}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-stage-{{ $color }}"></span>
    {{ $label }}
</span>
