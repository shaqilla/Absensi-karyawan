<?php
// app/Http/Controllers/Admin/AssessmentController.php
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
use Illuminate\Support\Facades\Log;

class AssessmentController extends Controller
{
    // Menampilkan daftar karyawan yang bisa dinilai
    public function employees()
    {
        $employees = Karyawan::with(['user', 'departemen'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'karyawan');
            })
            ->get();

        foreach ($employees as $emp) {
            $emp->assessed_this_month = Assessment::where('evaluator_id', auth()->id())
                ->where('evaluatee_id', $emp->user_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->exists();
        }

        return view('admin.assessment.employees', compact('employees'));
    }

    // Menampilkan form penilaian
    public function create($evaluatee_id)
    {
        if (!is_numeric($evaluatee_id)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID karyawan tidak valid!');
        }

        $target = User::with('karyawan')->where('role', 'karyawan')->find($evaluatee_id);

        if (!$target) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Karyawan tidak ditemukan!');
        }

        $categories = AssessmentCategory::with(['activeQuestions'])
            ->where('is_active', true)
            ->get();

        if ($categories->isEmpty()) {
            return redirect()->route('admin.assessment.categories')
                ->with('error', 'Buat kategori penilaian dulu!');
        }

        $totalQuestions = 0;
        foreach ($categories as $category) {
            $totalQuestions += $category->activeQuestions->count();
        }

        return view('admin.assessment.create', compact('target', 'categories', 'totalQuestions'));
    }

    // Menyimpan penilaian baru
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

            \Log::info('Store assessment called', [
                'evaluatee_id' => $request->evaluatee_id,
                'scores_count' => count($request->scores)
            ]);

            $existingAssessment = Assessment::where('evaluator_id', auth()->id())
                ->where('evaluatee_id', $request->evaluatee_id)
                ->whereMonth('assessment_date', now()->month)
                ->whereYear('assessment_date', now()->year)
                ->first();

            if ($existingAssessment) {
                return redirect()->back()
                    ->with('error', 'Anda sudah menilai karyawan ini bulan ini!');
            }

            // Simpan header assessment
            $assessment = Assessment::create([
                'evaluator_id'    => auth()->id(),
                'evaluatee_id'    => $request->evaluatee_id,
                'assessment_date' => now(),
                'period'          => now()->format('F Y'),
                'period_type'     => 'monthly',
                'general_notes'   => $request->notes,
            ]);

            \Log::info('Assessment created', ['id' => $assessment->id]);

            $detailCount = 0;
            foreach ($request->scores as $question_id => $score) {
                if (!is_numeric($question_id)) continue;

                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'question_id'   => $question_id,
                    'score'         => $score,
                ]);
                $detailCount++;
            }

            \Log::info('Details created', ['count' => $detailCount]);

            DB::commit();
            \Log::info('Store completed successfully');

            if ($request->has('action') && $request->action == 'save_next') {
                return redirect()->route('admin.assessment.employees')
                    ->with('success', 'Penilaian berhasil disimpan! Silakan pilih karyawan berikutnya.');
            }

            return redirect()->route('admin.assessment.report')
                ->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Menampilkan form edit penilaian
    public function edit($id)
    {
        if (!is_numeric($id)) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'ID penilaian tidak valid!');
        }

        $assessment = Assessment::with(['evaluatee.karyawan', 'details.question.category'])->find($id);

        if (!$assessment) {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Data penilaian tidak ditemukan!');
        }

        if ($assessment->evaluator_id != auth()->id() && auth()->user()->role != 'admin') {
            return redirect()->route('admin.assessment.employees')
                ->with('error', 'Anda tidak berhak mengedit penilaian ini!');
        }

        $categories = AssessmentCategory::with('questions')->where('is_active', true)->get();

        $existingScores = [];
        foreach ($assessment->details as $detail) {
            $existingScores[$detail->question_id] = $detail->score;
        }

        return view('admin.assessment.edit', compact('assessment', 'categories', 'existingScores'));
    }

    // Mengupdate penilaian
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

            $assessment->update(['general_notes' => $request->notes]);

            foreach ($request->scores as $question_id => $score) {
                if (!is_numeric($question_id)) continue;

                AssessmentDetail::updateOrCreate(
                    ['assessment_id' => $assessment->id, 'question_id' => $question_id],
                    ['score' => $score]
                );
            }

            DB::commit();
            return redirect()->route('admin.assessment.report')->with('success', 'Penilaian berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Laporan/Rapor - VERSI FIX
    public function report(Request $request)
    {
        if (!auth()->check()) return redirect()->route('login');

        $currentUser = auth()->user();

        // Mulai Query dengan Eager Loading lengkap
        $query = Assessment::with([
            'evaluator.karyawan',
            'evaluatee.karyawan',
            'details.question.category'
        ]);

        // FILTER BERDASARKAN ROLE
        if ($currentUser->role == 'admin') {
            // Admin: Lihat semua data
        } elseif ($currentUser->role == 'guru' || $currentUser->role == 'manager' || $currentUser->role == 'penilai') {
            // Penilai: Lihat orang-orang yang DIA nilai
            $query->where('evaluator_id', $currentUser->id);
        } else {
            // Karyawan/Siswa: Lihat nilai MILIKNYA sendiri
            $query->where('evaluatee_id', $currentUser->id);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->get();

        // Ambil periode unik untuk filter dropdown
        $periods = Assessment::distinct()->orderBy('period', 'desc')->pluck('period')->filter();

        // Hitung Statistik Radar Chart
        $allScores = $assessments->flatMap->details->pluck('score');
        $stats = [
            'total_assessments' => $assessments->count(),
            'total_employees' => $assessments->pluck('evaluatee_id')->unique()->count(),
            'average_all' => $allScores->isNotEmpty() ? round($allScores->avg(), 2) : 0,
        ];

        $categoryTotals = [];
        foreach ($assessments as $assessment) {
            foreach ($assessment->details as $detail) {
                if ($detail->question && $detail->question->category) {
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
                'average' => round($data['total'] / $data['count'], 2),
                'total' => $data['count']
            ];
        }
        usort($avgPerCategory, fn($a, $b) => strcmp($a['category'], $b['category']));

        return view('admin.assessment.report', compact('assessments', 'stats', 'avgPerCategory', 'periods'));
    }

    // Detail Penilaian
    public function detail($id)
    {
        $assessment = Assessment::with([
            'evaluator.karyawan',
            'evaluatee.karyawan',
            'details.question.category'
        ])->findOrFail($id);

        if (auth()->user()->role != 'admin' && auth()->id() != $assessment->evaluator_id && auth()->id() != $assessment->evaluatee_id) {
            return redirect()->back()->with('error', 'Akses ditolak!');
        }

        return view('admin.assessment.detail', compact('assessment'));
    }

    // Riwayat Penilaian
    public function history(Request $request)
    {
        $query = Assessment::with(['evaluator.karyawan', 'evaluatee.karyawan', 'details'])
            ->orderBy('assessment_date', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('evaluatee', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%");
            });
        }

        $periods = Assessment::distinct()->pluck('period');
        $assessments = $query->paginate(15)->withQueryString();

        return view('admin.assessment.history', compact('assessments', 'periods'));
    }

    // Hapus Penilaian
    public function destroy($id)
    {
        if (auth()->user()->role != 'admin') return redirect()->back()->with('error', 'Hanya Admin!');

        try {
            DB::beginTransaction();
            $assessment = Assessment::findOrFail($id);
            $assessment->details()->delete();
            $assessment->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan rapor untuk karyawan (nilai sendiri)
     */
    public function myReport(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $currentUser = auth()->user();

        // Pastikan hanya karyawan yang bisa akses (atau admin melihat preview)
        if ($currentUser->role != 'karyawan' && $currentUser->role != 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak berhak mengakses halaman ini!');
        }

        // Jika admin, bisa pilih karyawan (tambahan)
        $targetId = $currentUser->role == 'admin' && request('user_id')
            ? request('user_id')
            : $currentUser->id;

        $targetUser = User::with('karyawan')->find($targetId);

        if (!$targetUser) {
            return redirect()->back()->with('error', 'User tidak ditemukan!');
        }

        // Ambil periode untuk filter
        $periods = Assessment::where('evaluatee_id', $targetId)
            ->select('period')
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period')
            ->filter();

        // Ambil semua penilaian untuk user ini
        $query = Assessment::with([
            'evaluator.karyawan',
            'details.question.category'
        ])
            ->where('evaluatee_id', $targetId)
            ->orderBy('assessment_date', 'desc');

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $assessments = $query->get();

        // Hitung statistik
        $allScores = $assessments->flatMap->details->pluck('score');

        $stats = [
            'total_assessments' => $assessments->count(),
            'average_all' => $allScores->isNotEmpty() ? round($allScores->avg(), 2) : 0,
            'latest_score' => $assessments->isNotEmpty()
                ? round($assessments->first()->details->avg('score'), 2)
                : 0,
        ];

        // Hitung rata-rata per kategori
        $categoryTotals = [];
        $feedbackList = [];

        foreach ($assessments as $assessment) {
            // Kumpulkan feedback
            if ($assessment->general_notes && $assessment->general_notes != '-') {
                $feedbackList[] = [
                    'date' => $assessment->assessment_date->format('d M Y'),
                    'period' => $assessment->period,
                    'evaluator' => $assessment->evaluator->nama ?? 'Admin',
                    'notes' => $assessment->general_notes
                ];
            }

            // Hitung per kategori
            foreach ($assessment->details as $detail) {
                if ($detail->question && $detail->question->category) {
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
                'average' => round($data['total'] / $data['count'], 2),
                'total' => $data['count']
            ];
        }

        // Urutkan berdasarkan kategori
        usort($avgPerCategory, fn($a, $b) => strcmp($a['category'], $b['category']));

        // Data untuk grafik perkembangan per bulan
        $monthlyData = $assessments
            ->groupBy(fn($a) => $a->assessment_date->format('M Y'))
            ->map(fn($items) => round($items->flatMap->details->avg('score'), 2))
            ->take(12); // 12 bulan terakhir

        return view('karyawan.rapor', compact(
            'targetUser',
            'assessments',
            'stats',
            'avgPerCategory',
            'periods',
            'feedbackList',
            'monthlyData'
        ));
    }
}
