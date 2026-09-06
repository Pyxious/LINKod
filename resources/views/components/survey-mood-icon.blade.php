@props([
    'score' => 5,
    'class' => 'w-7 h-7',
    'onHeader' => false,
])

@php
    $score = (int) $score;
    $colorClass = match($score) {
        5 => $onHeader ? 'text-emerald-300 dark:text-emerald-400' : 'text-emerald-500 dark:text-emerald-400',
        4 => $onHeader ? 'text-green-300 dark:text-green-400' : 'text-green-600 dark:text-green-400',
        3 => $onHeader ? 'text-amber-300 dark:text-amber-400' : 'text-amber-500 dark:text-amber-400',
        2 => $onHeader ? 'text-orange-300 dark:text-orange-400' : 'text-orange-500 dark:text-orange-400',
        default => $onHeader ? 'text-rose-300 dark:text-rose-400' : 'text-rose-500 dark:text-rose-400',
    };
@endphp

<svg viewBox="0 0 24 24" fill="none" {{ $attributes->merge(['class' => "{$class} {$colorClass} shrink-0 transition-all duration-200"]) }}>
    <!-- Face Outline & Subtle Background Tint -->
    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" fill="currentColor" fill-opacity="{{ $onHeader ? '0.2' : '0.12' }}" />

    @if($score === 5)
        <!-- 5: Very Satisfied (Joyful smiling curved eyes, wide open happy grin, and dimple accents) -->
        <path d="M7 10c.6-1.6 2.4-1.6 3 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
        <path d="M14 10c.6-1.6 2.4-1.6 3 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
        <path d="M7.5 13.5c.8 2.6 2.4 3.7 4.5 3.7s3.7-1.1 4.5-3.7h-9z" fill="currentColor" fill-opacity="0.25" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
        <path d="M6 13c0 .8.5 1.2 1 1.2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
        <path d="M18 13c0 .8-.5 1.2-1 1.2" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />

    @elseif($score === 4)
        <!-- 4: Satisfied (Happy open eyes, friendly upward smile) -->
        <circle cx="8.5" cy="10" r="1.3" fill="currentColor" />
        <circle cx="15.5" cy="10" r="1.3" fill="currentColor" />
        <path d="M8 13.8c1 2 2.3 2.7 4 2.7s3-.7 4-2.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />

    @elseif($score === 3)
        <!-- 3: Neutral (Calm eyes, straight horizontal mouth) -->
        <circle cx="8.5" cy="10.5" r="1.3" fill="currentColor" />
        <circle cx="15.5" cy="10.5" r="1.3" fill="currentColor" />
        <path d="M8.5 15h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />

    @elseif($score === 2)
        <!-- 2: Dissatisfied (Slightly sad eyes, gentle downward frown) -->
        <circle cx="8.5" cy="11" r="1.3" fill="currentColor" />
        <circle cx="15.5" cy="11" r="1.3" fill="currentColor" />
        <path d="M8.5 16c1-1.6 2.2-2.2 3.5-2.2s2.5.6 3.5 2.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />

    @else
        <!-- 1: Very Dissatisfied (Angled furrowed brows and deep distressed frown) -->
        <path d="M7 8.5l3 1.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
        <path d="M17 8.5l-3 1.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
        <circle cx="8.5" cy="11.5" r="1.3" fill="currentColor" />
        <circle cx="15.5" cy="11.5" r="1.3" fill="currentColor" />
        <path d="M8 16.8c1-2.2 2.3-3 4-3s3 .8 4 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" fill="none" />
    @endif
</svg>
