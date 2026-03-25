<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminCategoriesController extends Controller
{
    public function index()
    {
        $categories = DB::table('products')
            ->select('therapeutic_category',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count'))
            ->groupBy('therapeutic_category')
            ->orderBy('therapeutic_category')
            ->get();
        return view('admin.categories', compact('categories'));
    }
}
