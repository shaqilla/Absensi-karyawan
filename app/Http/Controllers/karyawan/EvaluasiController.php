<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;

class EvaluasiController extends Controller
{
    public function index()
    {
        // Ambil data karyawan yang supervisor-nya adalah saya
        $bawahan = Karyawan::with('user')->where('supervisor_id', Auth::id())->get();

        $totalBawahan = $bawahan->count();
        $sudahDinilai = Assessment::where('evaluator_id', Auth::id())
            ->where('period', now()->format('F Y'))
            ->count();

        return view('karyawan.evaluasi.index', compact('bawahan', 'totalBawahan', 'sudahDinilai'));
    }

    public function create($id)
    {
        $target = User::with('karyawan')->findOrFail($id);
        $categories = AssessmentCategory::where('is_active', true)->get();

        return view('karyawan.evaluasi.create', compact('target', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required',
            'scores' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $assessment = Assessment::create([
                'evaluator_id' => Auth::id(),
                'evaluatee_id' => $request->evaluatee_id,
                'assessment_date' => now(),
                'period' => now()->format('F Y'),
                'general_notes' => $request->notes
            ]);

            foreach ($request->scores as $catId => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'category_id' => $catId,
                    'score' => $score
                ]);
            }

            DB::commit();
            return redirect()->route('karyawan.evaluasi.index')->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
