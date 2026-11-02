/** Dashboard charts. Chart.js is only downloaded on pages that have a chart. */
const canvases = document.querySelectorAll('canvas[data-chart]');

if (canvases.length) {
    import('chart.js/auto').then(({ default: Chart }) => {
        const dark = document.documentElement.classList.contains('dark');
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
        Chart.defaults.color = dark ? '#93a4ba' : '#5e6e82';
        Chart.defaults.borderColor = dark ? '#22304a' : '#e2e8f0';

        canvases.forEach((canvas) => {
            const data = JSON.parse(canvas.dataset.chart || '{}');
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: data.labels ?? [],
                    datasets: (data.datasets ?? []).map((set) => ({
                        borderRadius: 4,
                        borderSkipped: false,
                        maxBarThickness: 18,
                        ...set,
                    })),
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 600 },
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, padding: 16 } },
                        tooltip: { mode: 'index', intersect: false, padding: 10, cornerRadius: 8 },
                    },
                    scales: {
                        x: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { drawTicks: false } },
                        y: { stacked: true, grid: { display: false } },
                    },
                },
            });
        });
    });
}
