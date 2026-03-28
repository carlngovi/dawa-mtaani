<?php

namespace App\Events;

use App\Models\CustomerOrder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public CustomerOrder $order)
    {
    }
}
