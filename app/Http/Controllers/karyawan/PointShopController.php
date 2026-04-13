<?php
namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\{FlexibilityItem, PointLedger, UserToken};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointShopController extends Controller {
    public function index() {
        $items = FlexibilityItem::all();
        $history = PointLedger::where('user_id', Auth::id())->latest()->get();
        return view('karyawan.point_shop.index', compact('items', 'history'));
    }

    public function buy($itemId) {
        $item = FlexibilityItem::findOrFail($itemId);
        $user = Auth::user();
        $currentBalance = $user->currentPoints(); // Pastikan method ini ada di User.php (Langkah 2 turn sebelumnya)

        if ($currentBalance < $item->point_cost) {
            return back()->with('error', 'Poin tidak cukup!');
        }

        DB::transaction(function() use ($user, $item, $currentBalance) {
            PointLedger::create([
                'user_id' => $user->id,
                'transaction_type' => 'SPEND',
                'amount' => -$item->point_cost,
                'current_balance' => $currentBalance - $item->point_cost,
                'description' => 'Membeli item: ' . $item->item_name
            ]);

            UserToken::create([
                'user_id' => $user->id,
                'item_id' => $item->id,
                'status' => 'AVAILABLE'
            ]);
        });

        return back()->with('success', 'Item berhasil dibeli!');
    }
}
