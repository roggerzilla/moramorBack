<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockAlert;


class NotifyController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
        ]);

        $user = $request->user();

        $alert = StockAlert::firstOrCreate([
            'user_id' => $user->id,
            'item_id' => $request->item_id,
        ]);

        return response()->json(['message' => 'Notificación registrada.']);
    }
}