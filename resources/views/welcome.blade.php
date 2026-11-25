<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>As-Sajjaad | Portal Infak Madrasah</title>
    <meta name="theme-color" content="#1264c7">
    <meta name="description" content="Sistem Informasi Pencatatan & Pembayaran Infak Madrasah As-Sajjad">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=2">
    <link rel="alternate icon" type="image/x-icon" href="/favicon.ico?v=2">
    <link rel="apple-touch-icon" href="/icon-192x192.png?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Onest:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f2f7fd] text-[#172b4d] antialiased selection:bg-[#1264c7]/15 selection:text-[#144b88]">
    <div id="app"></div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('SW registered:', reg.scope))
                    .catch((err) => console.log('SW failed:', err));
            });
        }
    </script>
</body>
</html>
