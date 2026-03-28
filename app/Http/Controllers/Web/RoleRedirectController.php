<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RoleRedirectController extends Controller
{
    public static function redirectForUser(?User $user): RedirectResponse
    {
        if (! $user) {
            return redirect('/login');
        }

        return match (true) {
            $user->hasRole('technical_admin')                     => redirect('/tech/diagnostics'),
            $user->hasRole('super_admin')                         => redirect('/super/settings'),
            $user->hasAnyRole(['network_admin', 'admin'])         => redirect('/admin/dashboard'),
            $user->hasRole('assistant_admin')                     => redirect('/assistant/dashboard'),
            $user->hasRole('admin_support')                       => redirect('/support/tickets'),
            $user->hasRole('shared_accountant')                   => redirect('/finance/settlement'),
            $user->hasRole('wholesale_facility')                  => redirect('/wholesale/orders'),
            $user->hasRole('logistics_facility')                  => redirect('/logistics/deliveries'),
            $user->hasRole('retail_facility')                     => redirect('/retail/dashboard'),
            $user->hasRole('group_owner')                         => redirect('/group/dashboard'),
            $user->hasRole('network_field_agent')                 => redirect('/field/pharmacies'),
            $user->hasRole('county_coordinator')                  => redirect('/county'),
            $user->hasRole('sales_rep')                           => redirect('/sales'),
            $user->hasRole('customer')                            => redirect('/store'),
            $user->hasRole('manufacturer')                        => redirect('/manufacturer'),
            $user->hasRole('system')                              => redirect('/admin/dashboard'),
            default => redirect('/login'),
        };
    }
}
