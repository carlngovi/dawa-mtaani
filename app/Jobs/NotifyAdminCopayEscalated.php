<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotifyAdminCopayEscalated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $orderId) {}

    public function handle(): void
    {
        $order = DB::table('orders as o')
            ->join('facilities as f', 'o.retail_facility_id', '=', 'f.id')
            ->where('o.id', $this->orderId)
            ->select('o.ulid', 'o.copay_escalated_at', 'f.name as facility_name', 'f.phone')
            ->first();

        if (! $order) {
            Log::warning('NotifyAdminCopayEscalated: order not found', ['order_id' => $this->orderId]);
            return;
        }

        // Get all network_admin users
        $admins = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['network_admin', 'super_admin'])
            ->select('users.id', 'users.phone')
            ->get();

        foreach ($admins as $admin) {
            // WhatsApp notification dispatched via existing outbound service
            dispatch(new \App\Jobs\SendWhatsAppMessage(
                phone: $admin->phone,
                template: 'COPAY_ESCALATED',
                variables: [
                    'order_ref'     => $order->ulid,
                    'facility_name' => $order->facility_name,
                    'escalated_at'  => $order->copay_escalated_at,
                ],
            ));
        }
    }
}
