@props(['options', 'chartKey' => 'chart'])

{{-- wire:ignore keeps Livewire's morph away from ApexCharts' injected SVG;
     the wire:key (with the period baked in) makes Livewire swap the node so
     Alpine re-inits with fresh data when the period changes. --}}
<div wire:ignore wire:key="apex-{{ $chartKey }}"
     x-data="apexChart(@js($options))"
     {{ $attributes }}></div>
