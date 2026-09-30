{{--
    Practice-project disclaimer. Home page only, by design — shows on first
    load, on every refresh, and again whenever the tab regains focus (so it
    can't be missed by someone switching away mid-read).
--}}
<div
    x-data="{ open: true }"
    x-init="document.addEventListener('visibilitychange', () => { if (!document.hidden) open = true })"
    x-show="open"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[100] flex items-center justify-center bg-ink-900/60 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="disclaimer-title"
>
    <div @click.outside="open = false" x-transition class="w-full max-w-md bg-white border border-sand-300 p-6 sm:p-8">
        <h2 id="disclaimer-title" class="font-editorial text-2xl">Sebelum kamu lihat-lihat</h2>

        <div class="mt-4 space-y-3 text-sm text-ink-400 leading-relaxed">
            <p>
                VELORA adalah proyek latihan/pembelajaran pengembangan web, <strong class="text-ink-900">bukan toko
                online resmi</strong>.
            </p>
            <p>
                Nama produk seperti Adidas dan Calvin Klein yang muncul di sini dipakai murni sebagai
                <strong class="text-ink-900">contoh data untuk keperluan demonstrasi dan belajar</strong>. Situs ini
                tidak berafiliasi dengan, tidak mewakili, dan tidak disponsori oleh merek-merek tersebut.
            </p>
            <p>
                Tidak ada transaksi maupun pembelian produk yang nyata di situs ini — semua pesanan dan pembayaran
                hanya simulasi.
            </p>
        </div>

        <button type="button" @click="open = false" class="btn-primary w-full mt-6">Mengerti, lanjutkan</button>

        <p class="mt-4 text-xs text-ink-400 text-center">© {{ now()->year }} Proyek Latihan</p>
    </div>
</div>