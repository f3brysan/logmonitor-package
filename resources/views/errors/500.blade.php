<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terjadi Kesalahan</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, Segoe UI, sans-serif;
            background: #f4f4f5;
            color: #18181b;
        }
        main {
            max-width: 28rem;
            padding: 2rem;
            text-align: center;
        }
        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.5rem;
        }
        p {
            margin: 0;
            color: #52525b;
            line-height: 1.5;
        }
        .log-id {
            margin-top: 1.25rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.875rem;
            color: #18181b;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <main>
        <h1>Terjadi Kesalahan</h1>
        <p>Sistem mengalami gangguan. Silakan coba lagi nanti.</p>
        @if (! empty($logId))
            <p>Jika Anda menghubungi dukungan, sampaikan LOG ID berikut.</p>
            <p class="log-id">LOG ID: {{ $logId }}</p>
        @endif
    </main>
</body>
</html>
