@php
    $chip = match ($verification) {
        'verified' => 'ns-chip-green',
        'unverified' => 'ns-chip-grey',
        default => 'ns-chip-amber',
    };
    $label = match ($verification) {
        'verified' => 'Verified',
        'unverified' => 'Unverified',
        default => 'Pending verification',
    };
@endphp
<span class="ns-chip {{ $chip }}">{{ $label }}</span>