<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Keys editable from this page, and which are digits-only phone numbers. */
    private const KEYS = [
        'store.name', 'store.tagline', 'store.email',
        'store.phone', 'store.whatsapp', 'store.address',
    ];

    public function edit(): View
    {
        $this->authorize('manage-settings');

        return view('admin.settings.edit', [
            'settings' => collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => setting($key, '')]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage-settings');

        $data = $request->validate([
            'store.name' => ['required', 'string', 'max:120'],
            'store.tagline' => ['nullable', 'string', 'max:160'],
            'store.email' => ['required', 'email', 'max:150'],
            'store.phone' => ['nullable', 'string', 'max:32'],
            'store.whatsapp' => ['required', 'string', 'regex:/^(?:\+?62|0)8[0-9]{7,11}$/'],
            'store.address' => ['nullable', 'string', 'max:500'],
        ], [
            'store.whatsapp.regex' => 'Enter a valid Indonesian mobile number that has WhatsApp — start with 08 or 62, not a mix of both.',
        ]);

        // Stored the same canonical way recipient phone numbers are, so
        // wa.me links built from it always work regardless of which format
        // was typed in.
        $data['store.whatsapp'] = normalize_id_phone($data['store.whatsapp']);

        foreach ($data as $key => $value) {
            Setting::put($key, $value, 'general');
        }

        return back()->with('status', 'Store settings were updated.');
    }
}