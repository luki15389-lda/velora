<x-layouts.app title="Verify your email">
<section class="mx-auto max-w-md px-4 py-20">
    <h1 class="font-editorial text-3xl">Check your email</h1>
    <p class="mt-2 text-sm text-ink-400">
        We sent a 6-digit code to <span class="text-ink-900">{{ $email }}</span>. Enter it below to activate your account.
    </p>

    @if ($errors->any())
        <div class="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mt-6 border border-ink-900 bg-sand-100 px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.verify') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label for="code" class="block text-sm mb-1">Verification code</label>
            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus
                   class="field text-center text-2xl tracking-[0.5em]" placeholder="000000">
        </div>

        <button type="submit" class="btn-primary w-full">Verify</button>
    </form>

    <form method="POST" action="{{ route('verification.resend') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-ink-400 underline hover:text-ink-900">Resend the code</button>
    </form>

    <p class="mt-8 text-xs text-ink-400 text-center">
        Code expires 10 minutes after it's sent. Check spam if it doesn't show up shortly.
    </p>
</section>
</x-layouts.app>
