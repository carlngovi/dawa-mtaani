<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class AdminBankAccountsController extends Controller
{
    public function index()
    {
        $accounts = DB::table('facilities')
            ->whereNotNull('banking_account_number')
            ->whereNull('deleted_at')
            ->select(['id','ulid','facility_name','phone','county',
                      'banking_account_number','banking_account_validated_at',
                      'network_membership','facility_status'])
            ->orderBy('facility_name')
            ->paginate(30);
        return view('admin.bank-accounts', compact('accounts'));
    }
}
