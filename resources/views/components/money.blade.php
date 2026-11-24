@props(['value' => 0])
<span {{ $attributes->merge(['class' => 'money-cell']) }}>{{ money($value) }}</span>
