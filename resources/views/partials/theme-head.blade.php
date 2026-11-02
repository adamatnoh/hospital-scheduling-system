<meta name="theme-color" content="#0f1f3d">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23117c7a'/%3E%3Cpath fill='%23fff' d='M12.5 6h7v6.5H26v7h-6.5V26h-7v-6.5H6v-7h6.5z'/%3E%3C/svg%3E">
<script>
    (() => {
        const stored = localStorage.getItem('hss-theme');
        if (stored === 'dark' || (! stored && window.matchMedia('(prefers-color-scheme: dark)').matches && {{ request()->routeIs('landing', 'login') ? 'false' : 'true' }})) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
