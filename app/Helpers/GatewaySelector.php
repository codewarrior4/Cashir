<?php

namespace App\Helpers;

use App\Models\Gateway; // Ensure this points to your Gateway model

class GatewaySelector
{
    public static function getActiveGateway()
    {
        $gateway = Gateway::where('paystack', 1)->first();

        if ($gateway) {
            return 'paystack';
        }

        $gateway = Gateway::where('monnify', 1)->first();

        if ($gateway) {
            return 'monnify';
        }

        return null;
    }
}
