<?php

namespace App\Http\Controllers\API;

use App\Http\Services\FlutterwavePaymentService;
use App\Models\ParkingSession;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ParkingSessionController
{
    protected FlutterwavePaymentService $paymentService;

    public function __construct(FlutterwavePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function active(Request $request)
    {
        $query = ParkingSession::query()
            ->whereNull('exit_time')
            ->latest();

        if ($request->search) {
            $query->where('plate_number', 'like', "%{$request->search}%");
        }

        if ($request->from) {
            $query->whereDate('entry_time', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('entry_time', '<=', $request->to);
        }

        $overstayLimit = config('app.overstay_hours', 8);

        $sessions = $query->paginate(10)->through(function ($item) use ($overstayLimit) {
            $entry = Carbon::parse($item->entry_time);
            $durationHours = ceil($entry->diffInMinutes(now()) / 60);
            return [
                'id' => $item->id,
                'plate_number' => $item->plate_number,
                'entry_time' => $item->entry_time->format('Y-m-d H:i'),
                'phone_number' => $item->phone_number,
                'overstayed' => $durationHours > $overstayLimit,
                'duration_hours' => $durationHours,
                'estimated_amount' => $this->calculateAmount($durationHours)
            ];
        });

        return response()->json($sessions);
    }

    public function checkIn(Request $request)
    {
        // TODO Track user who checked in the car.
        // TODO Send  SMS on entry
        $request->validate([
            'plate_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9 ]+$/'
            ],
            'phone' => 'nullable|string|max:15',
        ]);


        $plate = strtoupper(preg_replace('/\s+/', '', $request->plate_number));
        $existingSession = ParkingSession::query()
            ->where(DB::raw("REPLACE(UPPER(plate_number), ' ', '')"), $plate)
            ->where('status', 'active')
            ->first();


        if ($existingSession) {
            return response()->json([
                'message' => 'This car is already checked in.',
                'data' => $existingSession
            ], 409); // Conflict
        }

        $session = ParkingSession::query()->create([
            'plate_number' => $plate,
            'phone' => $request->phone,
            'entry_time' => now(),
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Car checked in successfully',
            'data' => $session
        ], 201);
    }

    public function previewCheckOut(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:20',
        ]);

        $plate = strtoupper($request->plate_number);

        $session = ParkingSession::query()->where('plate_number', $plate)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'No active session found for this car.'
            ], 404);
        }

        $exitTime = now();
        $duration = $session->entry_time->diffInMinutes($exitTime);
        $amount = $session->calculateAmount($duration);

        return response()->json([
            'message' => 'Preview checkout',
            'data' => [
                'plate_number' => $session->plate_number,
                'entry_time' => $session->entry_time,
                'current_time' => $exitTime,
                'duration_minutes' => $duration,
                'estimated_amount' => $amount
            ]
        ]);
    }


    /**
     * @throws Throwable
     */
    public function checkOut(Request $request, ParkingSession $session)
    {
        $data = $request->validate([
            'payment_method' => ['required'],
            'phone_number' => ['nullable', 'string', 'max:15'],
        ]);

        DB::beginTransaction();

        $exitTime = now();
        $diffInMinutes = Carbon::parse($session->entry_time)->diffInMinutes($exitTime);
        $duration = ceil($diffInMinutes / 60);
        // Calculate amount
        $amount = $session->calculateAmount($duration);
        $txRef = uniqid('pkg_');
        $data['tx_ref'] = $txRef; // Unique reference
        // Update session
        $session->update([
            'exit_time' => $exitTime,
            'duration_minutes' => ceil($diffInMinutes),
            'amount' => $amount,
            'status' => 'active',
            'tx_ref' => $txRef
        ]);

        if ($data['payment_method'] == 'momo') {
            $data['amount'] = $amount;
            $data['email'] = "jeanpaulcami@live.com";
            try {
                $response = $this->paymentService->chargeRwandaMobileMoney($data);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Charge initiated',
                    'redirect_url' => $response['meta']['authorization']['redirect'] ?? null,
                    'flw_response' => $response,
                    'data' => $session
                ], 302);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment initiation failed: ' . $e->getMessage(),
                ], 500);
            }
        }
        DB::commit();
        return response()->json([
            'message' => 'Car checked out successfully',
            'data' => $session
        ]);
    }

    private function calculateAmount(int $durationHours)
    {
        return Rate::calculateParkingFee($durationHours);
    }

    public function logs()
    {
        $search = request('search');
        $from = request('from');
        $to = request('to');
        $paymentMethod = request('payment_method');
        $logs = ParkingSession::query()
            ->whereNotNull('exit_time')
            ->when($search, function ($query) use ($search) {
                return $query->where('plate_number', 'like', "%{$search}%");
            })
            ->when($from, function ($query) use ($from) {
                return $query->whereDate('entry_time', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                return $query->whereDate('entry_time', '<=', $to);
            })
            ->when(request('payment_method'), function ($query) use ($paymentMethod) {
                return $query->where('payment_method', $paymentMethod);
            })
            ->latest('exit_time')
            ->paginate(10);

        return response()->json($logs);
    }
}
