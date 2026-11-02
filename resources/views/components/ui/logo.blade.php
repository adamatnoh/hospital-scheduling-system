@props(['dark' => false, 'size' => 'md', 'text' => true])
@php($box = $size === 'lg' ? 'h-12 w-12' : ($size === 'sm' ? 'h-8 w-8' : 'h-10 w-10'))
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="{{ $box }} relative grid shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 via-brand-600 to-navy-800 shadow-lg shadow-brand-900/30 ring-1 ring-white/10">
        <svg viewBox="0 0 32 32" class="h-3/5 w-3/5" aria-hidden="true">
            <path fill="#fff" d="M12.5 4h7a1 1 0 0 1 1 1v6.5H27a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-6.5V27a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-6.5H5a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1h6.5V5a1 1 0 0 1 1-1Z"/>
            <path fill="none" stroke="#106463" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 16h4l1.6-3 2.6 6 1.6-3H23"/>
        </svg>
    </span>
    @if ($text)
        <span class="leading-tight">
            <span class="block text-lg font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-navy-900 dark:text-white' }}">HSS</span>
            <span class="block text-[0.7rem] font-medium {{ $dark ? 'text-navy-200' : 'text-muted' }}">Hospital Scheduling System</span>
        </span>
    @endif
</span>
