<x-layouts.app title="Create an account">
<section class="mx-auto max-w-md px-4 py-20">
    <h1 class="font-editorial text-3xl">Create an account</h1>
    <p class="mt-2 text-sm text-ink-400">Use a real email — we'll send a 6-digit code to verify it before your account is active.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf

        @foreach ([
            ['name', 'Full name', 'text'],
            ['email', 'Email', 'email'],
            ['phone', 'Phone (optional)', 'tel'],
        ] as [$field, $label, $type])
            <div>
                <label for="{{ $field }}" class="block text-sm mb-1">{{ $label }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ old($field) }}" class="field"
                       @if ($field === 'phone')
                           inputmode="numeric" placeholder="+62 812 3456 7890"
                           oninput="this.value = this.value.replace(/[^0-9+]/g, '')"
                       @endif>
                @error($field)<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        @endforeach

        <div>
            <label for="password" class="block text-sm mb-1">Password</label>
            <input id="password" name="password" type="password" required class="field">
            <p class="mt-1 text-xs text-ink-400">At least 8 characters, with letters and numbers.</p>
            @error('password')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm mb-1">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="field">
        </div>

        <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="mt-6 text-sm text-ink-400">
        Already registered? <a href="{{ route('login') }}" class="text-ink-900 underline">Sign in</a>.
    </p>
</section>
</x-layouts.app>
