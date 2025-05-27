<?php

namespace App\Http\Controllers\API;

use App\Models\ParkingSession;
use Illuminate\Http\Request;

class PaymentController
{
    public function checkPayment(ParkingSession $session, Request $request)
    {
        if ($session->status === 'completed') {
            return response()->json([
                'message' => 'Payment already recorded',
                "status" => "success"
            ], 200);
        }
        return response()->json([
            'message' => 'Payment not recorded',
            "status" => "error"
        ], 200);
    }
}
