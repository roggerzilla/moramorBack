<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


class VerifyEmailController extends Controller
{
    /**
     * Maneja la verificación de email
     */
    public function __invoke(Request $request, $id, $hash)
    {
        Log::info('Iniciando verificación de email para usuario ID: '.$id);
    
        if (! URL::hasValidSignature($request)) {
            Log::error('Firma inválida para verificación de email');
            return redirect()->away('http://localhost:4200/home?email=invalid');
        }
        
        
    
        $user = User::findOrFail($id);
    
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Log::error('Hash inválido');
            return response()->json(['message' => 'Enlace inválido'], 403);
        }
    
        if ($user->hasVerifiedEmail()) {
            Log::info('Email ya verificado anteriormente');
            return redirect()->away('http://localhost:4200/home?email=already-verified');
        }
    
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            Log::info('Email verificado exitosamente');
        }
    
        return redirect()->away('http://localhost:4200/home?email=success');
    }
    
    
}