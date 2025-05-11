<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Address;

class UserController extends Controller
{
    public function assignAdmin(Request $request)
    {
        if (Auth::user()->role !== 'superuser') {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->role = 'admin';
        $user->save();

        return response()->json(['message' => 'Usuario ahora es Administrador']);
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito'], 201);
    }

    public function getUsers()
    {
        $users = User::where('role', 'user')->get();
        return response()->json($users);
    }
    public function getAdmins()
    {
        $users = User::where('role', 'admin')->get();
        return response()->json($users);
    }
    public function deleteAdmin($id)
    {
        // Verifica que el usuario autenticado sea un superuser
        if (Auth::user()->role !== 'superuser') {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }
    
        // Busca al usuario administrador por su ID
        $user = User::where('role', 'admin')->find($id);
    
        // Si el usuario no existe, devuelve un error
        if (!$user) {
            return response()->json(['message' => 'Usuario administrador no encontrado'], 404);
        }
    
        // Elimina lógicamente al usuario
        $user->delete();
    
        return response()->json(['message' => 'Usuario administrador eliminado correctamente']);
    }
    
    public function restoreAdmin($id)
    {
        // Verifica que el usuario autenticado sea un superuser
        if (Auth::user()->role !== 'superuser') {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }
    
        // Busca al usuario eliminado por su ID
        $user = User::withTrashed()->where('role', 'admin')->find($id);
    
        // Si el usuario no existe, devuelve un error
        if (!$user) {
            return response()->json(['message' => 'Usuario administrador no encontrado'], 404);
        }
    
        // Restaura al usuario
        $user->restore();
    
        return response()->json(['message' => 'Usuario administrador restaurado correctamente']);
    }
    
    public function getDeletedAdmins()
    {
        // Obtiene los usuarios administradores eliminados
        $users = User::onlyTrashed()->where('role', 'admin')->get();
        return response()->json($users);
    }
    public function updateAdmin(Request $request, $id)
{
    // Verifica que el usuario autenticado sea un superuser
    if (Auth::user()->role !== 'superuser') {
        return response()->json(['message' => 'Acceso denegado'], 403);
    }

    // Busca al usuario administrador por su ID
    $user = User::where('role', 'admin')->find($id);

    // Si el usuario no existe, devuelve un error
    if (!$user) {
        return response()->json(['message' => 'Usuario administrador no encontrado'], 404);
    }

    // Valida los datos de entrada
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
    ]);

    // Actualiza los datos del usuario
    $user->name = $request->name;
    $user->email = $request->email;
    if ($request->password) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    return response()->json(['message' => 'Usuario administrador actualizado correctamente']);
}

public function getUserInfo(Request $request)
{
    Log::info('Iniciando getUserInfo...'); // Log de inicio del método

    $user = Auth::user(); // Obtener el usuario autenticado

    if (!$user) {
        Log::error('Usuario no autenticado en getUserInfo'); // Log de error
        return response()->json(['message' => 'Usuario no autenticado'], 401);
    }

    // Log del usuario autenticado
    Log::info('Usuario autenticado en getUserInfo:', [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
    ]);

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
    ]);
}

public function addAddress(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'street' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'state' => 'required|string|max:255',
        'postal_code' => 'required|string|max:20',
        'country' => 'required|string|max:255',
    ]);

    $address = new Address([
        'street' => $request->street,
        'city' => $request->city,
        'state' => $request->state,
        'postal_code' => $request->postal_code,
        'country' => $request->country,
    ]);

    $user->addresses()->save($address);

    return response()->json(['message' => 'Dirección agregada exitosamente']);
}

public function getAddresses()
{
    $user = Auth::user();
    return response()->json($user->addresses);
}

public function updateProfile(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'nullable|string|min:8',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    if ($request->password) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json(['message' => 'Perfil actualizado correctamente']);
}

public function updateAddress(Request $request, $id)
{
    $address = Address::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

    $address->update($request->only([
        'street', 'city', 'state', 'postal_code', 'country'
    ]));

    return response()->json(['message' => 'Dirección actualizada correctamente']);
}
public function deleteAddress($id)
{
    $address = Address::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
    $address->delete();

    return response()->json(['message' => 'Dirección eliminada correctamente']);
}
}