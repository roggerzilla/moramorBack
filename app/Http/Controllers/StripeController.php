<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount, // Monto en centavos (ej: $10.00 USD = 1000)
                'currency' => 'mxn',
                'metadata' => [
                    'order_id' => '12345', // Opcional: ID de tu orden
                ],
            ]);
    
            return response()->json([
                'clientSecret' => $paymentIntent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}