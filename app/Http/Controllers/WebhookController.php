<?php

namespace App\Http\Controllers;

use App\Models\ParkingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('Webhook received:', $data);

        if (isset($data['event']) && $data['event'] === 'charge.completed') {
            $payment = $data['data'];

            if ($payment['status'] === 'successful') {
                $txRef = $payment['tx_ref'];
                $amount = $payment['amount'];
                $phone = $payment['customer']['phone_number'];

                // Example: Update your ParkingSession model
                $session = ParkingSession::query()->where('tx_ref', $txRef)->first();
                $session->update([
                    'status' => 'completed',
                    'amount' => $amount,
                    'phone' => $phone,
                ]);
                $session->payments()->create([
                    'payment_method' => "momo",
                    'phone_number' => $phone,
                    'amount' => $amount,
                    'status' => 'paid',
                    'response' => $payment
                ]);
                return response()->json(['message' => 'Payment recorded'], 200);
            }
        }

        return response()->json(['message' => 'Ignored'], 200);
    }


}
