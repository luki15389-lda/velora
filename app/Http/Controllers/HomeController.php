<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $withCard = ['primaryImage', 'category'];

        return view('home', [
            'departments' => Category::roots()->active()->with('children')->orderBy('position')->get(),
            'newArrivals' => Product::active()->with($withCard)->latest()->take(8)->get(),
            'bestSellers' => Product::active()->with($withCard)->orderByDesc('sold_count')->take(8)->get(),
            'menCollection' => Product::active()->with($withCard)->whereIn('gender', ['men', 'unisex'])->inRandomOrder()->take(4)->get(),
            'womenCollection' => Product::active()->with($withCard)->whereIn('gender', ['women', 'unisex'])->inRandomOrder()->take(4)->get(),
            'featured' => Product::active()->featured()->with($withCard)->take(4)->get(),
            'testimonials' => Review::query()
                ->where('status', ReviewStatus::Approved->value)
                ->where('rating', '>=', 4)
                ->with(['user', 'product'])
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }
}
