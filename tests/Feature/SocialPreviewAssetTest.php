<?php

/**
 * Root PNGs are not under /build, /images or /css. On Vercel the legacy routes
 * list is an allowlist: anything missing falls through to api/index.php and
 * LinkedIn receives the Laravel 404 HTML page instead of the brand graphic.
 */
$rootBrandPngs = [
    'og-image.png' => [1200, 630],
    'apple-touch-icon.png' => [180, 180],
    'favicon-16.png' => [16, 16],
    'favicon-32.png' => [32, 32],
    'icon-192.png' => [192, 192],
    'icon-512.png' => [512, 512],
];

it('serves only the named root brand pngs before the laravel catch-all', function () use ($rootBrandPngs) {
    $routes = json_decode((string) file_get_contents(base_path('vercel.json')), true, 512, JSON_THROW_ON_ERROR)['routes'];

    $catchAll = $routes[array_key_last($routes)];
    expect($catchAll['dest'])->toBe('/api/index.php');

    $pngRouteIndex = collect($routes)->search(
        fn (array $route): bool => isset($route['dest']) && str_contains($route['src'], 'og-image\.png')
    );
    expect($pngRouteIndex)->toBeInt()->toBeLessThan(array_key_last($routes));

    $pngRoute = $routes[$pngRouteIndex];
    expect($pngRoute['dest'])->toBe('/$1')
        ->and($pngRoute['src'])->toStartWith('^/')
        ->and($pngRoute['src'])->toEndWith('$')
        ->and($pngRoute['src'])->not->toContain('.*');

    foreach ($rootBrandPngs as $file => [$width, $height]) {
        expect($pngRoute['src'])->toContain(str_replace('.', '\.', $file))
            ->and(is_file(public_path($file)))->toBeTrue()
            ->and(hssResolveVercelDest($routes, '/'.$file))->toBe('/'.$file);

        [$actualWidth, $actualHeight] = hssPngSize(public_path($file));
        expect($actualWidth)->toBe($width)->and($actualHeight)->toBe($height);
    }

    expect(hssResolveVercelDest($routes, '/favicon.ico'))->toBe('/favicon.ico')
        ->and(hssResolveVercelDest($routes, '/robots.txt'))->toBe('/robots.txt')
        ->and(hssResolveVercelDest($routes, '/build/assets/app.js'))->toBe('/build/assets/app.js')
        ->and(hssResolveVercelDest($routes, '/images/doctor-card-984.webp'))->toBe('/images/doctor-card-984.webp')
        ->and(hssResolveVercelDest($routes, '/css/base_style.css'))->toBe('/css/base_style.css');

    foreach ([
        '/',
        '/login',
        '/.env',
        '/composer.json',
        '/../.env',
        '/storage/logs/laravel.log',
        '/vendor/autoload.php',
        '/.git/config',
        '/secret.png',
        '/og-image.png.bak',
        '/foo/og-image.png',
        '/api/cron/daily',
    ] as $path) {
        expect(hssResolveVercelDest($routes, $path))->toBe('/api/index.php');
    }
});

it('advertises the same 1200x630 brand png on the public pages', function () {
    [$width, $height] = hssPngSize(public_path('og-image.png'));
    $image = url('/og-image.png');

    expect($width)->toBe(1200)->and($height)->toBe(630);

    foreach (['/', '/login'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect(substr_count($html, 'property="og:image"'))->toBe(1)
            ->and(substr_count($html, 'name="twitter:image"'))->toBe(1)
            ->and($html)->toContain('property="og:image" content="'.$image.'"')
            ->and($html)->toContain('property="og:image:width" content="'.$width.'"')
            ->and($html)->toContain('property="og:image:height" content="'.$height.'"')
            ->and($html)->toContain('property="og:image:alt" content="HSS — Hospital Scheduling System"')
            ->and($html)->toContain('name="twitter:image" content="'.$image.'"')
            ->and($html)->toContain('href="'.asset('favicon-32.png').'"')
            ->and($html)->toContain('href="'.asset('favicon-16.png').'"')
            ->and($html)->toContain('href="'.asset('apple-touch-icon.png').'"');
    }
});

/**
 * @param  array<int, array<string, mixed>>  $routes
 */
function hssResolveVercelDest(array $routes, string $path): ?string
{
    foreach ($routes as $route) {
        if (! preg_match('#'.$route['src'].'#', $path, $matches)) {
            continue;
        }

        if (! isset($route['dest'])) {
            if (! empty($route['continue'])) {
                continue;
            }

            return null;
        }

        $dest = preg_replace_callback(
            '/\$(\d+)/',
            fn (array $match): string => $matches[(int) $match[1]] ?? '',
            $route['dest'],
        );

        if (empty($route['continue'])) {
            return $dest;
        }
    }

    return null;
}

/**
 * @return array{0: int, 1: int}
 */
function hssPngSize(string $path): array
{
    $bytes = (string) file_get_contents($path);

    expect(substr($bytes, 0, 8))->toBe("\x89PNG\r\n\x1a\n")
        ->and(substr($bytes, 12, 4))->toBe('IHDR');

    return [
        unpack('N', substr($bytes, 16, 4))[1],
        unpack('N', substr($bytes, 20, 4))[1],
    ];
}
