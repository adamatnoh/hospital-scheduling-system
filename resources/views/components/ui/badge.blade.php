@props(['tone' => 'slate', 'icon' => null, 'dot' => false])
@php
    $tones = [
        'slate' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        'brand' => 'bg-brand-50 text-brand-700 border-brand-100 dark:bg-brand-900/40 dark:text-brand-300 dark:border-brand-800',
        'navy' => 'bg-navy-50 text-navy-700 border-navy-100 dark:bg-navy-800 dark:text-navy-200 dark:border-navy-700',
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
        'amber' => 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
        'rose' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
        'violet' => 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:border-violet-800',
        'morning' => 'bg-[#8da399]/15 text-[#4f6a5d] border-[#8da399]/40 dark:text-[#b9cbc2]',
        'evening' => 'bg-[#d65302]/10 text-[#b54602] border-[#d65302]/30 dark:text-[#f59a5c]',
        'night' => 'bg-[#311F62]/10 text-[#311F62] border-[#311F62]/25 dark:bg-[#311F62]/40 dark:text-violet-200',
    ];
    $dots = ['emerald' => 'bg-emerald-500', 'amber' => 'bg-amber-500', 'rose' => 'bg-rose-500', 'brand' => 'bg-brand-500', 'navy' => 'bg-navy-500', 'violet' => 'bg-violet-500'];
@endphp
<span {{ $attributes->merge(['class' => 'hss-badge '.($tones[$tone] ?? $tones['slate'])]) }}>
    @if ($dot)<span class="h-1.5 w-1.5 rounded-full {{ $dots[$tone] ?? 'bg-slate-400' }}" aria-hidden="true"></span>@endif
    @if ($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif
    {{ $slot }}
</span>
