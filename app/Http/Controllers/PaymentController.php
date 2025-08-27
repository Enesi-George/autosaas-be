<?php

namespace App\Http\Controllers;

use App\Mail\OnBoardingMail;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;
    private $paymentAmount = 1000000; // 10,000 Naira in kobo

    public function __construct()
    {
        $this->paystackSecretKey = config('services.paystack.secret_key');
        $this->paystackPublicKey = config('services.paystack.public_key');
    }

    /**
     * Initialize payment for user onboarding
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'user_data' => 'required|array',
        ]);

        $userRawPassword = $request->user_data['password'];

        try {
            DB::beginTransaction();

            // Generate unique reference
            $reference = 'ONB_' . Str::random(10) . '_' . time();

            // Store temporary user data in session or cache
            // We'll create the user after successful payment
            $tempUserData = $request->user_data;
            $tempUserData['payment_reference'] = $reference;

            // Store in cache for 30 minutes
            cache()->put("temp_user_data_{$reference}", $tempUserData, now()->addMinutes(30));

            // Initialize Paystack payment
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->email,
                'amount' => $this->paymentAmount,
                'reference' => $reference,
                'callback_url' => config('app.frontend_url') . '/payment/callback',
                'metadata' => [
                    'purpose' => 'user_onboarding',
                    'user_email' => $request->email,
                ]
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to initialize payment: ' . $response->body());
            }

            $paymentData = $response->json();

            // Create payment record
            Payment::create([
                'reference' => $reference,
                'email' => $request->email,
                'amount' => $this->paymentAmount,
                'status' => 'pending',
                'gateway_response' => $paymentData,
                'purpose' => 'user_onboarding'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment initialized successfully',
                'data' => [
                    'authorization_url' => $paymentData['data']['authorization_url'],
                    'access_code' => $paymentData['data']['access_code'],
                    'reference' => $reference,
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment initialization failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Payment initialization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Paystack webhook callback
     */
    public function handleWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();

        if (!$this->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->all();

        if ($event['event'] === 'charge.success') {
            $this->handleSuccessfulPayment($event['data']);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Frontend callback handler - called when user returns from payment
     */
    public function paymentCallback(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        try {
            // Verify payment with Paystack
            $verification = $this->verifyPayment($request->reference);

            if ($verification['status'] && $verification['data']['status'] === 'success') {
                // Payment successful - create user if not already created
                $result = $this->processSuccessfulPayment($verification['data']);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment verified and user created successfully',
                    'data' => $result
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment verification failed or payment was not successful',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment callback failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment with Paystack
     */
    private function verifyPayment($reference)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
        ])->get("https://api.paystack.co/transaction/verify/{$reference}");

        return $response->json();
    }

    /**
     * Process successful payment and create user
     */
    private function processSuccessfulPayment($paymentData)
    {
        return DB::transaction(function () use ($paymentData) {
            $reference = $paymentData['reference'];

            // Get temporary user data from cache
            $tempUserData = cache()->get("temp_user_data_{$reference}");

            if (!$tempUserData) {
                throw new \Exception('User registration data not found or expired');
            }

            // Check if user already exists (prevent duplicate creation)
            $existingUser = User::where('email', $tempUserData['email'])->first();
            if ($existingUser) {
                // Update payment record
                Payment::where('reference', $reference)->update([
                    'status' => 'completed',
                    'user_id' => $existingUser->id,
                    'gateway_response' => $paymentData,
                    'paid_at' => now()
                ]);

                return [
                    'user' => $existingUser,
                    'payment_status' => 'completed'
                ];
            }

            // Create the user
            $userData = $tempUserData;
            unset($userData['payment_reference']); // Remove payment reference from user data

            $userRawPassword = $userData['password']; //get the raw pwd

            // Hash password if provided
            if (isset($userData['password'])) {
                $userData['password'] = bcrypt($userData['password']);
            }
            $userData['amount'] = $this->paymentAmount / 100; // Convert kobo to naira
            $userData['paid'] = true;
            $user = User::create($userData);

            // Update payment record
            Payment::where('reference', $reference)->update([
                'status' => 'completed',
                'user_id' => $user->id,
                'gateway_response' => $paymentData,
                'paid_at' => now()
            ]);

            // Clear temporary data from cache
            cache()->forget("temp_user_data_{$reference}");

            //send user a welcome email
            Mail::to($user->email)->queue(new OnBoardingMail($user, $userRawPassword));

            return [
                'user' => $user,
                'payment_status' => 'completed'
            ];
        });
    }

    /**
     * Handle successful payment from webhook
     */
    private function handleSuccessfulPayment($paymentData)
    {
        try {
            $this->processSuccessfulPayment($paymentData);
        } catch (\Exception $e) {
            Log::error('Webhook payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($payload, $signature)
    {
        $computedSignature = hash_hmac('sha512', $payload, $this->paystackSecretKey);
        return hash_equals($signature, $computedSignature);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($reference)
    {
        try {
            $payment = Payment::where('reference', $reference)->first();

            if (!$payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'reference' => $payment->reference,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'email' => $payment->email,
                    'paid_at' => $payment->paid_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve payment status'
            ], 500);
        }
    }
}
