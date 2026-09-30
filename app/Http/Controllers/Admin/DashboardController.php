<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'revenue' => (int) Order::where('payment_status', PaymentStatus::Paid->value)->sum('grand_total'),
            'orderCount' => Order::count(),
            'customerCount' => User::customers()->count(),
            'productCount' => Product::active()->count(),
            'pendingOrders' => Order::where('status', OrderStatus::Pending->value)->count(),
            'lowStockCount' => ProductVariant::lowStock()->inStock()->whereHas('product')->count(),
            'recentOrders' => Order::with('user')->latest()->take(6)->get(),
            'topProducts' => Product::active()
                ->with('primaryImage')
                ->orderByDesc('sold_count')
                ->take(5)
                ->get(),
            'lowStockVariants' => ProductVariant::lowStock()->inStock()
                ->whereHas('product')
                ->with(['product', 'color', 'size'])
                ->take(6)
                ->get(),
        ]);
    }
}