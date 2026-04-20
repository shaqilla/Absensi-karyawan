<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function employees()
    {
        $employees = Karyawan::with(['user', 'departemen'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'karyawan');
            })
            ->get();

        foreach ($employees as $emp) {
            $latest = Assessment::with([
                'evaluator.karyawan',
                'evaluatee.karyawan',
                'details.question.category',
            ])
                ->where('evaluatee_id', $emp->user_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->latest()
                ->first();

            $emp->assessed_this_month  = !is_null($latest);
            $emp->latest_assessment_id = $latest?->id;
            $emp->latest_assessment    = $latest;
        }

        return view('admin.assessment.employees', compact('employees'));
    }

    public function create($evaluatee_id)
    {
        $target = User::with('karyawan')->where('role', 'karyawan')->findOrFail($evaluatee_id);

        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();

        if ($categories->isEmpty()) {
            return redirect()->route('admin.assessment.categories')
                ->with('error', 'Buat kategori dan pertanyaan penilaian dulu!');
        }

        return view('admin.assessment.create', compact('target', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'evaluatee_id' => 'required|exists:users,id',
            'scores'       => 'required|array',
            'scores.*'     => 'required|integer|min:1|max:5',
            'notes'        => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $existing = Assessment::where('evaluatee_id', $request->evaluatee_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->first();

            if ($existing) {
                return redirect()->back()->with('error', 'Karyawan ini sudah dinilai bulan ini!');
            }

            $assessment = Assessment::create([
                'evaluator_id'    => Auth::id(),
                'evaluatee_id'    => $request->evaluatee_id,
                'assessment_date' => now(),
                'period'          => now()->format('F Y'),
                'general_notes'   => $request->notes,
            ]);

            foreach ($request->scores as $question_id => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $question_id,
                    'score'         => $score,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.assessment.report')->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }

    // --- FITUR EDIT (BARU) ---
    public function edit($id)
    {
        // Ambil data penilaian beserta detail skornya
        $assessment = Assessment::with(['evaluatee', 'details'])->findOrFail($id);

        // Ambil kategori dan pertanyaan yang aktif
        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->where('is_active', true);
        }])->where('is_active', true)->get();

        // Mapping skor lama biar muncul di form edit
        $oldScores = $assessment->details->pluck('score', 'question_id')->toArray();

        return view('admin.assessment.edit', compact('assessment', 'categories', 'oldScores'));
    }

    // --- FITUR UPDATE (BARU) ---
    public function update(Request $request, $id)
    {
        $request->validate([
            'scores'   => 'required|array',
            'scores.*' => 'required|integer|min:1|max:5',
            'notes'    => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $assessment = Assessment::findOrFail($id);

            // Update catatan umum
            $assessment->update([
                'general_notes' => $request->notes,
            ]);

            // Hapus detail lama, ganti yang baru biar gak ribet logikanya
            AssessmentDetail::where('assessment_id', $id)->delete();

            foreach ($request->scores as $question_id => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $question_id,
                    'score'         => $score,
                ]);
            }

            DB::commit();
            return redirect()->route('admin.assessment.report')->with('success', 'Penilaian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function report(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Ambil data penilaian dengan relasi
        $query = Assessment::with([
            'evaluator.karyawan',
            'evaluatee.karyawan',
            'details.question.category',
        ]);

        // Filter: Kalau bukan admin, cuma bisa lihat yang dia nilai
        if ($currentUser->role != 'admin') {
            $query->where('evaluator_id', $currentUser->id);
        }

        // Filter Periode (Jika ada)
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'asc')->get(); // Diurutkan ASC untuk tren grafik
        $periods = Assessment::distinct()->orderBy('assessment_date', 'desc')->pluck('period');

        // 2. HITUNG RATA-RATA PER KATEGORI (Untuk Radar Chart & Lineart Kategori)
        $categoryTotals = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                if ($detail->question->category) {
                    $catName = $detail->question->category->name;
                    if (!isset($categoryTotals[$catName])) {
                        $categoryTotals[$catName] = ['total' => 0, 'count' => 0];
                    }
                    $categoryTotals[$catName]['total'] += $detail->score;
                    $categoryTotals[$catName]['count']++;
                }
            }
        }

        $avgPerCategory = [];
        foreach ($categoryTotals as $cat => $data) {
            $avgPerCategory[] = [
                'category' => $cat,
                'average' => round($data['total'] / $data['count'], 2)
            ];
        }

        // 3. HITUNG TREN NILAI PER PERIODE (Untuk Grafik Lineart Tren)
        $trendData = [];
        $periodGroups = $assessments->groupBy('period');
        foreach ($periodGroups as $period => $group) {
            $totalScore = 0;
            $totalDetails = 0;
            foreach ($group as $asmt) {
                foreach ($asmt->details as $det) {
                    $totalScore += $det->score;
                    $totalDetails++;
                }
            }
            $trendData[] = [
                'period' => $period,
                'average' => $totalDetails > 0 ? round($totalScore / $totalDetails, 2) : 0
            ];
        }

        // Urutkan kembali assessments ke DESC untuk tampilan tabel riwayat di bawah
        $assessments = $assessments->sortByDesc('assessment_date');

        return view('admin.assessment.report', compact('assessments', 'avgPerCategory', 'periods', 'trendData'));
    }

    public function myReport(Request $request)
    {
        $userId = Auth::id();

        // 1. Ambil Penilaian milik user ini
        $query = Assessment::with(['evaluator', 'details.question.category'])
            ->where('evaluatee_id', $userId);

        // Filter Periode kalau ada
        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->get();

        // 2. FIX ERROR SQL: Pake groupBy buat ambil periode unik dan diurutin berdasarkan tanggal terbaru
        $periods = Assessment::where('evaluatee_id', $userId)
            ->select('period')
            ->groupBy('period')
            ->orderByRaw('MAX(assessment_date) DESC')
            ->pluck('period');

        // 3. HITUNG RATA-RATA PER KATEGORI (Buat Radar Chart & Progress Bar)
        $categoryScores = [];
        foreach ($assessments as $a) {
            foreach ($a->details as $d) {
                $catName = $d->question->category->name ?? 'Lainnya';
                if (!isset($categoryScores[$catName])) {
                    $categoryScores[$catName] = ['sum' => 0, 'count' => 0];
                }
                $categoryScores[$catName]['sum'] += $d->score;
                $categoryScores[$catName]['count']++;
            }
        }

        $avgPerCategory = [];
        foreach ($categoryScores as $name => $val) {
            $avgPerCategory[] = [
                'category' => $name,
                'average'  => round($val['sum'] / $val['count'], 2)
            ];
        }

        // 4. HITUNG DATA BULANAN (Buat Line Chart Perkembangan Nilai)
        $monthlyData = [];
        $sortedForChart = $assessments->sortBy('assessment_date');
        foreach ($sortedForChart as $a) {
            $monthlyData[$a->period] = round($a->details->avg('score'), 2);
        }

        // 5. HITUNG STATS (Buat Info Card)
        $latestAssessment = $assessments->first();
        $stats = [
            'total_assessments' => $assessments->count(),
            'average_all'       => $assessments->isNotEmpty() ? round($assessments->flatMap->details->avg('score'), 1) : 0,
            'latest_score'      => $latestAssessment ? round($latestAssessment->details->avg('score'), 1) : 0
        ];

        // 6. Kirim SEMUA variabel ke Blade
        return view('karyawan.rapor', compact(
            'assessments',
            'periods',
            'avgPerCategory',
            'monthlyData',
            'stats'
        ));
    }

    public function detail($id)
    {
        $assessment = Assessment::with(['evaluator', 'evaluatee', 'details.question.category'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json($assessment);
        }

        return view('admin.assessment.detail', compact('assessment'));
    }

    public function destroy($id)
    {
        $assessment = Assessment::findOrFail($id);
        $assessment->delete();
        return back()->with('success', 'Data penilaian berhasil dihapus!');
    }

    public function history(Request $request)
    {
        $query = Assessment::with(['evaluator.karyawan', 'evaluatee.karyawan', 'details.question.category']);

        if ($request->filled('search')) {
            $query->whereHas('evaluatee', function ($q) use ($request) {
                $q->where('nama', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->paginate(10);
        $periods = Assessment::distinct()->pluck('period');

        return view('admin.assessment.history', compact('assessments', 'periods'));
    }
}
