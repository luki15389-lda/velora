import axios from 'axios';
import collapse from '@alpinejs/collapse';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * IMPORTANT: Livewire ships and boots its own Alpine instance internally
 * (via @livewireScripts in the layout). We do NOT `import Alpine from
 * 'alpinejs'` or call `Alpine.start()` ourselves — doing that previously
 * created a second, separate Alpine instance that raced with Livewire's own,
 * and Livewire's `$wire` magic only exists on ITS instance. That mismatch is
 * why `$wire.addToCart(...)` silently did nothing on the product page: the
 * x-data component was live, but calling into Livewire from it wasn't.
 *
 * The correct extension point is the `alpine:init` event, which Livewire
 * fires on `document` right before it calls Alpine.start() itself. Anything
 * registered inside this listener attaches to Livewire's own global `Alpine`.
 */
document.addEventListener('alpine:init', () => {
    Alpine.plugin(collapse);

    /**
     * Drives the admin product form: the colour/size variant matrix and the
     * image list. Kept as one component so the variant generator can react to
     * the size group the admin picks, and so removed rows/images stay in the
     * DOM (hidden) instead of being spliced out — that way their hidden
     * "remove" input still posts and the controller can act on it.
     */
    Alpine.data('productForm', ({ initialRows, sizesByGroup, initialSizeGroup, initialImages }) => ({
        sizeGroup: initialSizeGroup,
        sizesByGroup,
        rows: (initialRows || []).map((row, index) => ({ ...row, key: index + 1, remove: false })),
        nextKey: (initialRows || []).length + 1,
        images: (initialImages || []).map((img, index) => ({ ...img, key: `existing-${index}`, remove: false })),
        newImages: [],
        nextImageKey: 1,
        primaryImageId: (initialImages || []).find((img) => img.is_primary)?.id ?? null,

        get availableSizes() {
            return this.sizesByGroup[this.sizeGroup] || [];
        },

        /**
         * Colours available to pick in the variant table: whatever colours
         * are currently tagged on this product's photos (existing + newly
         * added), deduplicated by hex. A variant row that already has a
         * colour no photo currently carries (an older product, or a photo
         * that got untagged) still shows its own colour too, so editing
         * something else on that row never silently blanks it out.
         */
        get taggedColors() {
            const seen = new Map();

            const add = (hex, name) => {
                if (!hex || !name) return;
                const key = hex.toLowerCase();
                if (!seen.has(key)) seen.set(key, { hex, name });
            };

            this.images.forEach((i) => !i.remove && add(i.color_hex, i.color_name));
            this.newImages.forEach((i) => add(i.color_hex, i.color_name));
            this.rows.forEach((r) => !r.remove && add(r.color_hex, r.color_name));

            return Array.from(seen.values());
        },

        addRow() {
            this.rows.push({ key: this.nextKey++, id: null, color_hex: '', color_name: '', size_id: '', stock: 0, price: '', sale_price: '', remove: false });
        },

        removeRow(key) {
            const row = this.rows.find((r) => r.key === key);
            if (row) row.remove = true;
        },

        get visibleRowCount() {
            return this.rows.filter((r) => !r.remove).length;
        },

        addImageSlot() {
            this.newImages.push({ key: this.nextImageKey++, color_hex: '#cccccc', color_name: '' });
        },

        removeImageSlot(key) {
            this.newImages = this.newImages.filter((img) => img.key !== key);
        },

        removeExistingImage(key) {
            const image = this.images.find((img) => img.key === key);
            if (image) image.remove = true;
        },
    }));

    /**
     * "Use my current location" on the checkout address field. Reads the
     * browser's GPS via the Geolocation API, then reverse-geocodes the
     * coordinates into a readable address using OpenStreetMap's free
     * Nominatim API (no key needed). Writes the result straight into the
     * Livewire-bound textarea via $wire.set — the person can still edit it
     * afterwards, this just saves typing the address from scratch.
     */
    Alpine.data('addressLocator', () => ({
        locating: false,
        error: null,

        locate() {
            if (!navigator.geolocation) {
                this.error = "Your browser doesn't support location detection.";
                return;
            }

            this.locating = true;
            this.error = null;

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        const { latitude, longitude } = position.coords;
                        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}&addressdetails=1`;
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        const data = await response.json();
                        const address = data?.display_name;

                        if (address) {
                            this.$wire.set('street_address', address);
                        } else {
                            this.error = "Couldn't work out an address from your location — enter it manually.";
                        }
                    } catch (e) {
                        this.error = 'Something went wrong looking up your address.';
                    } finally {
                        this.locating = false;
                    }
                },
                () => {
                    this.error = 'Location permission was denied — enter your address manually.';
                    this.locating = false;
                },
                { timeout: 10000 },
            );
        },
    }));

    /**
     * Drives the product page's colour/size/quantity picker entirely
     * client-side — instant, no server round trip. Livewire is only called
     * for Add to Cart / Buy Now, via $wire, where a server check actually
     * matters (stock can change between page load and click).
     */
    Alpine.data('productGallery', ({ variants, colors, sizes, images }) => ({
        variants,
        colors,
        sizes,
        images,
        selectedColorId: null,
        selectedSizeId: null,
        quantity: 1,

        init() {
            const firstAvailable = this.variants.find((v) => v.stock > 0) || this.variants[0];
            this.selectedColorId = this.normalize(firstAvailable?.color_id);
            this.selectedSizeId = this.normalize(firstAvailable?.size_id);
        },

        /** Coerces an id to a number (or null), so "3" and 3 always compare
         *  equal regardless of whether the DB driver handed JSON a string or
         *  an int. */
        normalize(id) {
            return id === null || id === undefined || id === '' ? null : Number(id);
        },

        get selectedVariant() {
            return this.variants.find((v) => this.normalize(v.color_id) === this.selectedColorId
                && this.normalize(v.size_id) === this.selectedSizeId) || null;
        },

        get activeImage() {
            return this.images.find((i) => this.normalize(i.color_id) === this.selectedColorId) || this.images[0] || null;
        },

        stockForSize(sizeId) {
            const variant = this.variants.find((v) => this.normalize(v.color_id) === this.selectedColorId
                && this.normalize(v.size_id) === this.normalize(sizeId));
            return variant ? variant.stock : 0;
        },

        colorName(id) {
            return this.colors.find((c) => this.normalize(c.id) === this.normalize(id))?.name || '';
        },

        formatRupiah(amount) {
            return 'Rp' + Number(amount || 0).toLocaleString('id-ID');
        },

        selectColor(colorId) {
            this.selectedColorId = this.normalize(colorId);
            this.quantity = 1;
        },

        selectSize(sizeId) {
            this.selectedSizeId = this.normalize(sizeId);
            this.quantity = 1;
        },

        incrementQuantity() {
            const max = this.selectedVariant?.stock ?? 1;
            this.quantity = Math.min(this.quantity + 1, Math.max(1, max));
        },

        decrementQuantity() {
            this.quantity = Math.max(1, this.quantity - 1);
        },

        addToCart() {
            if (!this.selectedVariant) return;
            this.$wire.addToCart(Number(this.selectedVariant.id), Number(this.quantity));
        },

        buyNow() {
            if (!this.selectedVariant) return;
            this.$wire.buyNow(Number(this.selectedVariant.id), Number(this.quantity));
        },
    }));
});