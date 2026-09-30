<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27%3E%3Crect width=%27100%27 height=%27100%27 fill=%27%231C1C1C%27/%3E%3Ctext x=%2750%27 y=%2768%27 font-family=%27Georgia,serif%27 font-size=%2760%27 fill=%27%23F7F4EF%27 text-anchor=%27middle%27%3EV%3C/text%3E%3C/svg%3E">
    <title>Admin sign in — VELORA</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,300;6..72,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink-900 text-white font-sans antialiased flex items-center justify-center px-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <p class="text-2xl font-semibold tracking-[0.35em]">VELORA</p>
            <p class="mt-2 text-xs uppercase tracking-[0.3em] text-white/40">Admin</p>
        </div>

        <div class="bg-white text-ink-900 p-8">
            <h1 class="font-editorial text-2xl">Sign in</h1>
            <p class="mt-1 text-sm text-ink-400">Store management access only.</p>

            @if ($errors->any())
                <div class="mt-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm mb-1">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="field">
                </div>

                <div>
                    <label for="password" class="block text-sm mb-1">Password</label>
                    <input id="password" name="password" type="password" required class="field">
                </div>

                <button type="submit" class="btn-primary w-full">Sign in</button>
            </form>

        </div>

        <p class="mt-6 text-center text-xs text-white/40">
            Looking for the store instead of the admin panel?
            <a href="{{ route('home') }}" class="text-white/70 hover:text-white underline">Go to velora.test</a>
        </p>
    </div>

</body>
</html>
