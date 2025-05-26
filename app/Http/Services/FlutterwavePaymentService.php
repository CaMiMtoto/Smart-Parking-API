<?php

namespace App\Http\Services;

class FlutterwavePaymentService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = env('FLW_SECRET_KEY'); // Add FLW_SECRET_KEY in your .env
    }

    /**
     * @throws \Exception
     */
    public function chargeRwandaMobileMoney(array $data)
    {
        $url = 'https://api.flutterwave.com/v3/charges?type=mobile_money_rwanda';

        $headers = [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json',
        ];

        $payload = [
            'phone_number' => $data['phone_number'],
            'amount' => $data['amount'],
            'currency' => 'RWF',
            'email' => $data['email'],
            'tx_ref' => $data['tx_ref'],
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception("cURL Error: $err");
        }

        $decoded = json_decode($response, true);

        if (!isset($decoded['status']) || $decoded['status'] !== 'success') {
            throw new \Exception("Payment failed: " . ($decoded['message'] ?? 'Unknown error'));
        }

        return $decoded;
    }
}

