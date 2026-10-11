{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
        // Public, indexable pages only (no dashboard or auth-only routes).
        $pages = [
            route('home'),
            route('features'),
            route('pricing'),
            route('help'),
            route('contact'),
            route('privacy'),
            route('terms'),
        ];
        $lastmod = date('Y-m-d');
    @endphp
    @foreach ($pages as $url)
    <url>
        <loc>{{ $url }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </url>
    @endforeach
</urlset>
