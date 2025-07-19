<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Twilio\Rest\Client;
use App\Models\OtpLog;

class AuthController extends Controller
{
    protected function sendSmsCode($phoneNumber, $code)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_PHONE_NUMBER');

        $client = new Client($twilioSid, $twilioToken);

        try {
            $client->messages->create(
                $phoneNumber,
                [
                    'from' => $twilioNumber,
                    'body' => "Votre code de vérification est: $code"
                ]
            );
            return true;
        } catch (\Exception $e) {
            \Log::error("Twilio SMS error: " . $e->getMessage());
            return false;
        }
    }
    public function sendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email|unique:users,email',
            'phone' =>  ['required_without:email', 'phone:AUTO', 'unique:users,phone'],
            'method' => 'required|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $identifier = $request->input('method') === 'email' ? $request->email : $request->phone;
        
        // Stocker le code dans le cache pour 15 minutes
        Cache::put(
            'verification_code_' . $identifier, 
            $verificationCode, 
            now()->addMinutes(15)
        );

        if ($request->method === 'email') {
            // Envoyer le code par email
            Mail::to($request->email)->send(new VerificationCodeMail($verificationCode));
        } else {
            if (!$this->sendSmsCode($request->phone, $verificationCode)) {
                return response()->json([
                    'message' => 'Failed to send SMS verification code'
                ], 500);
            }
        }

        return response()->json([
            'message' => 'Verification code sent successfully'
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'code' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $identifier = $request->email ?? $request->phone;
        $cachedCode = Cache::get('verification_code_' . $identifier);

        if (!$cachedCode || $cachedCode !== $request->code) {
            return response()->json([
                'message' => 'Code de vérification invalide ou expiré'
            ], 422);
        }

        // Marquer l'email/phone comme vérifié dans le cache
        Cache::put(
            'verified_' . $identifier, 
            true, 
            now()->addMinutes(30)
        );

        return response()->json([
            'message' => 'Code vérifié avec succès'
        ]);
    }

    public function resendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'method' => 'required|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->sendVerificationCode($request);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:users,email',
            'phone' => 'required_without:email|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'verification_code' => 'required|digits:6',
            'newsletter_opt_in' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $identifier = $request->email ?? $request->phone;
        
        // Vérifier que l'email/phone a été vérifié
        if (!Cache::get('verified_' . $identifier)) {
            return response()->json([
                'message' => 'Veuillez vérifier votre email/téléphone avant de vous inscrire'
            ], 422);
        }

        // Vérifier à nouveau le code (optionnel)
        $cachedCode = Cache::get('verification_code_' . $identifier);
        if ($cachedCode !== $request->verification_code) {
            return response()->json([
                'message' => 'Code de vérification invalide ou expiré'
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verified_at' => $request->email ? now() : null,
            'phone_verified_at' => $request->phone ? now() : null,
            'newsletter_opt_in' => $request->newsletter_opt_in ?? false
        ]);

        $method = $request->has('email') ? 'email' : 'phone';

         // Journaliser l'OTP
        OtpLog::create([
    'identifier' => $request->email ?? $request->phone,
    'method' => $method,
    'code' => $cachedCode,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'verified_at' => now()
]);

        // Nettoyer les caches
        Cache::forget('verification_code_' . $identifier);
        Cache::forget('verified_' . $identifier);

        // Créer un token d'authentification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        // Essayer de se connecter avec email ou phone
        $field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        if (!Auth::attempt([$field => $request->input($field), 'password' => $request->password])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful', 
            'user' => $user, 
            'token' => $token
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    public function user(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        return response()->json(['user' => $user], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'surname' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'address' => 'sometimes|string|max:255',
            // 'country' => 'sometimes|string|max:255',
            // 'postal_code' => 'sometimes|string|max:20',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully', 
            'user' => $user
        ], 200);
    }
}