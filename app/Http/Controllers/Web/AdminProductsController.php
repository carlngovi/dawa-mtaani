<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminProductsController extends Controller
{
    public function index()
    {
        $products = DB::table('products')
            ->orderBy('therapeutic_category')
            ->orderBy('generic_name')
            ->paginate(30);
        $categories = DB::table('products')
            ->distinct()->orderBy('therapeutic_category')
            ->pluck('therapeutic_category');
        return view('admin.products', compact('products', 'categories'));
    }
}
