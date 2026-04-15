<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{PointRule, FlexibilityItem, User}; // Pastikan Model User di-import
use Illuminate\Http\Request;

class PointRuleController extends Controller
{
    public function index()
    {
        // 1. Ambil semua aturan dan item marketplace
        $rules = PointRule::all();
        $items = FlexibilityItem::all();

        // 2. AMBIL DATA RANKING (LEADERBOARD) - Sudah Diurutkan
        $rankings = User::where('role', 'karyawan')
            ->get()
            ->sortByDesc(function ($u) {
                return (int) $u->currentPoints();
            })
            ->values()
            ->take(10);

        return view('admin.integrity.index', compact('rules', 'items', 'rankings'));
    }

    // Simpan Aturan Baru (Contoh: JIKA < 07:00 MAKA +10)
    public function storeRule(Request $request)
    {
        // 1. Gunakan manual validator supaya kita bisa kontrol arah redirect-nya
        // Kalau pake $request->validate() biasa, dia otomatis redirect back (ini yang bikin error GET)
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'rule_name' => 'required|string',
            'point_modifier' => 'required|numeric',
            'condition_operator' => 'required',
            'condition_value' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.integrity.index')
                ->withErrors($validator)
                ->withInput();
        }

        // 2. Simpan Data
        \App\Models\PointRule::create([
            'rule_name' => $request->rule_name,
            'target_role' => $request->target_role ?? 'karyawan',
            'condition_operator' => $request->condition_operator,
            'condition_value' => $request->condition_value,
            'point_modifier' => $request->point_modifier,
        ]);

        // 3. Paksa redirect ke ROUTE INDEX (Jangan pake back!)
        return redirect()->route('admin.integrity.index')->with('success', 'Aturan poin berhasil ditambahkan!');
    }

    public function storeItem(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'item_name' => 'required|string',
            'point_cost' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.integrity.index')
                ->withErrors($validator)
                ->withInput();
        }

        \App\Models\FlexibilityItem::create([
            'item_name' => $request->item_name,
            'point_cost' => $request->point_cost,
            'is_active' => true,
        ]);

        return redirect()->route('admin.integrity.index')->with('success', 'Item Marketplace berhasil ditambah!');
    }

    // EDIT ATURAN DAN ITEM 
    public function updateRule(Request $request, $id)
    {
        $request->validate([
            'rule_name' => 'required',
            'condition_operator' => 'required',
            'condition_value' => 'required',
            'point_modifier' => 'required|numeric',
        ]);

        $rule = PointRule::findOrFail($id);
        $rule->update($request->all());

        return redirect()->route('admin.integrity.index')->with('success', 'Aturan poin berhasil diupdate!');
    }

    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required',
            'point_cost' => 'required|numeric',
        ]);

        $item = FlexibilityItem::findOrFail($id);
        $item->update($request->all());

        return redirect()->route('admin.integrity.index')->with('success', 'Item Reward berhasil diupdate!');
    }

    // Hapus Aturan
    public function destroyRule($id)
    {
        PointRule::destroy($id);
        return redirect()->route('admin.integrity.index')->with('success', 'Aturan berhasil dihapus!');
    }

    // Hapus Item Reward
    public function destroyItem($id)
    {
        FlexibilityItem::destroy($id);
        return redirect()->route('admin.integrity.index')->with('success', 'Item berhasil dihapus!');
    }
}
