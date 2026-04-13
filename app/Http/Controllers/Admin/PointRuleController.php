<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{PointRule, FlexibilityItem};
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    public function index()
    {
        $rules = \App\Models\PointRule::all();
        $items = \App\Models\FlexibilityItem::all();

        // AMBIL DATA RANKING (LEADERBOARD)
        $rankings = \App\Models\User::where('role', 'karyawan')
            ->get() // Ambil semua data karyawan dulu
            ->sortByDesc(function($u) {
                // Pastikan hasil currentPoints() dikonversi ke angka (int) supaya sortirnya akurat
                return (int) $u->currentPoints();
            })
            ->values() // PENTING! Reset index biar jadi 0, 1, 2, 3... secara berurutan
            ->take(10); // Baru diambil 10 besar

        return view('admin.integrity.index', compact('rules', 'items', 'rankings'));
    }

    // Simpan Aturan (+10 atau -5)
    public function storeRule(Request $request)
    {
        $request->validate(['rule_name' => 'required', 'point_modifier' => 'required|numeric']);
        PointRule::create($request->all());
        return back()->with('success', 'Aturan poin berhasil ditambahkan!');
    }

    // Simpan Item Toko
    public function storeItem(Request $request)
    {
        $request->validate(['item_name' => 'required', 'point_cost' => 'required|numeric']);
        FlexibilityItem::create($request->all());
        return back()->with('success', 'Item Marketplace berhasil ditambah!');
    }

    public function destroyRule($id) { PointRule::destroy($id); return back(); }
    public function destroyItem($id) { FlexibilityItem::destroy($id); return back(); }
}
