<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminProductsController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $query = DB::table('products');

        if ($request->filled('search')) {
            $like = '%' . $request->search . '%';
            $query->where(function ($q) use ($like) {
                $q->where('generic_name', 'like', $like)
                  ->orWhere('brand_name', 'like', $like)
                  ->orWhere('sku_code', 'like', $like);
            });
        }

        if ($request->filled('category')) {
            $query->where('therapeutic_category', $request->category);
        }

        $products = $query->orderBy('therapeutic_category')
                          ->orderBy('generic_name')
                          ->paginate(30)
                          ->withQueryString();

        $categories = DB::table('products')
            ->distinct()->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');

        return view('admin.products', compact('products', 'categories'));
    }
}
