<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, $id, $hash)
    {
        Log::info('🔍 Iniciando verificación de correo');

        $user = User::findOrFail($id);

        if (!$user) {
            Log::error('❌ Usuario no encontrado con ID: ' . $id);
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        Log::info('✅ Usuario encontrado: ' . $user->email);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Log::error('❌ Hash inválido para el usuario: ' . $user->email);
            return response()->json(['message' => 'Enlace de verificación inválido.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            Log::info('⚠️ El usuario ya tiene el correo verificado: ' . $user->email);
            return redirect('http://localhost:4200/email-verified');
        }

        // FORZAMOS la verificación del correo
        $user->email_verified_at = Carbon::now();
        $user->save();
        Log::info('✅ Correo verificado para: ' . $user->email);

        // Disparar el evento de verificación
        event(new Verified($user));
        Log::info('✅ Evento de verificación disparado para: ' . $user->email);

        return redirect('http://localhost:4200/email-verified');
    }
}
