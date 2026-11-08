<meta name="theme-color" content="#0f1f3d">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23117c7a'/%3E%3Cpath fill='%23fff' d='M12.5 6h7v6.5H26v7h-6.5V26h-7v-6.5H6v-7h6.5z'/%3E%3C/svg%3E">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="description" content="Hospital Scheduling System — doctor rosters for on-call, wards and shifts. Built as a UTM FYP in 2023, rebuilt in 2026.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="HSS">
<meta property="og:title" content="HSS | Hospital Scheduling System">
<meta property="og:description" content="Doctor rosters for on-call, wards and shifts. UTM FYP rebuilt in 2026.">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ url('/og-image.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="HSS — Hospital Scheduling System">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="HSS | Hospital Scheduling System">
<meta name="twitter:description" content="Doctor rosters for on-call, wards and shifts. UTM FYP rebuilt in 2026.">
<meta name="twitter:image" content="{{ url('/og-image.png') }}">
<script>
    (() => {
        const stored = localStorage.getItem('hss-theme');
        if (stored === 'dark' || (! stored && window.matchMedia('(prefers-color-scheme: dark)').matches && {{ request()->routeIs('landing', 'login') ? 'false' : 'true' }})) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
