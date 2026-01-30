<?php

namespace App\Http\Controllers\Admin; // Namespace folder Admin

// TAMBAHKAN BARIS INI SUPAYA TIDAK ERROR
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\QrSession;
use Illuminate\Support\Facades\Auth;

class QrController extends Controller
{
    public function generate() 
    {
        try {
            $token = bin2hex(random_bytes(20)); 

            // Nonaktifkan token lama
            QrSession::where('is_active', true)->update(['is_active' => false]); 
            
            // Simpan token baru
            QrSession::create([
                'token' => $token,
                'created_by' => Auth::id(),
                'expired_at' => now()->addSeconds(40),
                'is_active' => true
            ]);

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}