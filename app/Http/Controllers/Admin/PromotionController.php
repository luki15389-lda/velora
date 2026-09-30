<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage-catalog');

        return view('admin.promotions.index', [
            'coupons' => Coupon::withCount('usages')->with('category')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage-catalog');

        return view('admin.promotions.create', ['departments' => $this->departments()]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::create($this->attributes($request));

        return redirect()->route('admin.promotions.index')->with('status', "Coupon \"{$coupon->code}\" was created.");
    }

    public function edit(Coupon $promotion): View
    {
        $this->authorize('manage-catalog');

        return view('admin.promotions.edit', ['coupon' => $promotion, 'departments' => $this->departments()]);
    }

    private function departments(): \Illuminate\Support\Collection
    {
        return \App\Models\Category::whereNull('parent_id')->orderBy('position')->get();
    }

    public function update(StoreCouponRequest $request, Coupon $promotion): RedirectResponse
    {
        $promotion->update($this->attributes($request));

        return redirect()->route('admin.promotions.index')->with('status', "Coupon \"{$promotion->code}\" was updated.");
    }

    public function destroy(Coupon $promotion): RedirectResponse
    {
        $this->authorize('manage-catalog');

        $code = $promotion->code;
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('status', "Coupon \"{$code}\" was deleted.");
    }

    /** Quick on/off switch from the index table, without opening the full form. */
    public function toggle(Coupon $promotion): RedirectResponse
    {
        $this->authorize('manage-catalog');

        $promotion->update(['is_active' => ! $promotion->is_active]);

        return back()->with('status', $promotion->is_active
            ? "Coupon \"{$promotion->code}\" is now active."
            : "Coupon \"{$promotion->code}\" is now paused.");
    }

    private function attributes(StoreCouponRequest $request): array
    {
        return [
            'code' => Str::upper($request->string('code')),
            'description' => $request->input('description'),
            'category_id' => $request->filled('category_id') ? $request->integer('category_id') : null,
            'type' => $request->string('type'),
            'value' => $request->string('type') === 'free_shipping' ? 0 : $request->float('value'),
            'minimum_purchase' => $request->integer('minimum_purchase'),
            'maximum_discount' => $request->filled('maximum_discount') ? $request->integer('maximum_discount') : null,
            'usage_limit' => $request->filled('usage_limit') ? $request->integer('usage_limit') : null,
            'usage_limit_per_user' => $request->integer('usage_limit_per_user'),
            'starts_at' => $request->date('starts_at'),
            'ends_at' => $request->date('ends_at'),
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
