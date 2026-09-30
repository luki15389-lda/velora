@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 fill=%27%231C1C1C%27/%3E%3Ctext x=%2750%27 y=%2768%27 font-family=%27Georgia,serif%27 font-size=%2760%27 fill=%27%23F7F4EF%27 text-anchor=%27middle%27%3EV%3C/text%3E%3C/svg%3E">
    <title>{{ $title ? $title.' — VELORA' : 'VELORA' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,300;6..72,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen flex flex-col">
    <main class="flex-1 flex items-center">
        {{ $slot }}
    </main>
</body>
</html>