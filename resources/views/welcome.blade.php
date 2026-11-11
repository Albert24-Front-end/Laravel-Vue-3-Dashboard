<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Корпоративный финансовый дашборд</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 antialiased min-h-screen">
    <div id="app">
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background-color: #f8fafc; font-family: sans-serif;">
            <div style="text-align: center;">
                <div style="width: 36px; height: 36px; border: 3px solid #2563eb; border-top-color: transparent; border-radius: 50%; margin: 0 auto 12px; animation: spin 0.8s linear infinite;"></div>
                <div style="font-size: 13px; font-weight: 600; color: #475569;">Загрузка финансового дашборда...</div>
            </div>
            <style>
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        </div>
    </div>
</body>
</html>
