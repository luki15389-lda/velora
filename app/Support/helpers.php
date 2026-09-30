<?php

if (! function_exists('rupiah')) {
    /** Formats whole-rupiah integers the way Indonesian storefronts print them. */
    function rupiah(int|float|null $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) ($amount ?? 0), 0, ',', '.');

        return $withSymbol ? "Rp{$formatted}" : $formatted;
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (! function_exists('normalize_id_phone')) {
    /**
     * Normalizes an Indonesian mobile number to a single canonical shape —
     * digits only, country code first, no leading 0 or +. "0812..." and
     * "+62812..." and "62812..." all end up as "62812...".
     *
     * This is what wa.me links expect, so store phone numbers this way
     * everywhere and the WhatsApp links (checkout confirmation, admin
     * "message this customer") work correctly regardless of which format
     * the person typed the number in.
     */
    function normalize_id_phone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }
}