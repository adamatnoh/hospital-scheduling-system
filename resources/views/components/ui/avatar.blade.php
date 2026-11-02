@props(['name' => '?', 'size' => 'md'])
@php
    $initials = collect(preg_split('/\s+/', trim(preg_replace('/^Dr\.?\s+/i', '', $name))))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
    $palette = ['from-brand-400 to-brand-700', 'from-navy-400 to-navy-700', 'from-[#8da399] to-[#4f6a5d]', 'from-[#f08a4b] to-[#d65302]', 'from-violet-400 to-[#311F62]'];
    $gradient = $palette[abs(crc32($name)) % count($palette)];
    $box = match ($size) { 'sm' => 'h-8 w-8 text-xs', 'lg' => 'h-12 w-12 text-base', default => 'h-9 w-9 text-sm' };
@endphp
<span {{ $attributes->merge(['class' => "inline-grid shrink-0 place-items-center rounded-full bg-gradient-to-br font-semibold text-white ring-2 ring-white/80 dark:ring-white/10 {$gradient} {$box}"]) }} aria-hidden="true">{{ $initials ?: '?' }}</span>
