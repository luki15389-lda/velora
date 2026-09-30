<x-layouts.app title="Sign in">
<section class="mx-auto max-w-md px-4 py-20">
    <h1 class="font-editorial text-3xl">Sign in</h1>
    <p class="mt-2 text-sm text-ink-400">Orders, wishlist and saved addresses live in your account.</p>

    @if ($errors->any())
        <div class="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
            @if (str_contains($errors->first(), 'Verify your email'))
                <a href="{{ route('verification.notice') }}" class="block mt-2 underline font-medium">Enter the code now →</a>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm mb-1">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="field">
        </div>

        <div>
            <label for="password" class="block text-sm mb-1">Password</label>
            <input id="password" name="password" type="password" required class="field">
        </div>

        <label class="flex items-center gap-2 text-sm text-ink-400">
            <input type="checkbox" name="remember" value="1" class="border-sand-300 text-ink-900 focus:ring-0">
            Keep me signed in
        </label>

        <button type="submit" class="btn-primary w-full">Sign in</button>
    </form>

    <p class="mt-6 text-sm text-ink-400">
        No account yet? <a href="{{ route('register') }}" class="text-ink-900 underline">Create one</a>.
    </p>
</section>
</x-layouts.app>
