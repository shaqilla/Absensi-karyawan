<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\PointLedger;
use App\Models\FlexibilityItem;
use App\Models\UserToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Tampilan Utama Dompet & Toko
     */
    public function index()
    {
        // Ambil riwayat poin milik saya
        $history = PointLedger::where('user_id', Auth::id())->latest()->get();

        // Ambil semua barang di toko
        $items = FlexibilityItem::all();

        // Ambil token yang saya punya dan masih bisa dipakai (AVAILABLE)
        $tokens = UserToken::with('item')
                    ->where('user_id', Auth::id())
                    ->where('status', 'AVAILABLE')
                    ->get();

        return view('karyawan.wallet.index', compact('history', 'items', 'tokens'));
    }

    /**
     * Proses Tukar Poin (Belanja)
     */
    public function exchange($id)
    {
        $item = FlexibilityItem::findOrFail($id);
        $user = Auth::user();
        $balance = $user->currentPoints(); // Mengambil saldo dari Model User

        // --- FITUR TAMBAHAN: CEK KUPON DISKON ---
        // Cari apakah user punya token "Diskon" di inventory-nya
        $discountToken = UserToken::where('user_id', $user->id)
                            ->where('status', 'AVAILABLE')
                            ->whereHas('item', function($q) {
                                $q->where('item_name', 'LIKE', '%Diskon%');
                            })->first();

        // Tentukan harga akhir (Jika punya diskon, potong 50%)
        $finalCost = $discountToken ? ($item->point_cost / 2) : $item->point_cost;

        // 1. Validasi Saldo
        if ($balance < $finalCost) {
            return response()->json([
                'success' => false,
                'message' => 'Poin Anda tidak mencukupi, kumpulkan lebih banyak lagi!'
            ], 422);
        }

        // 2. Proses Transaksi (Database Transaction)
        DB::beginTransaction();
        try {
            // A. Catat Pengurangan Poin di Ledger
            PointLedger::create([
                'user_id' => $user->id,
                'transaction_type' => 'SPEND',
                'amount' => -$finalCost,
                'current_balance' => $balance - $finalCost,
                'description' => 'Berhasil Tukar: ' . $item->item_name . ($discountToken ? ' (Pake Diskon 50%)' : '')
            ]);

            // B. Masukkan Barang ke Inventory User
            UserToken::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'status' => 'AVAILABLE'
            ]);

            // C. Pakai Kupon Diskon-nya kalau tadi dipake
            if ($discountToken) {
                $discountToken->update(['status' => 'USED']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil ditukar! Token Anda sudah masuk ke Inventory.',
                'new_balance' => $balance - $finalCost
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
